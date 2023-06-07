<?php
class Offline_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    const ACTION_SKIP_ALL = 1;
    const ACTION_SKIP_FILES = 2;
    const ACTION_OVERRIDE = 3;
    const ACTION_COPY_FROM_COURSES = 4;
    const ACTION_COPY_FROM_RESOURSES = 5;
    
    
    public function indexAction()
    {
        $select = $this->getService('Offline')->getSelect();
        $select->from(
                    array(
                        't' => 'offlines'
                    ), 
                    array(
                        'id'      => 't.id',
                        'title'   => 't.title',
                        'created' => 't.created'
                    )
               )
               ->order('created DESC');
               
       $grid = $this->getGrid($select, 
                              array(
                                 'id'    => array('hidden' => true),
                                 'title'   => array('title'  => _('Название')),
                                 'created' => array(
                                     'title'  => _('Создана'), 
                                    'format' => array('date', array('date_format' => HM_Locale_Format::getDateTimeFormat()))
                                 )
                              ), null
        );
         
        $grid->addAction(array('module'     => 'offline', 
                               'controller' => 'list', 
                               'action'     => 'download'
                         ),
                         array('id'),
                         _('Скачать')
        );
        
        $grid->addAction(array('module'     => 'offline', 
                               'controller' => 'list', 
                               'action'     => 'delete'
                         ),
                         array('id'),
                         $this->view->svgIcon('delete', 'Удалить')
        );
        
        /*
        $grid->addMassAction(array('module'     => 'test', 
                                   'controller' => 'list', 
                                   'action'     => 'delete-by'
                             ),
                             _('Удалить'),
                             _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
        */
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }
    
    public function newAction()
    {
    	$courseIds = $resourceIds = array();
        $subjectId = $this->_getParam('subid', 0);
        $collection = $this->getService('Subject')->fetchAllHybrid('ResourceAssign', 'Course', 'CourseAssign', array('subid = ?' => $subjectId));
        if (count($collection)) {
            
            $subject = $this->getService('Subject')->getOne($collection);
            if (count($subject->courses)) {
            	$courseIds = $subject->courses->getList('CID');
            	$emulate = $subject->courses->getList('CID', 'emulate');
                $courseItemService = $this->getService('CourseItem');

                $courseItems = $courseItemService->fetchAllDependenceJoinInner(
                    'Module',
                    $courseItemService->quoteInto('self.cid IN (?)', $courseIds)
                );
                $allCourseIds = $courseIds;
                foreach($courseItems as $courseItem){
                    if($courseItem->module[0]){
                        if($courseItem->module[0]->filename){
                            $matches = array();
                            $foundMatch = preg_match(
                                '/^.*COURSES\/course([0-9]+).*$/',
                                $courseItem->module[0]->filename,
                                $matches
                            );
                            if($foundMatch){
                                $courseId = $matches[1];
                                $allCourseIds[] = $courseId;
                            }
                        }
                    }

                }
                $allCourseIds = array_unique($allCourseIds);
            }
            if (count($subject->resources)) $resourceIds = $subject->resources->getList('resource_id');
            
            /* Долго
            $free_space = disk_free_space(APPLICATION_PATH . '/../..');
            $total_size = dir_size(APPLICATION_PATH . '/../..');
            
            if ($free_space < $total_space) {
                $this->_flashMessenger->addMessage(array('message' => _('Оффлайн-версия не сформирована, недостаточно места'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                $this->_redirector->gotoSimple('index');
            }
            */
        
            /* ZIP */
            $error = false;
            $title = sprintf("subject_%s_%s", $subjectId, date('Y_m_d_H_i_s'));
            $zipPath = APPLICATION_PATH . '/../public/upload/offline/' . $title . '.zip';
            $dumpPath = APPLICATION_PATH . '/../data/offline/offline_' . $title . '.sql';
            $commonDumpPath = APPLICATION_PATH . '/../data/offline/common.dmp';
            $codePath = Zend_Registry::get('config')->path->offline->source;

//            if((Zend_Registry::get('config')->resources->db->adapter == 'mssql') || (Zend_Registry::get('config')->resources->db->adapter == 'pdo_mssql') || (Zend_Registry::get('config')->resources->db->adapter == 'sqlsrv')){
//                $fullDump = file_get_contents(APPLICATION_PATH . '/../!dumps/mssql/db_dump.sql');
//                $fullDump3 = file_get_contents(APPLICATION_PATH . '/../!dumps/mssql/db_dump3.sql');
//            } else {
//                // не реализовано для oracle

                $fullDump = file_get_contents(APPLICATION_PATH . '/../!dumps/mysql/db_dump.sql');
                $fullDump3 = file_get_contents(APPLICATION_PATH . '/../!dumps/mysql/db_dump3.sql');
                if (file_exists($commonDumpPath)) {
                    unlink($commonDumpPath);
                }
                $fullDump = preg_replace('/DROP\ +TABLE\ +IF\ +EXISTS.+;/', '', $fullDump);
                $commonDumpFile = fopen($commonDumpPath, 'w');
                $fullDump = str_replace('TYPE=','ENGINE=',$fullDump);
                fwrite($commonDumpFile, str_replace(array('CREATE TABLE', 'CREATE  TABLE'), 'CREATE TABLE IF NOT EXISTS', $fullDump) . " \r\n" . $fullDump3);
                fclose($commonDumpFile);
//            }
            
            if (strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql') !== false) {
                // никакого шаманства, дампим рабочую базу
                $mysqlUser = Zend_Registry::get('config')->resources->db->params->username;
                $mysqlPass = Zend_Registry::get('config')->resources->db->params->password;
                $mysqlBase = Zend_Registry::get('config')->resources->db->params->dbname;
            } elseif (Zend_Registry::get('config')->resources->db->adapter == 'oracle') {
                // мигрируем в mysql и затем дампим
                
                require_once(APPLICATION_PATH . '/../public/unmanaged/adodb/adodb.inc.php'); 
                require_once(APPLICATION_PATH . '/../public/unmanaged/ora2my.php'); 
                ora2my();
                
                $mysqlUser = Zend_Registry::get('config')->offlines->db->params->username;
                $mysqlPass = Zend_Registry::get('config')->offlines->db->params->password;
                $mysqlBase = Zend_Registry::get('config')->offlines->db->params->dbname;
            } elseif ((Zend_Registry::get('config')->resources->db->adapter == 'mssql') || (Zend_Registry::get('config')->resources->db->adapter == 'pdo_mssql') || (Zend_Registry::get('config')->resources->db->adapter == 'sqlsrv')) {
                require_once(APPLICATION_PATH . '/../public/unmanaged/adodb/adodb.inc.php'); 
                require_once(APPLICATION_PATH . '/../public/unmanaged/ms2my.php'); 
                ms2my();
                
                $mysqlUser = Zend_Registry::get('config')->offlines->db->params->username;
                $mysqlPass = Zend_Registry::get('config')->offlines->db->params->password;
                $mysqlBase = Zend_Registry::get('config')->offlines->db->params->dbname;
						} else {
                // неизвестный тип БД
                return true;            
            }  
            
	    
            $mysqlDump = Zend_Registry::get('config')->offlines->db->params->mysqldump;
            $onlyTables = $this->getService('Offline')->getOfflineTables();
            $mysqlHost = Zend_Registry::get('config')->offlines->db->params->host;
            if(!$mysqlHost){
                $mysqlHost = '127.0.0.1';
            }
            @system (sprintf('"%s" --hex-blob --host=%s -u %s --password=%s %s %s > "%s"', $mysqlDump, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlBase, $onlyTables, $dumpPath));
	    
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZIPARCHIVE::CREATE) !== TRUE) {
                $error = true;
            }
            
            // exe
            $path = APPLICATION_PATH . '/../data/offline/course.exe';
            if (!file_exists($path)) {
                $error = true;
            } else {
                $zip->addFile($path, 'course.exe');
            }
//            if (!$zip->addFile(APPLICATION_PATH . '/../data/offline/course.exe', 'course.exe')) {
//                $error = true;
//            }

            // dump
            if (!file_exists($dumpPath)) {
                $error = true;
            } else {
                $zip->addFile($dumpPath, basename($dumpPath));
            }
//            if (!$zip->addFile($dumpPath, basename($dumpPath))) {
//                $error = true;
//            }
	    // commondump
            if (!file_exists($commonDumpPath)) {
                $error = true;
            } else {
                $zip->addFile($commonDumpPath, basename($commonDumpPath));
            }
//            if (!$zip->addFile($commonDumpPath, basename($commonDumpPath))) {
//                $error = true;
//            }

            if (!$zip->addEmptyDir('course')) {
                $error = true;
            }
            
            // код
            $iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($codePath));
            $count = 0;
            foreach ($iterator as $key=>$value) {

                if ($error) break;
                if ($value->getFilename() == '.' || $value->getFilename() == '..') continue;
                if (strpos(realpath($key), 'config.ini') !== false) {
                    $str = file_get_contents(realpath($key));
                    $str = str_replace('offline = 0', "offline = {$subjectId}", $str);
                    if (!$zip->addFromString('course' . str_replace(realpath($codePath), '', realpath($key)), $str)) {
                        $error = true;
                    }
                    continue;
                }

                $path = realpath($key);
                $name = 'course' . str_replace(realpath($codePath), '', realpath($key));
                if (!file_exists($path)) {
                    $error = true;
                } else {
                    $zip->addFile($path, $name);
                }
//                if (!$zip->addFile(realpath($key), 'course' . str_replace(realpath($codePath), '', realpath($key)))) {
//                    $error = true;
//                }
                if(($count++) == 200) {
                    $error = !$this->_stashSave($zip, $zipPath);
                    $count = 0;
                }
            }
            
            $error = !$this->_stashSave($zip, $zipPath);           
            
            $materialPaths = array(
                APPLICATION_PATH . '/../data/upload/resource' => self::ACTION_COPY_FROM_RESOURSES,
                APPLICATION_PATH . '/../public/upload/resources' => self::ACTION_COPY_FROM_RESOURSES,
                APPLICATION_PATH . '/../public/unmanaged/COURSES' => self::ACTION_COPY_FROM_COURSES,
            );
            
            // материалы
            foreach ($materialPaths as $materialPath => $action) {
                
                $iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($materialPath));
                $count = 0;
                foreach ($iterator as $key => $value) {
                    if ($error) break;

                    if ($value->getFilename() == '.' || $value->getFilename() == '..') continue;

                    $itemPath = str_replace("\\", '/', realpath($key));
                    $isUsed = false;
                    switch ($action) {
                       case self::ACTION_COPY_FROM_COURSES:
                            // оставляем только используемые курсы
                            foreach ($allCourseIds/*$courseIds*/ as $courseId) {
                            	$emulatePath = '';
                            	if (isset($emulate[$courseId]) && $emulate[$courseId]) {
                            		$emulatePath = '/emulate-ie' . $emulate[$courseId];
                            	}                            	
                                if ($courseId && (strpos(realpath($key), realpath("{$materialPath}/{$emulatePath}/course{$courseId}")) !== false)) {
                                	$isUsed = true;
                                	continue 2;
                                } 
                            }
                            break;
                       case self::ACTION_COPY_FROM_RESOURSES:
                            // оставляем только используемые ресурсы
                            // @todo: составные ресурсы из нескольких файлов сейчас не попадают
                            foreach ($resourceIds as $resourceId) {
                                if ($resourceId && (strpos(realpath($key), realpath("{$materialPath}/{$resourceId}")) !== false)) {
                                	$isUsed = true;
                                	continue 2;
                                }
                            }
                            break;
                    }
                    
                    if (!$isUsed) continue;

                    $path = realpath($key);
                    $name = 'course' . str_replace(realpath(APPLICATION_PATH . '/../'), '', realpath($key));
                    if (!file_exists($path)) {
                        $error = false;
                    } else {
                        $zip->addFile($path, $name);
                    }
//                    if (!$zip->addFile(realpath($key), 'course' . str_replace(realpath(APPLICATION_PATH . '/../'), '', realpath($key)))) {
//                        $error = false;
//                    }
                    
                    if(($count++) == 200) {
                        $error = !$this->_stashSave($zip, $zipPath);
                        $count = 0;
                    }
                    
                }
            }

            //файлы вопросов на аудирование
            $subjectQuestService = $this->getService('SubjectQuest');
            $filesService = $this->getService('Files');
            $select = $subjectQuestService->getSelect();

            $select->from(array('sq' => 'subjects_quests'), array());
            $select->joinInner(array('q' => 'questionnaires'), 'sq.quest_id = q.quest_id', array());
            $select->joinInner(array('qqq' => 'quest_question_quests'), 'q.quest_id = qqq.quest_id', array());
            $select->joinInner(array('qq' => 'quest_questions'), 'qqq.question_id = qq.question_id', array('data'));

            $select->where('sq.subject_id = ?', $subjectId);
            $select->where('qq.type IN (?)',    array(
                HM_Quest_Question_QuestionModel::TYPE_LISTENING_LETTER,
                HM_Quest_Question_QuestionModel::TYPE_LISTENING_WORD,
            ));

            $questions = $select->query()->fetchAll();

            $fileIds = array();
            if($questions){
                foreach($questions as $question){
                    $data = unserialize($question['data']);
                    if($data){
                        $fileIds[] = $data['file_id'];
                    }
                }
            }


            if(count($fileIds)){
                foreach($fileIds as $fileId) {
                    $path = HM_Files_FilesService::getPath($fileId);
                    $name = 'course' . str_replace(realpath(APPLICATION_PATH . '/../'), '', $path);
                    if (file_exists($path) && is_file($path)) {
                        $zip->addFile($path, $name);
                    }
                }
            }

            if (!$zip->close()) {
                $error = true;
            } 
            
            if (!$error) {
                
                $this->getService('Offline')->deleteBy(array('subject_id = ?' => $subjectId));
                $offline = $this->getService('Offline')->insert(array(
                        'subject_id' => $subjectId,
                        'title' => $title,
                        'created' => date('Y-m-d H:i:s')
                ));
                
                $this->_flashMessenger->addMessage(_('Оффлайн-версия успешно сформирована'));
                $this->_redirector->gotoSimple('index', 'list', 'subject');
            } else {
                $this->getService('Offline')->delete($offline->id);

                $filename = APPLICATION_PATH . '/../public/upload/offline/' . $title . '.zip';
                if (file_exists($filename)) {
                    @unlink($filename);
                }
                    
                $this->_flashMessenger->addMessage(array('message' => _('Оффлайн-версия не сформирована'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                $this->_redirector->gotoSimple('index', 'list', 'subject');
            }
        } 
    }
    
    protected function _stashSave(&$zip, $zipPath)
    {
        $error = false;
        if (!$zip->close()) {
            $error = true;
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $error = true;
        }
        return !$error;
    }

    public function importAction()
    {
        $request = $this->getRequest();
        $form = new HM_Form_Import();
        
        if ($request->isPost() && $form->isValid($params = $request->getParams())) {
            if($form->data->isUploaded() && $form->data->receive() && $form->data->isReceived()){
                $data = file_get_contents($form->data->getFileName());
                
                try {
                    $data = offline_crypt($data, true);                   
                } catch (CException $e) {
                }
                
                $arr = @json_decode($data, true);
                if (is_array($arr)) {
                    if (is_array($arr['marks']) && count($arr['marks'])) {
                        foreach ($arr['marks'] as $value) {
                            $mark = $this->getOne($this->getService('LessonAssign')->fetchAll(
                                $this->getService('LessonAssign')->quoteInto(
                                    array('MID = ?', ' AND SSID = ?', ' AND SHEID = ?'),
                                    array($value['MID'], $value['SSID'], $value['SHEID'])
                                )
                            ));
                            if ($mark && (intval($value['V_STATUS']) > intval($mark->V_STATUS))) {
                                $this->getService('LessonAssign')->update($value);
                            }
                        }
                    }
                    
                    $stids = array();
                    
                    if (is_array($arr['loguser']) && count($arr['loguser'])) {
                        foreach ($arr['loguser'] as $value) {
                            $loguser = $this->getOne($this->getService('TestResult')->fetchAll(
                                $this->getService('TestResult')->quoteInto(
                                    array('mid = ?', ' AND tid = ?', ' AND cid = ?', ' AND start = ?'),
                                    array($value['mid'], $value['tid'], $value['cid'], $value['start'])
                                )
                            ));
                            
                            if (!$loguser) {
                                $stid = $value['stid'];
                                unset($value['stid']);
                                $tr = $this->getService('TestResult')->insert($value);
                                $stids[$stid] = $tr->stid;
                            }
                        }
                    }
                    
                    if (is_array($arr['logseance']) && count($arr['logseance'])) {
                        foreach ($arr['logseance'] as $value) {
                            $logseance = $this->getOne($this->getService('QuestionResult')->fetchAll(
                                $this->getService('QuestionResult')->quoteInto(
                                    array('mid = ?', ' AND cid = ?', ' AND time = ?', ' AND kod = ?', ' AND sheid = ?'),
                                    array($value['mid'], $value['cid'], $value['time'], $value['kod'], $value['sheid'])
                                )
                            ));

                            if (!$logseance) {
                                if (isset($stids[$value['stid']])) {
                                    $value['stid'] = $stids[$value['stid']];
                                    $value['attach'] = $value['attach'] ? $value['attach'] : 0;
                                    $this->getService('QuestionResult')->insert($value);
                                }
                            }
                        }
                    }

                    if (is_array($arr['scorm_tracklog']) && count($arr['scorm_tracklog'])) {
                        foreach ($arr['scorm_tracklog'] as $value) {
                            $scorm_tracklog = $this->getOne($this->getService('ScormTrack')->fetchAll(
                                $this->getService('ScormTrack')->quoteInto(
                                    array('mid = ?', ' AND trackID = ?', ' AND cid = ?'),
                                    array($value['mid'], $value['trackID'], $value['cid'])
                                )
                            ));
                            
                            if (!$scorm_tracklog) {
                                unset($value['trackID']);
                                $this->getService('ScormTrack')->insert($value);
                            }
                        }
                    }
                    
                    if (is_array($arr['testcount']) && count($arr['testcount'])) {
                        foreach ($arr['testcount'] as $value) {
                            $testcount = $this->getOne($this->getService('TestAttempt')->fetchAll(
                                $this->getService('TestAttempt')->quoteInto(
                                    array('mid = ?', ' AND tid = ?', ' AND cid = ?', ' AND qty = ?', ' AND lesson_id = ?'),
                                    array($value['mid'], $value['tid'], $value['cid'], $value['qty'], $value['lesson_id'])
                                )
                            ));
                            
                            if (!$testcount) {
                                $this->getService('TestAttempt')->insert($value);
                            } else {
                                if ($value['last'] > $testcount->last) {
                                    $this->getService('TestAttempt')->update($value);    
                                }
                            }
                        }
                    }
                    
                    $this->_flashMessenger->addMessage(_('Данные успешно импортированы'));
                    $this->_redirector->gotoSimple('index');
                }
            }
            $this->_flashMessenger->addMessage(array('message' => _('При загрузке данных произошла ошибка'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('import');
        } 
        
        $this->view->form = $form;
    }

    // @todo: убрать из public
    public function downloadAction() 
    {
        $id = (int) $this->_getParam('id', 0);
        $collection = $this->getService('Offline')->find($id);
        if (count($collection)) {
            $title = $collection->current()->title;
            $filename = APPLICATION_PATH . '/../public/upload/offline/' . $title . '.zip';
            if (file_exists($filename)) {
                $this->_helper->sendFile($filename, 'application/zip');
                exit();
            }
        }
        $this->_flashMessenger->addMessage(array('message' => _('Оффлайн-версии не существует'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        $this->_redirectToIndex();
    }
    
    public function deleteAction() 
    {
        $id = (int) $this->_getParam('id', 0);   
        $this->getService('Offline')->delete($id);
        
        $id = substr(md5(md5($id)), 0, 10);
        $filename = APPLICATION_PATH . '/../public/upload/offline/' . $id . '.zip';
        if (file_exists($filename)) {
            @unlink($filename);
        }
        
        $this->_flashMessenger->addMessage(_('Оффлайн-версия успешно удалена'));
        $this->_redirectToIndex();
    }

}

function offline_crypt($text, $decrypt = false) {
    $key = "#42^&bjsopa1!";

    if ($decrypt) {
        return base64_decode($text);
    } else {
        return base64_encode($text);
    }

    /* Open the cipher */
    $td = mcrypt_module_open('blowfish', '', 'ecb', '');

    /* Create the IV and determine the keysize length, use MCRYPT_RAND
     * on Windows instead */
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RANDOM);
    $ks = mcrypt_enc_get_key_size($td);

    /* Create key */
    $key = substr(md5($key), 0, $ks);

    /* Intialize encryption */
    mcrypt_generic_init($td, $key, $iv);

    if (!$decrypt) {
        $text = mcrypt_generic($td, $text);        
    }
    else {
        $text = mdecrypt_generic($td, $text);
        $text = trim($text);
    }
    
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    
    return $text;
}

function dir_size($dir) {
    $totalsize=0;
    if ($dirstream = @opendir($dir)) {
        while (false !== ($filename = readdir($dirstream))) {
            if ($filename!="." && $filename!="..") {
                if (is_file($dir."/".$filename)) {
                    $totalsize+=filesize($dir."/".$filename);
                }
                if (is_dir($dir."/".$filename)) {
                    $totalsize+=dir_size($dir."/".$filename);   
                }
            }
        }
        closedir($dirstream);
    }
    
    return $totalsize;
}

function decodeSize( $bytes )
{
    $types = array( 'б', 'Кб', 'Мб', 'Гб', 'Тб' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
}