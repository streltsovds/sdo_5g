<?php
class HM_Material_MaterialService extends HM_Service_Abstract
{
    /*
     * 5G
     * Интеллектуальная вставка того или иного типа контента,
     * в зависимости от входных данных
     */
    private function getDefaultResource($subjectId)
    {
        return [
            'location' => $subjectId ? HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL : HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
            'subject_id' => $subjectId,
            'status'     => $subjectId ? HM_Resource_ResourceModel::STATUS_STUDYONLY : HM_Resource_ResourceModel::STATUS_PUBLISHED, // при создании из курса достаточно видимости внутри курса
        ];
    }

    /**
     * @param $fileOrPath - путь к файлу
     * @param string|null $fileName - задать вручную имя файла для скачивания пользователем
     * @param string|null $title - задать вручную заголовок ресурса для отображения пользователю
     * @param int|null $subjectId
     * @param int|null $resourceId
     * @param bool $convertToPdf
     * @return HM_Model_Abstract
     * @throws Zend_Exception
     */
    public function importResource(
        $fileOrPath,
        $fileName = null,
        $title = null,
        $subjectId = null,
        $resourceId = null,
        $convertToPdf = false
    ) {
        $pathInfo = pathinfo($fileOrPath);
        $defaultResource = $this->getDefaultResource($subjectId);
        $fileName = $fileName ? : $pathInfo['basename'];
        $title = $title ? : $pathInfo['filename'];
        $unzipPath = $this->getService('Files')->unzip($fileOrPath, false);

        $fileType = HM_Files_FilesModel::getFileType($fileName);

        $resourceData = [
            'title'    => $title,
            'type'     => HM_Resource_ResourceModel::TYPE_EXTERNAL,
            'volume'   => HM_Files_FilesModel::toByteString(filesize($fileOrPath)),
            'filename' => $fileName,
            'filetype' => $fileType,
            'origin_filename' => '',
            'origin_filetype' => '',
        ];

        if ($resourceId) {
            $resource = $this->getService('Resource')->update(array_merge(
                $resourceData,
                ['resource_id' => $resourceId]
            ));
        } else {
            $resource = $this->getService('Resource')->insert(array_merge($defaultResource, $resourceData));
        }

        if ($index = self::autodetectHtmlSite($unzipPath)) {
            $resource = $this->getService('Resource')->update(array_merge($defaultResource, [
                'type' => HM_Resource_ResourceModel::TYPE_FILESET,
                'url' => $index,
                'resource_id' => $resource->resource_id,
            ]));

            $targetPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755);
            }

            $this->getService('Course')->copyDir($unzipPath, $targetPath);

        } else {
            if ($convertToPdf) {
                $converterPath = Zend_Registry::get('config')->src->officeConverter;
                $targetPath = realpath(Zend_Registry::get('config')->path->upload->resource);

// @todo: разобраться с этим - вешает openoffice на lms.elearn.ru
//
//                if(PHP_OS_FAMILY == 'Linux') {
//                    $command = 'export HOME=' . getcwd() . ' && ' . $command;
//                }

                if (!empty($converterPath)) {
                    $this->sendToOpenOffice($fileOrPath, $targetPath);

                    $pdfRealFilename = pathinfo($fileOrPath)['filename'].'.pdf';
                    $pdfDownloadFilename = pathinfo($fileName)['filename'].'.pdf';
                    $pdfPath = $targetPath . DIRECTORY_SEPARATOR . $pdfRealFilename;
                    $renameToPath = $targetPath . DIRECTORY_SEPARATOR . $resource->resource_id;
                    $originFileSrc = $resource->getOriginFileSrc();
                    rename($fileOrPath, $originFileSrc);
                    rename($pdfPath, $renameToPath);

                    $data = [
                        'resource_id' => $resource->resource_id,
                        'filetype' => HM_Files_FilesModel::FILETYPE_PDF,
                        'filename' => $pdfDownloadFilename,
                        'origin_filetype' => $resource->filetype,
                        'origin_filename' => $resource->filename,
                    ];
                    $this->getService('Resource')->update($data);

                    // Выставляем обновлённые данные в модель перед возвратом
                    foreach ($data as $key => $value) {
                        $resource->{$key} = $value;
                    }

                } else {

                    $this->unlinkTemp($fileOrPath);
                    $this->unlinkTemp($unzipPath);

                    throw new Exception('Converter not defined in config.ini');
                }
            } else {
                $renameToPath = $resource->getFilePath(true);
                rename($fileOrPath, $renameToPath);
            }
        }

        $this->unlinkTemp($fileOrPath);
        $this->unlinkTemp($unzipPath);

        return $resource;
    }

    public function insertQuest($fileOrString, $title, $subjectId = false)
    {
        $nameParts = explode('_', $title);
        if (count($nameParts) > 1) unset($nameParts[count($nameParts) - 1]);
        $name = implode('_', $nameParts);
        $test = $this->getService('Quest')->insert([
            'creator_role'               => $this->getService('User')->getCurrentUserRole(),
            'subject_id'                 => $subjectId ?: 0,
            'type'                       => HM_Quest_QuestModel::TYPE_TEST,
            'name'                       => $name,
            'description'                => '',
            'info'                       => '',
            'comments'                   => '',
            'status'                     => 1,
            'mode_display'               => 0,
            'mode_display_questions'     => null,
            'mode_display_clusters'      => null,
            'mode_selection'             => 0,
            'mode_selection_questions'   => '',
            'mode_selection_all_shuffle' => 0,
            'limit_attempts'             => '',
            'limit_time'                 => '',
            'mode_test_page'             => 0,
            'show_log'                   => 0,
            'show_result'                => 1,
            'mode_self_test'             => 0
        ]);

        return $this->importQuestions($fileOrString, $subjectId, $test);
    }

    public function insertEau3Quests($archiveSrc, $subjectId = null)
    {
        $result = false;
        $unzipPath = $this->getService('Files')->unzip($archiveSrc, false);
        if(self::autodetectScorm($unzipPath)) {
            $importManager = new HM_Quest_Import_Manager();
            $this->getService('QuestEau3')->setFileName(realpath($archiveSrc));
            $importManager->init($this->getService('QuestEau3')->fetchAll(), $archiveSrc, true);
            $result = $importManager->import($subjectId);
        }

        return $result;
    }

    public function insert($fileOrString, $title = '', $subjectId = false)
    {
        $unzipPath        = '';
        $fileType         = HM_Files_FilesModel::FILETYPE_UNKNOWN;
        $pathInfo         = [];

        if (is_file($fileOrString) && is_readable($fileOrString)) {
            $pathInfo = pathinfo($fileOrString);
            $unzipPath = $this->getService('Files')->unzip($fileOrString, false);
            $fileType = HM_Files_FilesModel::getFileType($fileOrString);
        }

        $defaultCourse = [
            'Status'         => HM_Course_CourseModel::STATUS_STUDYONLY,
            'lastUpdateDate' => date('Y-m-d'),
            'createDate'     => date('Y-m-d'),
            'new_window'     => 0,
            'chain'          => $subjectId ? : 0, // так было в 4.x
            'subject_id'     => $subjectId ? : 0,
            'title' => $title ? : $pathInfo['filename'],
        ];

        $defaultResource = $this->getDefaultResource($subjectId);

        if (!empty($unzipPath)) {

            /** @var HM_Course_CourseService $courseService */
            $courseService = $this->getService('Course');

            if (self::autodetectScorm($unzipPath)) {

                $course = $courseService->insert(array_merge($defaultCourse, [
                    'format' => HM_Course_CourseModel::FORMAT_SCORM,
                ]));

                $courseService->importScorm($course->CID, $unzipPath);
                // overwrite
                if (!empty($title)) {
                    $course->title = $title;
                    $courseService->update($course->getData());
                }

                return $course;

            } elseif (self::autodetectTincan($unzipPath)) {

                $course = $courseService->insert(array_merge($defaultCourse, [
                    'format' => HM_Course_CourseModel::FORMAT_TINCAN,
                ]));

                $courseService->importTincan($course->CID, $unzipPath);
                // overwrite
                if (!empty($title)) {
                    $course->title = $title;
                    $courseService->update($course->getData());
                }

                return $course;

            } elseif ($index = self::autodetectHtmlSite($unzipPath)) {

                $resource = $this->getService('Resource')->insert(array_merge($defaultResource, [
                    'title' => $title,
                    'type' => HM_Resource_ResourceModel::TYPE_FILESET,
                    'url' => $index,
                ]));

                $targetPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755);
                }

                $courseService->copyDir($unzipPath, $targetPath);

                return $resource;
            } elseif ($index = self::autodetectFree($unzipPath)) {

                $course = $courseService->insert(array_merge($defaultCourse, [
                    'format' => HM_Course_CourseModel::FORMAT_FREE,
                ]));

                $courseService->importFree($course->CID, $unzipPath);
                // overwrite
                if (!empty($title)) {
                    $course->title = $title;
                    $courseService->update($course->getData());
                }

                return $course;
            } else {

                $needConvertToPdf = $this->needConvertToPdf($fileOrString);

                // Походу придётся дважды распаковывать
                // Потому что при передаче $unzipPath ломается название ресурса и путей, а так меньше путаницы
                $resource = $this->importResource($fileOrString, null, $title, $subjectId, null, $needConvertToPdf);

                $this->unlinkTemp($fileOrString);
                $this->unlinkTemp($unzipPath);

                return $resource;

                throw new HM_Exception_Upload(_('Некорректный формат файла .zip'));
            }
        }

        if (is_file($fileOrString) && is_readable($fileOrString)) {

            $needConvertToPdf = $this->needConvertToPdf($fileOrString);
            if (in_array($fileType, [HM_Files_FilesModel::FILETYPE_XLSX, HM_Files_FilesModel::FILETYPE_TEXT])) {

                switch ($fileType) {
                    case HM_Files_FilesModel::FILETYPE_XLSX:

                        /** @var HM_Quest_Question_Import_Excel_ExcelService $service */
                        $service = $this->getService('QuestQuestionExcel');
                        break;

                    case HM_Files_FilesModel::FILETYPE_TEXT:

                        /** @var HM_Quest_Question_Import_Txt_TxtService $service */
                        $service = $this->getService('QuestQuestionTxt');
                        break;
                }

                $unlinkTempFile = false;
                if ($service->isTest($fileOrString, $unlinkTempFile) && !$needConvertToPdf) {
                    return $this->insertQuest($fileOrString, $title, $subjectId);
                } else {
                    return $this->importResource($fileOrString, null, $title, $subjectId, null, $needConvertToPdf);
                }
            } else {
                return $this->importResource($fileOrString, null, $title, $subjectId, null, $needConvertToPdf);
            }

        } elseif (is_string($fileOrString) && !empty($fileOrString)) {
            $string = trim($fileOrString);
            if ($this->autodetectUrl($string)) {
                $resource = $this->getService('Resource')->insert(array_merge($defaultResource, [
                    'title' => $title ? : $string,
                    'url' => trim($string),
                    'type' => HM_Resource_ResourceModel::TYPE_URL,
                ]));
                return $resource;
            } else {
                $resource = $this->getService('Resource')->insert(array_merge($defaultResource, [
                    'title' => $title ? : _('Информационный ресурс'),
                    'content' => trim($string),
                    'type' => HM_Resource_ResourceModel::TYPE_HTML,
                ]));

                return $resource;
            }
        }

        return false;
    }

    static public function autodetectScorm($path)
    {
        $manifest = "{$path}/imsmanifest.xml";
        return file_exists($manifest) && is_readable($manifest);
    }

    static public function autodetectTincan($path)
    {
        $manifest = "{$path}/tincan.xml";
        return file_exists($manifest) && is_readable($manifest);
    }

    static public function autodetectFree($path)
    {
        $manifest = "{$path}/index.csv";
        return file_exists($manifest) && is_readable($manifest);
    }

    static public function autodetectHtmlSite($path)
    {
        $indexes = array(
            'index.htm',
            'index.html',
            'default.htm',
            'default.html',
            'start.htm',
            'start.html',
        );

        foreach ($indexes as $index) {
            $fullPath = "{$path}/{$index}";
            if (file_exists($fullPath) && is_readable($fullPath)) {
                return $index;
            }
        }

        return false;
    }

    static public function autodetectUrl($value)
    {
        // @todo: интеллектуализировать
        return strpos(trim($value), 'http') === 0;
    }

    public function getMaterialId($material)
    {
        $result = false;

        switch (true) {
            case $material instanceof HM_Quest_QuestModel:
                $result = $material->quest_id;
                break;
            case $material instanceof HM_Resource_ResourceModel:
                $result = $material->resource_id;
                break;
            case $material instanceof HM_Course_CourseModel:
                $result = $material->CID;
                break;
            case $material instanceof HM_Task_TaskModel:
                $result = $material->task_id;
                break;
        }

        return $result;
    }

    public function getSubjectMaterials($subjectId, $allowedTypes = null)
    {
        $defaultAllowedTypes = [
            HM_Event_EventModel::TYPE_COURSE,
            HM_Event_EventModel::TYPE_RESOURCE,
            HM_Event_EventModel::TYPE_TEST,
            HM_Event_EventModel::TYPE_POLL,
            HM_Event_EventModel::TYPE_TASK,
        ];

        $allowedTypes = is_array($allowedTypes) && count($allowedTypes) ? $allowedTypes : $defaultAllowedTypes;

        $materialsCollection = $this->getService('Material')->fetchAll([
            'type in (?)' => $allowedTypes,
            'subject_id = ?' => $subjectId,
        ]);

        $materials = $materialsCollection->asArray();

        // вычищаем дубли
        $ids = array_column($materials, 'id');
        $idsDuplicates = array_diff_assoc($ids, array_unique($ids));
        $types = array_column($materials, 'type');
        $typesDuplicates = array_diff_assoc($types, array_unique($types));

        $excludedIds = $this->_getExcludedMaterialsIds($subjectId, $allowedTypes);

        $view = Zend_Registry::get('view');
        foreach ($materials as $materialKey => $material) {
            if (in_array($material['type'], $typesDuplicates) and
                in_array($material['id'], $idsDuplicates)
            ) {
                unset($materials[$materialKey]);
            }

            if(in_array($material['id'], $excludedIds[$material['type']])) {
                unset($materials[$materialKey]);
                continue;
            }

            $materials[$materialKey]['viewUrl'] = $view->url([
                'module' => 'subject',
                'controller' => 'material',
                'action' => 'index',
                'id' => $material['id'],
                'type' => $material['type'],
            ]);
        }

        usort($materials, function($item1, $item2){
            return strtolower($item1['title']) < strtolower($item2['title']) ? -1 : 1;
        });

        return array_values($materials);
    }

    public function createDefault(string $materialType, $title, $subjectId = false, $addToExtras = false)
    {
        $result = false;

        switch ($materialType) {
            case HM_Event_EventModel::TYPE_COURSE:
                $result = $this->getService('Course')->createDefault($title, $subjectId);
                break;
            case HM_Event_EventModel::TYPE_RESOURCE:
                // только ресурсы инфомогут быть добавлены в Extras
                $result = $this->getService('Resource')->createDefault($title, $subjectId, $addToExtras);
                break;
            case HM_Event_EventModel::TYPE_TEST:
                $result = $this->getService('Quest')->createDefault($title, $subjectId, HM_Quest_QuestModel::TYPE_TEST);
                break;
            case HM_Event_EventModel::TYPE_POLL:
                $result = $this->getService('Quest')->createDefault($title, $subjectId, HM_Quest_QuestModel::TYPE_POLL);
                break;
            case HM_Event_EventModel::TYPE_TASK:
                $result = $this->getService('Task')->createDefault($title, $subjectId);
                break;
            case HM_Event_EventModel::TYPE_ECLASS:
                $result = $this->getService('Eclass')->createDefault($title, $subjectId);
                break;
            case HM_Event_EventModel::TYPE_FORUM:
                $result = $this->getService('Forum')->createDefault($title, $subjectId);
                break;
            default:
                break;
        }

        return $result;
    }


    public function createCard($title, $subjectId = false)
    {
        return $this->getService('Resource')->createCard($title, $subjectId);
    }

    public function findMaterial($id, $type): ?HM_Material_Interface
    {
        switch ($type) {
            case HM_Event_EventModel::TYPE_RESOURCE:
                $result = $this->getService('Resource')->findOne($id);
                break;
            case HM_Event_EventModel::TYPE_COURSE:
                $result = $this->getService('Course')->findOne($id);
                break;
            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:
                $result = $this->getService('Quest')->findOne($id);
                break;
            case HM_Event_EventModel::TYPE_TASK:
                $result = $this->getService('Task')->findOne($id);
                break;
        }

        return $result;
    }

    /**
     * @param $fileOrString
     * @return bool
     */
    protected function needConvertToPdf($fileOrString): bool
    {
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $convertToPdf = isset($params['file_convertToPdf'])
            ? $params['file_convertToPdf']
            : $convertableToPdf = HM_Files_FilesModel::isConvertableToPdf(
                HM_Files_FilesModel::getFileTypeString($fileOrString)
            );

        return $convertToPdf;
    }

    protected function sendToOpenOffice($sourcePath, $targetPath)
    {
        if (empty($sourcePath) || empty($targetPath)) return false;

        $config = Zend_Registry::get('config');

        set_time_limit(0);
        $ch = curl_init();

        $serverUrl = $config->local_server_url ?? Zend_Registry::get('view')->serverUrl();

        curl_setopt($ch, CURLOPT_URL, $url = $serverUrl . $config->exec->path . '/convertpdf.php');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params = [
            'sourcePath' => $sourcePath,
            'targetPath' => $targetPath
        ]);

        $response = curl_exec($ch);

        if (0) {
            $log = Zend_Registry::get('log_security');
            $log->log('CONVERTER URL: ' . $url, Zend_Log::DEBUG);
            $log->log(sprintf('CONVERTER REQUEST: %s', serialize($params)), Zend_Log::DEBUG);
            $log->log(sprintf('CONVERTER RESPONSE: %s', $response), Zend_Log::DEBUG);
        }

        curl_close($ch);
        return $response;
    }

    /**
     * @param $fileOrString
     * @param $subjectId
     * @param HM_Model_Abstract $test
     * @return HM_Model_Abstract
     * @throws HM_Exception
     */
    public function importQuestions($fileOrString, $subjectId, $test)
    {
        if ($test) {

            $importManager = new HM_Quest_Question_Import_Manager();
            $fileType = HM_Files_FilesModel::getFileType($fileOrString);

            switch ($fileType) {
                case HM_Files_FilesModel::FILETYPE_XLSX:
                    $this->getService('QuestQuestionExcel')->setFileName(realpath($fileOrString));
                    $importManager->init($this->getService('QuestQuestionExcel')->fetchAll(), $fileOrString, true);
                    $importManager->import($subjectId, $test);
                    break;
                case HM_Files_FilesModel::FILETYPE_TEXT:
                    $this->getService('QuestQuestionTxt')->setFileName(realpath($fileOrString));
                    $importManager->init($this->getService('QuestQuestionTxt')->fetchAll(), $fileOrString, true);
                    $importManager->import($subjectId, $test);
                    break;
                default:
                    throw new HM_Exception('This file format is not support for import');
                    break;
            }

            return $test;
        }
    }

    public function getRecommendedMaterials($userId, $count = 1)
    {
        $materials = $conditions = [];
        if ($user = $this->getService('User')->getOne($this->getService('User')->findDependence('Position', $userId))) {
            // если совместитель - берём первую попавшуюся
            if ($position = $user->positions ? $user->positions->current() : null) {
                if ($profile = $this->getService('AtProfile')->findOne($position->profile_id)) {
                    if (count($criteria = $this->getService('AtProfileCriterionValue')->fetchAllDependence('Criterion', [
                        'profile_id = ?' => $profile->profile_id,
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
                    ]))) {
                        $conditions[] = [
                            'criterion_id IN (?)' => $criteria->getList('criterion_id'),
                            'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
                        ];
                    }
                    if (count($criteria = $this->getService('AtProfileCriterionValue')->fetchAllDependence('CriterionTest', [
                        'profile_id = ?' => $profile->profile_id,
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                    ]))) {
                        $conditions[] = [
                            'criterion_id IN (?)' => $criteria->getList('criterion_id'),
                            'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                        ];
                    }
                    if (count($criteria = $this->getService('AtProfileCriterionValue')->fetchAllDependence('CriterionPersonal', [
                        'profile_id = ?' => $profile->profile_id,
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PERSONAL,
                    ]))) {
                        $conditions[] = [
                            'criterion_id IN (?)' => $criteria->getList('criterion_id'),
                            'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PERSONAL,
                        ];
                    }
                }
            }
        }

        if (count($conditions)) {
            array_walk($conditions, function (&$condition) {
                array_walk($condition, function (&$value, $key) {
                    $value =  $this->getService('Material')->quoteInto($key, $value);
                });
                $condition = implode(' AND ', $condition);
            });
            $conditions = implode(' OR ', $conditions);

            if (count($materialCriteria = $this->getService('MaterialCriteria')->fetchAllDependence(['Resource'/*, 'Course'*/], $conditions))) {

                foreach ($materialCriteria as $materialCriterion) {
                    switch ($materialCriterion->material_type) {
                        case HM_Event_EventModel::TYPE_RESOURCE:
                            if (count($materialCriterion->resources)) $materials[] = $materialCriterion->resources->current();
                            break;
                        case HM_Event_EventModel::TYPE_COURSE:
                            if (count($materialCriterion->courses)) $materials[] = $materialCriterion->courses->current();
                            break;
                    }
                }

                shuffle($materials);

                // @todo:
                // реализовать $count + ротацию
                // реализовать запоминание просмотренных ресурсов

                return array_pop($materials);
            }
        }
    }

    /**
     * Фильтр, например, по заданиям без вариантов. Фильтровать во view нельзя - оно много где используется.
     * Фильтровать в выборке нельзя - там повторяются id для разных типов. Поэтому придётся постфактум
     *
     * @param $subjectId
     *
     * Передаём используемые типы, чтобы не дёргать лишнего
     * @param $allowedTypes
     *
     * @return array
     */
    private function _getExcludedMaterialsIds($subjectId, $allowedTypes): array
    {
        $ids = [];
        foreach ($allowedTypes as $type) {
            switch ($type) {

                case HM_Event_EventModel::TYPE_TASK:
                    $result = $this->getService('Task')->getTasksIdsWithoutVariants($subjectId);
                    break;

                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_POLL:
                    $result = $this->getService('Quest')->getQuestIdsWithoutQuestions($type, $subjectId);
                    break;

                case HM_Event_EventModel::TYPE_COURSE:
                    break;

                case HM_Event_EventModel::TYPE_RESOURCE:
                    break;
            }

            $ids[$type] = $result;
        }

        return $ids;
    }

    private function unlinkTemp($path)
    {
        if (is_dir($path)) {
            foreach (glob($path . '\*') as $subPath) {

                if (is_dir($subPath)) {
                    $this->unlinkTemp($subPath);
                    rmdir($subPath);
                } else {
                    unlink($subPath);
                }
            }

            // Убрали всё внутри папки и саму папку уберём
            rmdir($path);

        } elseif (file_exists($path)) {
            unlink($path);
        }
    }

}
