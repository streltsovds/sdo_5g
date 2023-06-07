<?php
class HM_Course_CourseService extends HM_Service_Abstract
{
    protected $_cache = [
        'getCourse' => []
    ];

    protected $listeners = array();

    public function getListeners() {
        return $this->listeners;
    }

    public function importScorm($courseId, $unzipPath)
    {
        $targetPath = PUBLIC_PATH . "/unmanaged/COURSES/course" . $courseId;
        $this->emptyDir($targetPath);

        // глобальные переменные для unmanaged
        $GLOBALS['send'] = 1;
        $GLOBALS['cid'] = $_POST['cid'] = $courseId;
        $GLOBALS['isEAU3'] = false;
        $GLOBALS['import_type'] = $_POST['import_type'] = HM_Course_CourseModel::FORMAT_SCORM;
        $GLOBALS['packageDir'] = $unzipPath;
        $_POST['ch_info'] = true; // переписать заголовок

        $user = $this->getService('User')->getCurrentUser();
        $this->getService('Unmanaged')->initUnmanagedSession($user, $systemInfo = false);

        $_REQUEST += $_POST; //for unmanaged
        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, [$paths, APPLICATION_PATH . "/../public/unmanaged/"]));

        $currentDir = getcwd();
        ob_start();

        chdir(APPLICATION_PATH.'/../public/unmanaged/teachers/');
        include(APPLICATION_PATH.'/../public/unmanaged/teachers/organization_exp.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, [$paths]));

        chdir($currentDir);

        return true;
    }

    public function importFree($courseId, $unzipPath)
    {
        $targetPath = PUBLIC_PATH . "/unmanaged/COURSES/course" . $courseId;
        $this->emptyDir($targetPath);
        $this->copyDir($unzipPath, $targetPath);
        $filename = $targetPath . '/index.csv';

        $hmFile = new HM_File_File();
        $hmFile->detectFileEncoding($filename);


        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = $this->getService('CourseItem');

        /** @var HM_Files_FilesService $filesService */
        $filesService = $this->getService('Files');

        $courseMap = [];
        $prevRef = -1;

        if ($fh = fopen($filename, 'r')) {

            // На всякий случай уже после открытия файла, вдруг пре ре-импорте снесём слишком рано
            $filesService->deleteBy(
                $this->quoteInto(['item_type = ?', ' AND item_id = ?'], [HM_Files_FilesModel::ITEM_TYPE_COURSES_FREE, (int) $courseId])
            );

            while (($data = fgetcsv($fh, 2048)) !== false) {

                $paths = array_filter(str_getcsv($data[0], ';'));
                $courseFile = array_shift($paths);

                if (file_exists($targetPath . '/' . $courseFile)) {

                    $file = $filesService->addFile($targetPath . '/' . $courseFile, $courseFile,HM_Files_FilesModel::ITEM_TYPE_COURSES_FREE, $courseId);
                    $fullPath = '';

                    // Алярм!
                    //
                    // Для тех, кто не понимает, как работает оглавление в этих модулях:
                    // Сортировка сверху вниз в меню идёт по prev_ref ASC, иногда отступая "вправо" по level
                    // Возвращаясь на предыдущий уровень ("влево"), prev_ref не ссылается на родительский элемент как в nested set,
                    // а ссылается на элемент выше, даже если он находится на один или несколько уровней "правее"
                    foreach ($paths as $level => $coursePathRaw) {

                        $fullPath .= $coursePathRaw;

                        if(!$courseMap[$fullPath]) {
                            $courseItem = $courseItemService->insert([
                                'title' => $coursePathRaw,
                                'cid' => $courseId,
                                'level' => $level,
                                'prev_ref' => $prevRef
                            ]);

                            $courseMap[$fullPath] = $courseItem->oid;
                            $prevRef = $courseItem->oid;
                        }
                    }

                    $courseItem = $courseItemService->insert([
                        'title' => $courseFile,
                        'cid' => $courseId,
                        'level' => ++$level,
                        'prev_ref' => $prevRef,
                        // В последнюю часть пути ставим указатель на файл
                        'module' => $file->file_id,
                        // Для определения в HM_Course_Item_File_FileModel делаем строго отрицательный
                        'vol2' => -$file->file_id,
                    ]);

                    $prevRef = $courseItem->oid;
                }
            }

            fclose($fh);
        }

        return true;
    }

    public function importTincan($courseId, $unzipPath)
    {
        $tincanFile = "{$unzipPath}/tincan.xml";
        $tincanXml = simplexml_load_file($tincanFile);
        $result = false;

        foreach ($tincanXml->activities->activity as $activity)
        {
            foreach ($activity->attributes() as $attribName => $attribValue) {
                if('id' == $attribName) {
                    $activityId = (string) $attribValue;
                } elseif('type' == $attribName) {
                    $activityType = (string) $attribValue;
                }
            }

            $courseType = 'http://adlnet.gov/expapi/activities/course';

            if($activityType == $courseType) {
                $entryPoint = (string) $activity->launch;

                $this->update([
                    'CID' => $courseId,
                    'entry_point' => $entryPoint,
                    'activity_id' => $activityId,
                ]);

                $result = true;

                break;
            }
        }

        $targetPath = PUBLIC_PATH . "/unmanaged/COURSES/course" . $courseId;
        $this->emptyDir($targetPath);
        $this->copyDir($unzipPath, $targetPath);

        return $result;
    }

    public function setListeners(array $listeners)
    {
        $this->listeners = $listeners;
    }

    public function delete($courseId)
    {
        // Удаляем структура
        $this->getService('CourseItem')->deleteBy($this->quoteInto('cid = ?', $courseId));

        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($courseId,HM_Tag_Ref_RefModel::TYPE_COURSE)));

        // Удаляем модули из library
        $collection = $this->getService('Library')->fetchAll($this->quoteInto('cid = ?', $courseId));
        if (count($collection)) {
            foreach($collection as $item) {
                $this->getService('Library')->delete($item->bid);
            }
        }

        // Удаляем связки из subjects_courses
        $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto('course_id = ?', (int) $courseId)
        );

        // Удаляем связки из files для импортированных модулей
        $this->getService('Files')->deleteBy(
            $this->quoteInto(['item_type = ?', ' AND item_id = ?'], [HM_Files_FilesModel::ITEM_TYPE_COURSES_FREE, (int) $courseId])
        );

        $result = parent::delete($courseId);
        if ($result == true)
        {
            $this->rmrf(PUBLIC_PATH . "/unmanaged/COURSES/course" . $courseId . "/{,.}[!.,!..]*");
            rmdir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $courseId);
        }
        return $result;

    }

    public function create_dirs($id)
    {

        $create = array();
        $ret = 0;

        if (! is_dir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id))
            if (! @mkdir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id, 0700))
                $ret = 1;
        if (! @chmod(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id, 0775))
            $ret = 2;
        if (! is_dir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/TESTS"))
            if (! @mkdir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/TESTS", 0700))
                $ret = 1;
        if (! @chmod(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/TESTS", 0775))
            $ret = 2;
        if (! is_dir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/webcam_room_" . $id))
            if (! @mkdir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/webcam_room_" . $id, 0700))
                $ret = 1;
        if (! @chmod(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/webcam_room_" . $id, 0775))
            $ret = 2;
        if (! is_dir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/mods"))
            if (! @mkdir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/mods", 0700))
                $ret = 1;
        if (! @chmod(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/mods", 0775))
            $ret = 2;
        if (! is_dir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/TESTS_ANW"))
            if (! @mkdir(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/TESTS_ANW", 0700))
                $ret = 1;
        if (! @chmod(PUBLIC_PATH . "/unmanaged/COURSES/course" . $id . "/TESTS_ANW", 0775))
            $ret = 2;
        if ($ret == 2)
            $ret = 0;

        return $ret;
    }

    public function insert($data, $unsetNull = true) {

        $result = parent::insert($data);

        $this->getService('CourseItem')->insert(
            array('title' => _('Пустой элемент'),
                  'cid'   => $result->CID,
                  'level' => 0,
                  'prev_ref' => -1
            )
        );

        if($result){
            $error = $this->create_dirs($result->CID);
        }
        return ($error) ? $error : $result;

    }

    public function rmrf($dir)
    {
        foreach ( glob($dir,GLOB_MARK|GLOB_BRACE) as $file )
        {
            if (is_dir($file))
            {
                $this->rmrf("$file{,.}[!.,!..]*");
                rmdir($file);
            } else
            {
                unlink($file);
            }
        }
    }

	public function assignClaimant($courseId, $studentId)
    {
        $course = $this->getOne($this->findDependence(array('Student', 'Claimant'), $courseId));
        if ($course) {
            if (!$course->isClaimant($studentId) && !$course->isStudent($studentId)) {
                return $this->getService('Claimant')->insert(
                    array(
                        'MID' => $studentId,
                        'CID' => $courseId,
                        'Teacher' => 0
                    )
                );
            }
        }
    }

    public function getParentItem($itemId)
    {
        return $this->getService('CourseItem')->getParent($itemId);
    }

    public function getChildrenLevelItems($courseId, $parent = -1)
    {
        return $this->getService('CourseItem')->getChildrenLevel($courseId, $parent);
    }

    public function isTeacher($courseId, $userId){

        $parentSubjects = $this->getService('SubjectCourse')->getCourseParent($courseId);

        $res = false;

        foreach($parentSubjects as $val){
            if($this->getService('Subject')->isTeacher($val->subject_id, $userId)){
                $res = true;
            }
        }
        return $res;
    }

    public function isStudent($courseId, $userId){
        $parentSubjects = $this->getService('SubjectCourse')->getCourseParent($courseId);

        $res = false;

        foreach($parentSubjects as $val){
            if($this->getService('Subject')->isStudent($val->subject_id, $userId)){
                $res = true;
                break;
            }
        }
        return $res;

    }

    public function getDevelopers($courseId){
        $res = $this->getService('User')->fetchAllDependenceJoinInner('Developer', 'Developer.cid = '. (int)$this->CID);
        $result =array();
        if($res){
            foreach($res as $val){
                $result[] = $val->getName() ;
            }
            return $result;
        }

        return false;
    }

    public function copy($fromSubjectId, $toSubjectId)
    {
        $result = array(); // возвращается массив ассоциаций "оригиналИД"=>"копияИД"

        $links = $this->fetchAll($this->quoteInto('subject_id = ?', $fromSubjectId));
        $this->deleteBy($this->quoteInto('subject_id = ?', $toSubjectId));

        if (count($links)) {

            //копирование локальных модулей
            $courseIds    = $links->getList('CID');
            $localCourses = $this->fetchAll($this->quoteInto(array('CID IN (?)', ' AND chain <> ? AND chain IS NOT NULL'), array($courseIds, 0)));

            if (count($localCourses)) {
                /** @var HM_Course_CourseModel $localItem */
                foreach ($localCourses as $localItem) {

                    $oldID = $localItem->CID;
                    $path = $localItem->getPath();
                    $data = $localItem->getValues();

                    unset($data['CID']);
                    $data['chain'] = $toSubjectId;
                    $data['subject_id'] = $toSubjectId;
                    $data['tree']  = '';

                    $newCourse = $this->insert($data);

                    // копирование содержимого курса
                    $this->getService('CourseItem')->copyItem($oldID, $newCourse->CID);

                    $newPath = substr($path, 0, -strlen($localItem->CID)) .$newCourse->CID;
                    try {
                        $this->copyDir($path, $newPath, $strtolower = false);
                    } catch (Exception $e){
                        $this->getService('CourseItem')->delete($newCourse->CID);
                    }

                    if ($newCourse) {
                        $courseIds[$newCourse->CID] = $newCourse->CID;
                        $result[$oldID]             = $newCourse->CID;
                    }
                }
            }
        }

        return $result;
    }

    public function publish($courseId)
    {
        $course = $this->getOne($this->getService('Course')->fetchAll(sprintf('cid = %d', $courseId)));
        if ($course) {
            $errors = array();
            if (!in_array($course->Status, array(HM_Course_CourseModel::STATUS_DEVELOPED, HM_Course_CourseModel::STATUS_ARCHIVED))) {
                $errors[] = _('курс уже опубликован');
            }
            if (!strlen($course->Title)) {
                $errors[] = _('не заполнено название курса');
            }
            //if (!strlen($course->Description)) {
            //    $errors[] = _('не заполнено описание курса');
            //}

            if (!count($errors)) {
                $data = array(
                    'CID' => $courseId,
                    'Status' => HM_Course_CourseModel::STATUS_ACTIVE
                );

                $this->getService('Course')->update($data);
                return true;
            }

            throw new HM_Exception(sprintf(_('Курс %s не опубликован. %s.'), $course->Title, join(', ', $errors)));
        }

        return false;

    }

    public function copyDir($strDirSource, $strDirDest, $strtolower = false)
    {
        $ret = $failures = array();
        if (substr($strDirDest, -1) != '/') {
            $strDirDest .= '/';
        }
        if ($handle = opendir($strDirSource)) {
            while (false !== ($file = readdir($handle))) {
                $strLowerFile = ($strtolower) ? strtolower($file) : $file;
                if (is_dir($strDirSource."/".$file)) {
                    if (($file != "..") && ($file != ".")) {
                        if (!mkdir($strDirDest.$strLowerFile,0775)) {
                            throw new HM_Exception(sprintf(_('Невозможно создать каталог %s'), $strDirDest.$strLowerFile));
                        }
                        if (!chmod($strDirDest.$strLowerFile,0775)) {
                            throw new HM_Exception(sprintf(_('Невозможно установить права на каталог %s'), $strDirDest.$strLowerFile));
                        }

                        $ret = array_merge($ret, $this->copyDir($strDirSource."/".$file, $strDirDest.$strLowerFile."/", $strtolower));
                    }
                }
                else {
                    if (!copy($strDirSource."/".$file, $strDirDest.$strLowerFile)) {
                        $failures[] = $strDirDest.$strLowerFile;
                    }
                    $ret[] = $strDirDest.$strLowerFile;
                }
            }
            closedir($handle);
        } else {
            throw new HM_Exception(sprintf(_('Невозможно прочитать каталог %s'), $strDirSource));
        }

        if (count($failures)) {
            throw new HM_Exception(sprintf(_('Невозможно скопировать файл(ы): %s'), implode(', ', $failures)));
        }
        return $ret;
    }

    public function emptyDir($dir)
    {
        if (is_dir($dir)) {
            foreach (glob($dir . '\*') as $subDir) {
                if (is_dir($subDir)) {
                    $this->emptyDir($subDir);
                    rmdir($subDir);
                } else {
                    unlink($subDir);
                }
            }
        }
    }

    public function removeDir($dir)
    {
        $this->rmrf($dir . '/{,.}[!.,!..]*');
        rmdir($dir);
        return true;
    }

    public function emulate($courseId, $emulateMode)
    {
        $course = $this->getOne($this->find($courseId));
        if ($course) {

            if ($course->emulate == $emulateMode) {
                return true;
            }

            $emulatePath = $course->getEmulatePath($emulateMode);

            if (file_exists($emulatePath) && is_dir($emulatePath)) {
                $this->rmrf($emulatePath);
                rmdir($emulatePath);
            }

            try {
            /*if (!mkdir($emulatePath, 0700)) {
                    throw new HM_Exception(sprintf(_("Невозможно создать каталог %s"), $emulatePath));
                }

                if (!chmod($emulatePath, 0775)) {
                    throw new HM_Exception(sprintf(_("Невозможно установить права на каталог %s"), $emulatePath));
            }*/

            //$this->copyDir($course->getPath(), $emulatePath);
            if (!rename($course->getPath(), $emulatePath)) {
                throw new HM_Exception(sprintf(_("Невозможно переименовать в %s"), $emulatePath));
            }

            $collection = $this->getService('Library')->fetchAll(
                $this->quoteInto('cid = ?', $courseId)
            );

            $this->getMapper()->getAdapter()->getAdapter()->beginTransaction();

            $emulatePathPiece = $course->getEmulatePathPiece($emulateMode);
            if (count($collection)) {
                foreach($collection as $item) {
                    $item->filename = preg_replace('/emulate-ie[0-9]+\//', '', $item->filename);
                    if ($emulatePathPiece) {
                        $item->filename = str_replace('COURSES/', 'COURSES/'.$emulatePathPiece.'/', $item->filename);
                    }
                    $this->getService('Library')->update($item->getValues());
                }
            }

            $this->getService('Course')->update(array('CID' => $courseId, 'emulate' => $emulateMode));

            $this->getMapper()->getAdapter()->getAdapter()->commit();

            } catch(HM_Exception $e) {
                $this->rmrf($emulatePath);
                throw $e;
                //return false;
            } catch(Zend_Db_Exception $e) {
                $this->getMapper()->getAdapter()->rollBack();
                $this->rmrf($emulatePath);
                rmdir($emulatePath);
                throw $e;
            }

        }
    }

    /**
     * @param $title
     * @param $subjectId
     * @return HM_Model_Abstract
     * @throws HM_Exception
     */
    public function createDefault($title, $subjectId = false)
    {
        if(!strlen($title)) {
            throw new HM_Exception(_('Ошибка при создании учебного модуля'));
        }
        $defaults = $this->getDefaults();
        $defaults['title'] = $title;
        $defaults['format'] = HM_Course_CourseModel::FORMAT_FREE;
        $defaults['chain'] = $defaults['subject_id'] = $subjectId;

        $result = $this->getService('Course')->insert($defaults);

        return $result;
    }

    public function createLesson($subjectId, $courseId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_COURSE, '%module_id='.$courseId.';%', $subjectId)
            )
        );

        //if (!count($lessons)) {
            $course = $this->getOne($this->getService('Course')->find($courseId));
            if ($course) {
                $values = array(
                    'title' => $course->Title,
                    'descript' => $course->Description,
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => 1,
                    'createDate' => date('Y-m-d H:i:s'),
                    'typeID' => HM_Event_EventModel::TYPE_COURSE,
                    'vedomost' => 1,
                    'CID' => $subjectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'teacher' => 0,
                    'params' => 'module_id='.(int) $course->CID.';',
                    // 5G
                    // продублируем в отдельное человеческое поле,
                    // чтобы в будущем отказаться от "params"
                    'material_id' => $course->CID,
                    'all' => 1,
                    'cond_sheid' => '',
                    'cond_mark' => '',
                    'cond_progress' => 0,
                    'cond_avgbal' => 0,
                    'cond_sumbal' => 0,
                    'cond_operation' => 0,
                    'isfree' => HM_Lesson_LessonModel::MODE_PLAN, // deprecated, для обратной совместимости
                );
                $lesson = $this->getService('Lesson')->insert($values);

                $students = $lesson->getService()->getAvailableStudents($subjectId);
                if (is_array($students) && count($students)) {
                    $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                }
//[ES!!!] array('lesson' => $lesson))
            }
//        } else {
//            $lesson = $lessons->current();
//        }
        return $lesson;
    }


    public function clearLesson($subject, $courseId)
    {
        if ($subject == null) {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('(typeID = ?', ' AND params LIKE ?) OR ', '(typeID = ?', ' AND params LIKE ?)'),
                    array(
                        HM_Event_EventModel::TYPE_COURSE,
                        '%module_id='.$courseId.';%',
                        HM_Event_EventModel::TYPE_LECTURE,
                        '%course_id='.$courseId.';%',
            )));
        } else {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('((typeID = ?', ' AND params LIKE ?) OR ', '(typeID = ?', ' AND params LIKE ?))', ' AND CID = ?'),
                    array(
                        HM_Event_EventModel::TYPE_COURSE,
                        '%module_id='.$courseId.';%',
                        HM_Event_EventModel::TYPE_LECTURE, // нужно подчистить также и все занятия с типом "раздел уч.модуля"
                        '%course_id='.$courseId.';%',
                        $subject->subid,
             )));
        }

        if (count($lessons)) {
            /** @var HM_Lesson_LessonService $lessonService */
            $lessonService = $this->getService('Lesson');
            foreach($lessons as $lesson) {
                $lessonService->resetMaterialFields($lesson->SHEID);
            }
        }

    }

    public function getDefaults()
    {
        return array(
            'Description' => '',
            'Status' => HM_Course_CourseModel::STATUS_STUDYONLY,
            'provider' => 0,
            'developStatus' => 0,
            'lastUpdateDate' => $this->getDateTime(),
            'createDate' => $this->getDateTime(),
            'TypeDes' => 0,
            'chain' => 0,
            'did' => 0,
            'sequence' => 0,
            'is_module_need_check' => 0,
            'has_tree' => 0,
            'new_window' => 0,
            'emulate' => HM_Course_CourseModel::EMULATE_IE_NONE ,
            'longtime' => 0,
            'format' => HM_Course_CourseModel::FORMAT_FREE,
            'author' => $this->getService('User')->getCurrentUserId()
        );
    }

    public function getRelatedUserList($event) {
        return $this->getListeners();
    }

    public function getCourse($courseId)
    {
        $cacheName = 'getCourse';

        if ($this->_cache[$cacheName][$courseId]) {
            $output = $this->_cache[$cacheName][$courseId];
        } else {

            $output = $this->fetchRow(array('CID = ?' => (int)$courseId));
            $this->_cache[$cacheName][$courseId] = $output;
        }

        return $output;
    }
}