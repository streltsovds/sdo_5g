<?php
class HM_Resource_ResourceService extends HM_Service_Abstract {
    const EVENT_GROUP_NAME_PREFIX = 'ADD_MATERIAL_TO_COURSE';

    protected $notDeleted;

    public function insert($data, $unsetNull = true)
    {
        $data['created'] = $data['updated'] = $this->getDateTime();
        $data['created_by'] = $this->getService('User')->getCurrentUserId();

        $hasRelated = (is_array($data['related_resources']) && count($data['related_resources']));
        
        if($hasRelated){
            $relatedResources = array_unique($data['related_resources']);
            $data['related_resources'] = implode(',', $relatedResources);
        }

        //if(!isset($data['time_to_learn'])) $data['time_to_learn']=0;

        $result = parent::insert($data, $unsetNull);
        
        if($hasRelated){
            $this->propagateRelatedResources($result->resource_id, $relatedResources);
        }
        
        return $result;
    }

    public function update($data, $unsetNull = true)
    {
        if (isset($data['related_resources']))
        {
            // обновляем связанные ресурсы только если они пришли с POSTом в виде массива
            if (is_array($data['related_resources'])) {
                $relatedResources = array_unique($data['related_resources']);
                if (false !== ($key = array_search($data['resource_id'], $relatedResources))) unset($relatedResources[$key]);
                $this->propagateRelatedResources($data['resource_id'], $relatedResources);
                $data['related_resources'] = implode(',', $relatedResources);
            }
        }

        $data['updated'] = $this->getDateTime();
        return parent::update($data, $unsetNull);
    }

    public function prepareMultipleFiles($resource, $fileResource, $populatedFiles = array())
    {
        // Формируем zip и указываем файлом parent'а
        $zip = new ZipArchive();
        $res = $zip->open(realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($res) {
            foreach ($populatedFiles as $populatedFile) {
                $zip->addFile($populatedFile->getPath(), iconv('UTF-8', 'cp866', $populatedFile->getDisplayName()));
            }
            if ($fileResource->isReceived()) {
	            $fileNames = $fileResource->getFileName();
	            if (!is_array($fileNames)) $fileNames = array($fileNames); 
	            foreach($fileNames as $filename) {
	                $zip->addFile($filename, iconv('UTF-8', 'cp866', basename($filename)));
	            }
	        }
        }
        $zip->close();

        return realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id.'.zip';
    }

    public function updateDependentResources($resource, $fileResource, $populatedFiles = array(), $saveAsRevision = false)
    {
        // Заносим каждый файл по отдельности
        $count = 0;
        $fileSizes = $fileNames = array();

        if ($fileResource->isReceived()) {
            $fileSizes = $fileResource->getFileSize();
            if (!is_array($fileSizes)) $fileSizes = array($fileSizes);
            $fileNames = $fileResource->getFileName();
            if (!is_array($fileNames)) $fileNames = array($fileNames);
        }

        $values = $resource->getValues();
        $values['parent_id'] = $values['resource_id'];
        unset($values['resource_id']);
        
        foreach($fileNames as $index => $filename) {
            $values['filename'] = basename($filename);
            $values['volume'] = $fileSizes[$index];
            $values['filetype'] = HM_Files_FilesModel::getFileType($values['filename']);

            $item = $this->getService('Resource')->insert($values);
            if ($item) {
                $filter = new Zend_Filter_File_Rename(
                    array(
                        'source' => $filename,
                        'target' => $item->getFilePath(true),//realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$item->resource_id,
                        'overwrite' => true
                    )
                );
                $filter->filter($filename);
            }
            $count++;
        }
        if ($saveAsRevision ||
            (!$saveAsRevision && (count($populatedFiles) == 1)) // up-shifting (был file, стал fileset) без сохранения ревизии
        ) {
            if (count($populatedFiles)) {
                foreach ($populatedFiles as $populatedFile) {
                    $values['filename'] = $populatedFile->getDisplayName();
                    $values['volume'] = 0; // @todo
                    $values['filetype'] = HM_Files_FilesModel::getFileType($values['filename']);
                    $item = $this->getService('Resource')->insert($values);   
                    @copy($populatedFile->getPath(), $item->getFilePath(true));//realpath(Zend_Registry::get('config')->path->upload->resource) . '/' . $item->resource_id);
                    $count++;             
                }
            }
        }
        return $count;
    }

    public function propagateRelatedResources($resourseId, $relatedResources)
    {
        if (!count($relatedResources))
            $relatedResources = array(0);

        // Добавим текущий ресурс во все связанные
        $resources = $this->fetchAll(array('resource_id IN (?)' => $relatedResources));
            foreach ($resources as $resource) {
                $data = $resource->getData();
                $existingResources = !empty($resource->related_resources) ? explode(',', $resource->related_resources) : array();
                if (!in_array($resourseId, $existingResources)) {
                    $existingResources[] = $resourseId;
                }
                $data['related_resources'] = implode(',', $existingResources);
                parent::update($data);
            }

        // Находим связанные, но отныне удалённые ресурсы
        if (count($resources = $this->fetchAll(
            $this->quoteInto(
                ["related_resources LIKE '%" . $resourseId . "%' AND resource_id NOT IN (?)"],
                [$relatedResources]
            )
        ))) {
            foreach ($resources as $resource) {
                $data = $resource->getData();
                $existingResources = !empty($resource->related_resources) ? explode(',', $resource->related_resources) : array();
                $key = array_search($resourseId, $existingResources);
                if ($key !== false) {
                    unset($existingResources[$key]);
                }
                $data['related_resources'] = implode(',', $existingResources);
                parent::update($data);
            }
        }
    }

    public function delete($id)
    {
        // Удаляем детей
        $collection = $this->getService('Resource')->fetchAll(
            $this->quoteInto('parent_id = ?', $id)
        );

        if(count($collection)) {
            foreach($collection as $item) {
                $this->delete($item->resource_id);
            }
        }
         //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(
            array('item_id=?',' AND item_type=?'),
            array($id,HM_Tag_Ref_RefModel::TYPE_RESOURCE)
        ));

        // удаляем связи с этим ресурсом из всех других ресурсов
        $resource = $this->find($id)->current();
        if (!empty($resource->related_resources)) {
            $this->propagateRelatedResources($id, array());
        }

        // Удаляем связи с курсами
        $this->getService('SubjectResource')->deleteBy(
            $this->quoteInto('resource_id = ?', $id)
        );

        $this->getService('StorageFileSystem')->delete($resource->storage_id);
        unlink($resource->getFilePath());//Zend_Registry::get('config')->path->upload->resource . $id);

        return parent::delete($id);
    }

    public function isEditable($subjectIdFromResource, $subjectId, $status, $subjectFromResource = 'subject', $subject = 'subject'){

        $all = array(
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        );
        $role = $this->getService('User')->getCurrentUserRole();
        if(in_array($role, $all)){
            return true;
        }
        if($subjectId == 0){
            return false;
        }

        if(
            $this->getService('Acl')->inheritsRole(
                $role,
                array(
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR)
            )
            //$role == HM_Role_Abstract_RoleModel::ROLE_TEACHER
            && $status == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL
            && $subjectIdFromResource == $subjectId && $subjectFromResource == $subject){
            return true;
        }

        return false;
    }

    public function printContent($resourceModel)
    {
        switch ($resourceModel->type) {
        	case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                $filePath = $resourceModel->getFilePath();//Zend_Registry::get('config')->path->upload->resource . $resourceModel->resource_id;
                $resourceReader = new HM_Resource_Reader($filePath, $resourceModel->filename);
                $resourceReader->readFile();
        		break;
        	case HM_Resource_ResourceModel::TYPE_HTML:
        	    echo $resourceModel->content;
	            break;
        	case HM_Resource_ResourceModel::TYPE_URL:
        	    echo $resourceModel->url;
	            break;
        }
    }

    public function createLesson($subjectId, $resourceId)
    {
        // создаём новое занятие, даже если ранее уже было создано
        $resource = $this->findOne($resourceId);
        if ($resource) {
            $values = array(
                'title' => $resource->title,
                'descript' => $resource->description,
                'begin' => date('Y-m-d 00:00:00'),
                'end' => date('Y-m-d 23:59:00'),
                'createID' => $this->getService('User')->getCurrentUserId(),
                'createDate' => date('Y-m-d H:i:s'),
                'typeID' => HM_Event_EventModel::TYPE_RESOURCE,
                'vedomost' => 1,
                'CID' => $subjectId,
                'startday' => 0,
                'stopday' => 0,
                'timetype' => 2,
                'isgroup' => 0,
                'teacher' => 0,
                'params' => 'module_id='.(int) $resource->resource_id.';',
                // 5G
                // продублируем в отдельное человеческое поле,
                // чтобы в будущем отказаться от "params"
                'material_id' => $resource->resource_id,
                'all' => 1,
                'cond_sheid' => '',
                'cond_mark' => '',
                'cond_progress' => 0,
                'cond_avgbal' => 0,
                'cond_sumbal' => 0,
                'cond_operation' => 0,
                'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
            );

            $lesson = $this->getService('Lesson')->insert($values);

            $students = $lesson->getService()->getAvailableStudents($subjectId);
            if (is_array($students) && count($students)) {
                $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
            }
//[ES!!!] //array('lesson' => $lesson))
        }

        return $lesson;
    }


    protected function _createMeeting($projectId, $resourceId, $section = false, $order = false)
    {
        if (empty($section)) {
            $section = $this->getService('Section')->getDefaultSection($projectId, HM_Section_SectionModel::ITEM_TYPE_PROJECT);
            if (empty($order)) {
                $currentOrder = $this->getService('Section')->getCurrentOrder($section);
                $order = ++$currentOrder;
            }
        }

        $meetings = $this->getService('Meeting')->fetchAll(
            $this->getService('Meeting')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND project_id = ?'),
                array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id='.$resourceId.';%', $projectId)
            )
        );
        if (!count($meetings)) {
            $resource = $this->getOne($this->getService('Resource')->find($resourceId));
            if ($resource) {
                $values = array(
                    'title' => $resource->title,
                    'descript' => $resource->description,
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => 1,
                    'createDate' => date('Y-m-d H:i:s'),
                    'typeID' => HM_Event_EventModel::TYPE_RESOURCE,
                    'vedomost' => 1,
                    'project_id' => $projectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'moderator' => 0,
                    'params' => 'module_id='.(int) $resource->resource_id.';',
                    'all' => 1,
                    'cond_project_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => 0,
                    'cond_avgbal' => 0,
                    'cond_sumbal' => 0,
                    'cond_operation' => 0,
                    'isfree' => HM_Meeting_MeetingModel::MODE_FREE,
                    'section_id' => $section->section_id,
                    'order' => $order,
                );
                $meetings = $this->getService('Meeting')->insert($values);
                /*$participants = $meetings->getService()->getAvailableParticipants($projectId);
                if (is_array($participants) && count($participants)) {
                    $this->getService('Meeting')->assignParticipants($meetings->meeting_id, $participants);
                }*/
            }
        }
    }
/*    public function deleteLesson($subject, $resourceId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%', $subject->subid)
            )
        );
        if (count($lessons)) {
            foreach($lessons as $lesson) {
                $this->getService('Lesson')->delete($lesson->SHEID);
            }
        }
    }*/

    public function clearLesson($subject, $resourceId, $subjectType = 'subject')
    {
        switch ($subjectType) {
            case 'project':
                $this->_clearMeeting($subject, $resourceId);
                break;
            default:
                $this->_clearLesson($subject, $resourceId);
        }
    }

    protected function _clearLesson($subject, $resourceId)
    {
        if($subject == null) {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?"),
                    array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%')
                )
            );
        } else {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                    array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%', $subject->subid)
                )
            );
        }

        if (count($lessons)) {
            /** @var HM_Lesson_LessonService $lessonService */
            $lessonService = $this->getService('Lesson');
            foreach($lessons as $lesson) {
                $lessonService->resetMaterialFields($lesson->SHEID);
            }
        }
    }

    protected function _clearMeeting($project, $resourceId)
    {
        if($project == null){
            $meetings = $this->getService('Meeting')->fetchAll(
                $this->getService('Meeting')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?"),
                    array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%')
                )
            );
        }else{
            $meetings = $this->getService('Meeting')->fetchAll(
                $this->getService('Meeting')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?", ' AND project_id = ?'),
                    array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%', $project->projid)
                )
            );
        }

        if (count($meetings)) {
            $projectNew = null;
            foreach($meetings as $meeting) {
                $projectNew = $this->getService('Project')->getOne($this->getService('Project')->find($meeting->projid));
                $this->getService('Meeting')->deleteBy(array('meeting_id = ?' => $meeting->meeting_id, 'isfree IN (?)' => new Zend_Db_Expr(implode(',', array(HM_Meeting_MeetingModel::MODE_FREE, HM_Meeting_MeetingModel::MODE_FREE_BLOCKED)))));
                $this->getService('Meeting')->updateWhere(array('params' => ''), array('meeting_id = ?' => $meeting->meeting_id, 'isfree = ?' => HM_Meeting_MeetingModel::MODE_PLAN));
    }
        }
    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            // т.к. именно HTML-редактор открывается если мы делаем "Редактировать в конструкторе"
            'type' => HM_Resource_ResourceModel::TYPE_HTML,
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => HM_Resource_ResourceModel::STATUS_PUBLISHED,
        );
    }

    private function getCardDefaults()
    {
        $defaults = $this->getDefaults();
        $defaults['type'] = HM_Resource_ResourceModel::TYPE_CARD;

        return $defaults;
    }

    public function copyContent($resource, $toResourceId)
    {
        if ($resource) {
            if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                $from = realpath(Zend_Registry::get('config')->path->upload->public_resource).'/'.$resource->resource_id.'/';
//                $to = realpath(Zend_Registry::get('config')->path->upload->public_resource).'/'.$toResourceId.'/';
                $to = realpath(Zend_Registry::get('config')->path->upload->public_resource).'/'.$toResourceId;
                if(!file_exists($to))
                    mkdir($to);
                $to .= "/";

                try {
                    $this->getService('Course')->copyDir($from, $to);
                } catch (HM_Exception $e) {
                    // что-то не скопировалось
                }
            } else {
                $from = $resource->getFilePath(true);//realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id;
                $to = $resource->getFilePath(true, $toResourceId);//realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$toResourceId;

                if (file_exists($from)) {
                    if (!is_readable($from)) {
                        throw new HM_Exception(sprintf(_('Нет прав на чтение %s'), $from));
                    }

                    if (!copy($from, $to)) {
                        throw new HM_Exception(sprintf(_('Невозможно скопировать файл %s в %s'), $from, $to));
                    }
                }
            }
        }
    }

    public function copy($resource, $toSubjectId = null, $newParentId = null)
    {
        if ($resource) {
            if (null !== $toSubjectId) {
                $resource->subject_id = $toSubjectId;
            }
            if (null !== $newParentId) {
                $resource->parent_id = $newParentId;
            }

            $newResource = $this->insert($resource->getValues(null, array('resource_id')));

            if ($newResource) {

                $this->copyContent($resource, $newResource->resource_id);

                $classifiers = $this->getService('ClassifierLink')->fetchAll(
                    $this->quoteInto(
                        array('item_id = ?', ' AND type = ?'),
                        array($resource->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE)
                    )
                );

                if (count($classifiers)) {
                    foreach($classifiers as $classifier) {
                        $this->getService('Classifier')->linkItem($newResource->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $classifier->classifier_id);
                    }
                }

                $this->getService('TagRef')->copy(HM_Tag_Ref_RefModel::TYPE_RESOURCE, $resource->resource_id, $newResource->resource_id);

            }

            return $newResource;
        }

        return false;
    }

    /**
     * Set related resources
     *
     * @param $relatedResources mixed
     *
     * @return array
     */
    public function setDefaultRelatedResources($relatedResources)
    {
        $return = array();
        if (!empty($relatedResources)) {
            if (!is_array($relatedResources)) {
                $relatedResources = explode(',', $relatedResources);
            }

            if (count($relatedResources) == 1 && (int)$relatedResources[0] == 0) {
                return $return;
            }

            $resources = $this->getService('Resource')->fetchAll(array('resource_id IN (?)' => $relatedResources));
            foreach ($resources as $resource) {
                $return[$resource->resource_id] = sprintf('#%s: %s', $resource->resource_id, $resource->title);
            }

        }
        return $return;
    }

    public function linkClassifiers($resourceId, $classifiers)
    {
        $classifiers = array_unique($classifiers);
        $this->getService('Classifier')->unlinkItem($resourceId, HM_Classifier_Link_LinkModel::TYPE_RESOURCE);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($resourceId, HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $classifierId);
                }
            }
        }
        return true;
    }

    public function getResourceRevision($resourceId, $revisionId)
    {
        $resource = $this->getOne($this->find($resourceId));

        if (!$resource) {
            return false;
        }

        if ($revisionId && ($revision = $this->getService('ResourceRevision')->find($revisionId)->current())) {
            foreach (HM_Resource_Revision_RevisionService::getRevisionableAttributes() as $key) {
            	$resource->$key = $revision->$key;
            }
        }
        return $resource;
    }

    static public function getIconClass($type, $filetype, $filename, $activityType)
    {
        $return = 'material-icon ';
        switch ($type) {
            case HM_Resource_ResourceModel::TYPE_URL:
                $return .= 'resource-' . HM_Resource_ResourceModel::TYPE_URL;
                break;
            case HM_Resource_ResourceModel::TYPE_FILESET:
            case HM_Resource_ResourceModel::TYPE_HTML:
                $return .= 'resource-' . HM_Resource_ResourceModel::TYPE_HTML;
            break;
            case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                if (empty($filetype)) {
                    $return .= 'resource-filetype-' . HM_Files_FilesModel::getFileType($filename);
                } else {
                    $return .= 'resource-filetype-' . $filetype;
                }
            break;
            case HM_Resource_ResourceModel::TYPE_ACTIVITY:
                    $return .= 'resource-activitytype-' . $activityType;
            break;
            default:
                $return .= 'resource-' . $type;
            break;
        }
        return $return;
    }

    public function getRelatedUserList($id) {
        $assigns = $this->getService('LessonAssign')->fetchAll($this->quoteInto('SHEID = ? AND MID > 0', $id));
        $result = array();
        if ($assigns->count() > 0) {
            foreach ($assigns as $student) {
                $result[] = intval($student->MID);
            }
        }
        return $result;
    }

    /**
     * Возвращает массив тегов, которые назначены ресурсам
     * @return array
     */
    public function getAllTagNames() {
        $select = $this->getSelect();

        //получем метки по каждому ресурсу
        $select->from(
            array('tr' => 'tag_ref'),
            array(
                'resource_id' => 'tr.item_id', //id ресурса
                'body' => 't.body'
            )
        );
        $select->joinLeft(
            array('t' => 'tag'),
            't.id = tr.tag_id',
            array()
        );
        $select->where('tr.item_type = ?', HM_Tag_Ref_RefModel::TYPE_RESOURCE);

        $result = $select->query()->fetchAll();

        $tags = array();
        foreach($result as $val) {
            array_push($tags, $val['body']);
        }
        $tags = array_values(array_unique($tags)); //убираем дубли

        return $tags;
    }

    /**
     * Возвращает массив классификаторов, которые назначены ресурсам
     * @return array - key = classifier_id, value = name
     */
    public function getAllClassifierList() {
        $select = $this->getSelect();

        $select->from(
            array('cll' => 'classifiers_links'),
            array(
                'classifier_id' => 'cll.classifier_id',
                'res_id' => 'cll.item_id', //id ресурса
                'name' => 'cl.name'
            )
        );
        $select->joinLeft(
            array('cl' => 'classifiers'),
            'cl.classifier_id = cll.classifier_id',
            array()
        );
        $select->where('cll.type = ?', HM_Classifier_Link_LinkModel::TYPE_RESOURCE);

        $result = $select->query()->fetchAll();

        $classifiers = array();
        foreach($result as $val) {
            $classifiers[$val['classifier_id']] = $val['name'];
        }

        return $classifiers;
    }

    /**
     * @param $title
     * @param $subjectId
     * @return HM_Model_Abstract
     * @throws HM_Exception
     */
    public function createDefault($title, $subjectId = false, $addToExtras = false)
    {
        if (!strlen($title)) {
            $title = _('[Без названия]');
        }

        $defaults = $this->getDefaults();
        $defaults['title'] = $title;
        $defaults['description'] = '';

        if ($subjectId) {
            $defaults['subject'] = 'subject'; //определяем, курс это или конкурс
            $defaults['subject_id'] = $subjectId;
        }

        $result = $this->insert($defaults);
        if ($subjectId && $result && $addToExtras) {
            $this->getService('SubjectResource')->link($result->resource_id, $subjectId, 'subject');
        }

        return $result;
    }

    /**
     * @param $title
     * @param $subjectId
     * @return HM_Model_Abstract
     * @throws HM_Exception
     */
    public function createCard($title, $subjectId = false)
    {
        if (!strlen($title)) {
            $title = _('[Без названия]');
        }

        $defaults = $this->getCardDefaults();
        $defaults['title'] = $title;
        $defaults['description'] = '';

        if ($subjectId) {
            $defaults['subject'] = 'subject'; //определяем, курс это или конкурс
            $defaults['subject_id'] = $subjectId;
        }

        $result = $this->insert($defaults);

        return $result;
    }

    public function complexRemove($resourceId, $subjectId = 0): bool
    {
        $resource = $this->getOne($this->findDependence('Revision', $resourceId));

        if (!empty($resource) &&
            $this->isEditable($resource->subject_id, $subjectId, $resource->location)
        ) {
            // убираем мусор
            if ($resource->filename) {
                $filePath = $resource->getFilePath(true);//realpath(Zend_Registry::get('config')->path->upload->resource) . '/' . $resource->resource_id;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                $resourcePath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';

                /** @var HM_Course_CourseService $courseService */
                $courseService = $this->getService('Course');

                $courseService->removeDir($resourcePath);
                foreach ($resource->revisions as $revision) {
                    $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revision->revision_id . '/';
                    $courseService->removeDir($revisionPath);
                }
            }

            $this->delete($resourceId);
            $this->clearLesson(null, $resourceId);

            /** @var HM_Subject_Resource_ResourceService $subjectResourceService */
            $subjectResourceService = $this->getService('SubjectResource');

            if(!empty($subjectId)) {
                // remove extra materials
                $subjectResourceService->unlink($resourceId, $subjectId);
            }

            $this->getService('ResourceRevision')->deleteBy(array('resource_id = ?' => $resourceId));

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $subjectId
     * @param HM_Form_Element_Html5File|HM_Form_Element_ServerFile $photo Элемент формы
     * @param $destination Путь к папке с иконками
     * @param bool $skipResize
     * @param int $removeIcon Удалить icon?
     * @return bool
     * @throws Exception
     * @todo Реализовать возможность выбирать размер иконок, решить как их сохранять (менять название файла/создавать папку)
     */
    public static function updateIcon($resourceId, $photo, $destination = null, $skipResize = false, $removeIcon = 0)
    {
        if (empty($destination)) {
            $destination = HM_Resource_ResourceModel::getIconFolder($resourceId);
        }

        // пока такие же как у subject
        $w = HM_Subject_SubjectModel::THUMB_WIDTH;
        $h = HM_Subject_SubjectModel::THUMB_HEIGHT;

        $path = rtrim($destination, '/') . '/' . $resourceId . '.jpg';

        if ($removeIcon) {
            unlink($path);
            return true;
        }

        if (is_null($photo)) return false;

        if ($photo->isUploaded()){

            $original = rtrim($photo->getDestination(), '/') . '/' . $photo->getValue();
            if ($skipResize) {
                $path = rtrim($destination, '/') . '/' . $resourceId . '-full.jpg';
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);

            $img->adaptiveResize($w, $h);
            $img->save($path);
            unlink($original);
        }
        return true;
    }

    public function getExternalUrl($resourceId)
    {
        $path = HM_View_Helper_Url::url(array('module' => 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resourceId, 'revision_id' => 0));
        $baseUrl = $this->getService('Option')->getOption('externalUrl');
        $serverUrl = Zend_Registry::get('config')->serverUrl ? Zend_Registry::get('config')->serverUrl : $baseUrl;
        $url = sprintf(parse_url($serverUrl, PHP_URL_SCHEME) . '://docs.google.com/viewer?url=%s&embedded=true', urlencode($baseUrl . $path));
        return $url;
    }

}
