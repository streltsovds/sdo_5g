<?php
/**
 * @deprecated ?
 * @see Kbase_ResourcesController
 * @see Kbase_ResourcesController
 */
class Resource_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $service = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id = 0;

    protected $_subjectId = 0;
    protected $_courseId = 0;
    protected $_key = 0;
    protected $_options = array(
        'subjectType' => 'subject',
        'lessonIdParamName' => 'lesson_id',
        'lessonIdFieldName' => 'SHEID',
        'lessonService' => 'Lesson',
        'lessonAssignService' => 'LessonAssign',
    );

    protected $_resource;
    protected $notDeleted;

    public function init()
    {
        $form = new HM_Form_Resource();
        $this->_setForm($form);
        $this->_key = (int)$this->_getParam('key', 0);

        // @todo: подключить баян от курса
        if ($resourceId = $this->_getParam('resource_id', 0)) {
            if ($collection = $this->getService('Resource')->find($resourceId)) {
                $this->_resource = $collection->current();
            }
        }

        if (
            ($this->_resource && ($this->_resource->type == HM_Resource_ResourceModel::TYPE_CARD)) ||
            ($this->_getParam('type') == HM_Resource_ResourceModel::TYPE_CARD)
        ) {
            $form->removeSubForm('resourceStep2');
        }

        parent::init();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'list', 'resource');
    }

    public function itemsAction()
    {
        $this->indexAction();
        $this->view->key = $this->_key;
    }

    /**
     * @deprecated ? Как и весь контроллер
     * @see Kbase_ResourcesController::indexAction()
     */
    public function indexAction()
    {
        $subjectId = (int)$this->_getParam($this->idParamName, 0);
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');

        $isTeacherOrDean = $aclService->checkRoles(
            array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            ));

        $isProjectUser = $aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_CURATOR);

        $order = $this->_request->getParam("ordergrid");

        if ($order == "") {
            $this->_request->setParam("ordergrid", 'title_ASC');
        }

        $switcher = $this->_getParam('switcher', 0);
        if ($switcher && $switcher != 'index') {
            $this->getHelper('viewRenderer')->setNoRender();
            $action = $switcher . 'Action';
            $this->$action();
            $this->view->render('list/' . $switcher . '.tpl');
            return true;
        }

        $filters = array(
            'title' => null,
            'updated' => array(
                'render' => 'date',
                array(
                    'transform' => 'dateChanger'
                )
            ),
            'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
            'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            'classifiers_name' => null
        );

        $rolesWithFilter = array(
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
        );

        if (in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)) {
            $filters['public'] = array('values' => HM_Resource_ResourceModel::getStatuses());
        } else {
            $this->_setParam('publicgrid', 1);
        }

        if ($subjectId > 0) {
            if ($order == '') {
                $this->_setParam('ordergrid', 'public_DESC');
            }

            $select = $this->getService('Resource')->getSelect();
            $select->from(
                array('t' => 'resources'),
                array(
                    't.resource_id',
                    't.created_by',
                    't.title',
                    't.location',
                    'locationtemp' => 't.location',
                    'statustemp' => 't.status',
                    'subjecttemp' => 't.subject_id',
                    'subject' => 's.subject_id',
                    'type',
                    'filetype',
                    'filename',
                    'activity_type',
                    'typetemp' => 't.type',
                    't.volume',
                    't.updated',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'tags' => 't.resource_id',
                    'classifiers_name' => 'GROUP_CONCAT(DISTINCT cla.name)'
                ));


            $subSelect = $this->getService('Resource')->getSelect();
            $subSelect->from(array('s' => 'subjects_resources'), array('subject_id', 'resource_id'))->where('subject_id = ?', $subjectId);

            $select->joinLeft(
                array('s' => $subSelect),
                't.resource_id = s.resource_id',
                array())
                ->joinLeft(array('p' => 'People'), 'p.MID = t.created_by', array())
                ->joinLeft(array('cl' => 'classifiers_links'), 'cl.item_id = t.resource_id', array())
                ->joinLeft(array('cla' => 'classifiers'), 'cla.classifier_id = cl.classifier_id', array())
                ->where('(t.location = ' . (int)HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL . ' AND t.status IN (' . (int)HM_Resource_ResourceModel::STATUS_PUBLISHED . ',' . (int)HM_Resource_ResourceModel::STATUS_STUDYONLY . ')) OR t.subject_id = ' . (int)$subjectId);
        } else {

            if ($order == '') {
                $this->_setParam('ordergrid', 'public_DESC');
            }
            $select = $this->getService('Resource')->getSelect();
            $select->from(
                array('t' => 'resources'),
                array(
                    'resource_id',
                    'created_by',
                    'title',
                    'filetype',
                    'filename',
                    'activity_type',
                    'volume',
                    'public' => 'status',
                    'updated',
                    'locationtemp' => 't.location',
                    'statustemp' => 't.status',
                    'subjecttemp' => 't.subject_id',
                    'typetemp' => 't.type',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'tags' => 'resource_id',
                    'classifiers_name' => 'GROUP_CONCAT(DISTINCT cla.name)'
                )
            )
                ->joinLeft(array('p' => 'People'), 'p.MID = t.created_by', array())
                ->joinLeft(array('cl' => 'classifiers_links'), 'cl.item_id = t.resource_id', array())
                ->joinLeft(array('cla' => 'classifiers'), 'cla.classifier_id = cl.classifier_id', array())
                ->where('location = ?', HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL);
            $select->where('t.db_id IS NULL OR t.db_id = ?', '');
            $select->where('t.parent_id = 0 OR t.parent_id IS NULL');
            $select->group('t.resource_id, t.created_by, t.title, t.type, t.filetype, t.filename, t.activity_type, 
                        t.volume, t.status, t.updated, t.location , t.status ,t.subject_id,t.type, 
                        CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, \' \') , p.FirstName), \' \'), p.Patronymic) , t.resource_id');

        }

        $grid = $this->getGrid(
            $select,
            array(
                'resource_id' => array('title' => '#'),
                'subjecttemp' => array('hidden' => true),
                'created_by' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'locationtemp' => array('hidden' => true),
                'filetype' => array('hidden' => true),
                'filename' => array('hidden' => true),
                'activity_type' => array('hidden' => true),
                'typetemp' => array('hidden' => true),
                'title' => array(
                    'title' => _('Название'),
                    'callback' => array(
                        'function' => array($this, 'updateResourceName'),
                        'params' => array('{{resource_id}}', '{{title}}', '{{type}}', '{{filetype}}', '{{filename}}', '{{activity_type}}')
                    ),
                ),
                'volume' => array('title' => _('Объём')),
                'updated' => array('title' => _('Дата последнего изменения')),
                'fio' => array('title' => _('Создан пользователем'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => '')) . '{{created_by}}', _('Карточка пользователя')) . '<a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => '')) . '{{created_by}}' . '">' . '{{fio}}</a>'),
                'location' => array('title' => _('Место хранения')),
                'public' => array('title' => _('Статус')),
                'tags' => array('title' => _('Метки')),
                'classifiers_name' => array('title' => _('Классификаторы'))
            ),
            $filters,
            'grid'
        );

        $grid->updateColumn('location',
            array('callback' =>
                array('function' =>
                    array($this, 'updateStatus'),
                    'params' => array('{{location}}')
                )
            )
        );

        $grid->updateColumn('public',
            array('callback' =>
                array('function' =>
                    array($this, 'updatePublic'),
                    'params' => array('{{public}}')
                )
            )
        );

        $grid->updateColumn('updated', array(
                'callback' => array(
                    'function' => array(
                        new HM_Resource_ResourceModel(array()),
                        'dateTime'),
                    'params' => array(
                        '{{updated}}')))
        );

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function' => array($this, 'displayTags'),
                'params' => array('{{tags}}', $this->getService('TagRef')->getResourceType(), null, '{{locationtemp}}')
            )
        ));

        if ($subjectId)
            $grid->setClassRowCondition("'{{subject}}' != ''", "success");
        $grid->addAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'download'),
            array('resource_id'),
            _('Скачать')
        );

        $grid->addAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'edit'),
            array('resource_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'delete'),
            array('resource_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{locationtemp}}', '{{subjecttemp}}', '{{typetemp}}', '{{resource_id}}', '{{created_by}}')
            )
        );

        $this->view->isTeacherOrDean = $isTeacherOrDean;
        $this->view->isProjectUser = $isProjectUser;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        $this->view->grid = $grid;
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Ресурс успешно создан'),
            self::ACTION_UPDATE => _('Ресурс успешно обновлён'),
            self::ACTION_DELETE => _('Ресурс успешно удалён'),
            self::ACTION_DELETE_BY => _('Ресурсы успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {
        $data = $form->getSubForm('resourceStep1')->getNonClassifierValues();
        $data['location'] = 1;

        unset($data['resource_id']);
        unset($data['tags']);

        // @todo: могут быть и другие viewer'ы?
        $data['external_viewer'] = $form->getValue('externalViewer') ? HM_Resource_ResourceModel::EXTERNAL_VIEWER_GOOGLE : '';

        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');

        $resource = $resourceService->insert($data);

        if ($resource) {
            $resourceService->linkClassifiers($resource->resource_id, $form->getSubForm('resourceStep1')->getClassifierValues());
        }

        if ($subform = $form->getSubForm('resourceStep2')) {

            $url = $subform->getElement('url');
            if ($resource && $url) {
                $data = $resource->getValues();
                $data['url'] = $url->getValue();
                $resourceService->update($data);
            }

            $content = $subform->getElement('content');
            if ($resource && $content) {
                $data = $resource->getValues();
                $data['content'] = $content->getValue();
                $data['volume'] = HM_Files_FilesModel::toByteString(strlen($data['content']));
                $resourceService->update($data);
            }

            /** @var HM_Form_Element_Vue_File $file */
            $file = $subform->getElement('file');

            if ($resource && $file && $file->isUploaded()) {
                $file->receive();
                if ($file->isReceived()) {

                    $filename = $file->getFileName();
                    if (count($filename) > 1) {
                        $filename = $resourceService->prepareMultipleFiles($resource, $file);
                        $resourceService->updateDependentResources($resource, $file);
                        $resource->volume = HM_Files_FilesModel::toByteString(filesize($filename));
                    } else {
                        $resource->volume = $file->getFileSize();
                    }

                    $resource->filename = basename($filename);
                    $resource->filetype = HM_Files_FilesModel::getFileType($resource->filename);

                    $filter = new Zend_Filter_File_Rename(
                        array(
                            'source' => $filename,
//                            'target' => realpath(Zend_Registry::get('config')->path->upload->resource) . '/' . $resource->resource_id,
                            'target' => $resource->getFilePath(true),
                            'overwrite' => true
                        )
                    );
                    if ($filter->filter($filename)) {
                        $resourceService->update($resource->getValues());
                    }
                }
            }

            $file = $subform->getElement('filezip');
            if ($resource && $file && $file->isUploaded()) {
                $file->receive();
                $oldUmask = umask(0);
                $resoursePath = realpath(Zend_Registry::get('config')->path->upload->public_resource);
                $target = $resoursePath . '/' . $resource->resource_id . '/';
                $volume = $file->getFileSize();

                if (!is_dir($target)) {
                    mkdir($target, 0755);
                }

                $filter = new Zend_Filter_Decompress(array('adapter' => 'Zip', 'options' => array('target' => $target)));
                $filter->filter($file->getFileName());

                if (file_exists($resoursePath . '/zip/' . basename($file->getFileName()))) {
                    unlink($resoursePath . '/zip/' . basename($file->getFileName()));
                }

                $resource = $resourceService->update(array(
                    'resource_id' => $resource->resource_id,
                    'url' => $this->_getParam('url', 'index.htm'),
                    'volume' => $volume,
                ));


                umask($oldUmask);
            }
        }

        if ($tags = $form->getValue('tags')) {
            $this->getService('Tag')->updateTags($tags, $resource->resource_id, $this->getService('TagRef')->getResourceType());
        }

        if ($redirectUrl = $this->_getParam('redirectUrl')) {
            if (!strpos($redirectUrl, 'resource_id')) {
                $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $resource->resource_id);
            }
            $this->_redirector->gotoUrl(urldecode($redirectUrl));
        }
    }

    public function update(Zend_Form $form)
    {
        $data = $form->getSubForm('resourceStep1')->getNonClassifierValues();
        unset($data['tags']);
        if ($this->_resource && ($this->_resource->type != HM_Resource_ResourceModel::TYPE_CARD)) {
            unset($data['type']);
        }

        $resource = $this->getService('Resource')->update($data);

        $tags = array_unique($form->getParam('tags', array()));
        $this->getService('Tag')->updateTags($tags, $resource->resource_id, $this->getService('TagRef')->getResourceType());

    }

    public function editResourceAction()
    {
        $form = new HM_Form_ResourceCard();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $data = $form->getValues();
                $tags = $data['tags'];
                $classifiers = [];
                foreach ($data as $key => $value) {
                    if (false !== strpos($key, 'classifier')) {
                        list(, $classifierTypeId) = explode('_', $key);
                        if (!empty($value)) $classifiers[$classifierTypeId] = $value;
                        unset($data[$key]);
                    }
                }
                unset($data['tags']);
                unset($data['cancel']);
                $resource = $this->getService('Resource')->update($data);

                if (count($tags)) {
                    $this->getService('Tag')->updateTags(
                        $tags, $resource->resource_id, $this->getService('TagRef')->getResourceType()
                    );
                }

                $this->getService('ClassifierLink')->deleteBy(
                    $this->quoteInto(
                        array('item_id = ?', ' AND type = ?'),
                        array($resource->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE)
                    )
                );

                if (count($classifiers)) {
                    foreach ($classifiers as $type => $classifiersOfType) {
                        foreach ($classifiersOfType as $classifierId) {
                            $this->getService('ClassifierLink')->insert([
                                'item_id' => $resource->resource_id,
                                'classifier_id' => $classifierId,
                                'type' => $type
                            ]);
                        }
                    }
                }

                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function delete($id)
    {
        $subjectId = (int)$this->_getParam($this->idParamName, 0);
        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->findDependence('Revision', $id));
        if (!empty($resource) && $this->getService('Resource')->isEditable($resource->subject_id, $subjectId, $resource->location)) {
            // убираем мусор
            if ($resource->filename) {
//                $filePath = realpath(Zend_Registry::get('config')->path->upload->resource) . '/' . $resource->resource_id;
                $filePath = $resource->getFilePath(true);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                $resourcePath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $resource->resource_id . '/';
                $this->getService('Course')->removeDir($resourcePath);
                foreach ($resource->revisions as $revision) {
                    $revisionPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/revision/' . $revision->revision_id . '/';
                    $this->getService('Course')->removeDir($revisionPath);
                }
            }

            $this->getService('Resource')->delete($id);
            $this->getService('Resource')->clearLesson(null, $id);
            $this->getService('ResourceRevision')->deleteBy(array('resource_id = ?' => $id));
        } else
            $this->notDeleted[$id] = $resource;
    }

    public function setDefaults(Zend_Form $form)
    {
        $resourceId = (int)$this->_getParam('resource_id', 0);

        $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));

        if ($resource) {
            $data = $resource->getValues();
            $data['related_resources'] = $this->getService('Resource')->setDefaultRelatedResources($data['related_resources']);
            $form->setDefaults($data);
        }

    }

    public function cardAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $resourceId = (int)$this->_getParam('resource_id', 0);
        $this->view->resource = false;
        $this->view->resource = $this->getService('Resource')->getOne(
            $this->getService('Resource')->find($resourceId)
        );
    }

    public function updateStatus($status)
    {
        $statuses = HM_Resource_ResourceModel::getLocaleStatuses();
        return $statuses[$status];
    }

    public function updateSubject($subject)
    {

        if ($subject != '') {
            return _('Да');
        } else {
            return _('Нет');
        }

    }

    public function updateActions($locationtemp, $subjecttemp, $typetemp, $resourceId, $createdBy, $actions)
    {
        $filename = APPLICATION_PATH . '/../public/upload/webinar-records/' . $resourceId . '.zip';

        if ($typetemp != HM_Resource_ResourceModel::TYPE_WEBINAR) {
            $this->unsetAction($actions, array(
                'module' => 'resource', 'controller' => 'list', 'action' => 'download'
            ));
        } else {
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'delete'));
            if (!file_exists($filename)) {
                return '';
            }
        }

        $isManager = $this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_MANAGER);
        $isTeacher = $this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_TEACHER);
        $isDean = $this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ));
        $isAtManager = $this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL
        ));

        $isRecruiter = $this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL
        ));

        // манагеру всё можно, внутри курса с локальными ресурсами тоже
        if (!($isManager || $isDean || ($isTeacher && $subjecttemp) || $isAtManager || $isRecruiter)) {
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'resource', 'controller' => 'list', 'action' => 'delete'));
        }

        return $actions;
    }

    public function deleteAction()
    {
        $id = (int)$this->_getParam('resource_id', 0);
        if ($id) {
            $temp = $this->delete($id);
            if ($temp === false) {
                $error = true;
            } else {
                $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
            }
            if ($error === false) {
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            } else {
                foreach ($this->notDeleted as $item) {
                    if (count($this->notDeleted)) {
                        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить ресурс, созданный в Базе знаний') . '. Ресурс "' . $item->title . '"" не удалён!', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    }
                }
            }
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            $this->notDeleted = null;
        }
        $this->_redirectToIndex();
    }

    public function updateType($type)
    {
        $types = HM_Resource_ResourceModel::getTypes();
        return $types[$type];
    }

    public function updatePublic($status)
    {
        $statuses = HM_Resource_ResourceModel::getStatuses();
        return $statuses[$status];

    }

    /**
     * TODO: На удаление?
     *
     * Подключить ресурс к учебному модулю из конструктора учебных модулей
     * @return void
     */
    public function assignToCourseAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';

        $ids = explode(',', $this->_getParam('postMassIds_' . $gridId, ''));
        if (count($ids)) {
            foreach ($ids as $id) {
                $resource = $this->getOne($this->getService('Resource')->find($id));
                if ($resource) {
                    $this->getService('CourseItem')->append(
                        array(
                            'title' => $resource->title,
                            'cid' => $this->_courseId,
                            'module' => 0,
                            'vol2' => $resource->resource_id
                        ),
                        $this->_key
                    );
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Ресурсы успешно подключены'));
        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_key, 'subject_id' => $this->_subjectId, 'course_id' => $this->_courseId));
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $temp = $this->delete($id);
                    if ($temp === false) {
                        $error = true;
                    } else {
                        $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
                    }
                }
                if ($error === false) {
                    $this->_flashMessenger->addMessage($this->_getMessage((count($ids) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE));
                } else {
                    $notDeleted = array();
                    foreach ($this->notDeleted as $item) {
                        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить ресурс, созданный в Базе знаний') . '. Ресурс "' . $item->title . '"" не удалён!', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                        $notDeleted[] = $item->title;
                    }
                    if (count($ids) - count($this->notDeleted)) {
                        $this->_flashMessenger->addMessage($this->_getMessage((count($ids) - count($this->notDeleted) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE));
                    } else {
                        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
                    }
                    $this->notDeleted = null;
                }
            }
        }
        $this->_redirectToIndex();
    }

    public function downloadAction()
    {
        $resource_id = (int)$this->_getParam('resource_id', 0);
        $filename = APPLICATION_PATH . '/../public/upload/webinar-records/' . $resource_id . '.zip';
        if ($resource_id && file_exists($filename)) {
            $this->_helper->sendFile($filename, 'application/zip');
            exit();
        }
        $this->_flashMessenger->addMessage(_('Файла записи вебинара не существует'));
        $this->_redirectToIndex();
    }

}