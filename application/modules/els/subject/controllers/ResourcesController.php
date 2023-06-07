<?php

class Subject_ResourcesController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');

        $gridId = "grid{$this->_subjectId}";
        $default   = new Zend_Session_Namespace('default');
        $order     = $this->_request->getParam("order{$gridId}");

        if (!isset($default->grid['resource-list-index'][$gridId])) {
            $default->grid['resource-list-index'][$gridId]['filters']['subject'] = $this->_subjectId; // по умолчанию показываем только слушателей этого курса
        }
        if ($order == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $filters = array(
            'title' => null,
            'updated' => array(
                'render' => 'date',
                array(
                    'transform' => 'dateChanger'
                )
            ),
            'type' => array('values' => $this->_subjectId),
            'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
            'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            'public' => array('values' => HM_Resource_ResourceModel::getStatuses())
        );

        $subSelect = $this->getService('Resource')->getSelect();
        $subSelect
            ->from(array('s' => 'subjects_resources'), array('subject_id', 'resource_id', 'subject'))
            ->where($this->getService('Resource')->quoteInto(
                array('subject_id = ? AND subject = \'subject\''),
                array($this->_subjectId))
            );

        $select = $this->getService('Resource')->getSelect();
        $select->from(
            array('r' => 'resources'),
            array(
                'r.resource_id',
                'r.created_by',
                'r.title',
                'r.location',
                'locationtemp'   =>'r.location',
                'statustemp'     => 'r.status',
                'subjecttemp'    => 'r.subject_id',
                'subject'        => 's.subject_id',
                'subjectType'    => 'r.subject',
                'type',
                'filetype',
                'filename',
                'activity_type',
                'typetemp'       => 'r.type',
                'r.volume',
                'r.updated',
                'tags' => 'r.resource_id'
            ));

        $select->joinLeft(
                array('s' => $subSelect),
                'r.resource_id = s.resource_id',
                array()
            )
            ->where('
                (r.location = ' . (int) HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL . ' AND r.status IN (' . (int) HM_Resource_ResourceModel::STATUS_PUBLISHED . ',' . (int) HM_Resource_ResourceModel::STATUS_STUDYONLY . ')) OR 
                (r.subject_id = ' . (int) $this->_subjectId.' AND r.subject =\'subject\')
            ')
            ->where('r.db_id IS NULL OR r.db_id = ?', '') // что это?
            ->where('r.parent_id = 0 OR r.parent_id IS NULL');

        $grid = $this->getGrid(
            $select,
            array(
                'resource_id' => array('title' => '#'),
                'subjecttemp' => array('hidden' => true),
                'subjectType'    => array('hidden' => true),
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
                'location' => array('title' => _('Место хранения')),
                'type' => array('title' => _('Тип ресурса')),
                'subject' => array(
                    'title' => _('Доступ для слушателей'),
                    'callback' => array(
                        'function' => array($this, 'updateSubjectColumn'),
                        'params' => array(HM_Event_EventModel::TYPE_RESOURCE, '{{resource_id}}', '{{subject}}', $this->_subjectId, 'subject')
                    )
                ),
                'public' => array('title' => _('Статус')),
                'tags' => array('title' => _('Метки'))
            ),
            $filters,
            $gridId
        );

        $options = array(
            'local' => array(
                'name' => 'local',
                'title' => _('используемые в данном учебном курсе'),
                'params' => array(
                    'subject' => $this->_subjectId,
                    'subjecttype' => 'subject',
                )
            ),
            'global' => array(
                'name' => 'global',
                'title' => _('все, включая ресурсы из Базы знаний'),
                'params' => array(
                    'subject' => null
                ),
                'order' => 'subject',
                'order_dir' => 'DESC'
            ),
        );

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
        $options = $event->getReturnValue();

        $grid->setGridSwitcher($options);

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
            array('module' => 'subject', 'controller' => 'resources', 'action' => 'assign'),
            _('Использовать в курсе и открыть свободный доступ для слушателей')
        );

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'resources', 'action' => 'unassign'),
            _('Не использовать в курсе и закрыть доступ для слушателей')
        );

        $grid->addMassAction(
            array('module' => 'resource', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->updateColumn('location',
            array('callback' =>
                array('function' =>
                    array($this,'updateStatus'),
                    'params'   => array('{{location}}')
                )
            )
        );

        $grid->updateColumn('type',
            array('callback' =>
                array('function' =>
                    array($this,'updateType'),
                    'params'   => array('{{type}}')
                )
            )
        );

        $grid->updateColumn('public',
            array('callback' =>
                array('function' =>
                    array($this,'updatePublic'),
                    'params'   => array('{{public}}')
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
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{tags}}', $this->getService('TagRef')->getResourceType(), $this->subjectId, '{{locationtemp}}')
            )
        ));

        $grid->setClassRowCondition("'{{subject}}' != ''", "success");

        $this->view->subjectId = $this->_subjectId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function assignAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        $subjectId = $this->_subjectId;
        
        if (strlen($postMassIds)) {

            $ids = explode(',', $postMassIds);
            $section = $this->getService('Section')->getDefaultSection($subjectId, 'subject');
            $currentOrder = $this->getService('Section')->getCurrentOrder($section);

            if (count($ids)) {
                foreach($ids as $id) {

                    $res = $this->getService('SubjectResource')->find($this->_subjectId, $id, 'subject');

                    if(count($res) == 0){
                        $rr = $this->getService('SubjectResource')->insert(array(
                            'subject_id' => $this->_subjectId,
                            'resource_id' => $id,
                            'subject' => 'subject',
                        ));
                    }
                    $this->getService('Resource')->createLesson($this->_subjectId, $id, $section, ++$currentOrder, 'subject');
                }

                $this->getService($this->service)->update(array(
                    'last_updated' => $this->getService($this->service)->getDateTime(),
                    $this->idFieldName => $this->_subjectId
                ));


                $this->_flashMessenger->addMessage(_('Информационные ресурсы успешно назначены на курс'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $bool = false;
                foreach($ids as $id) {
                    $this->getService('Resource')->clearLesson($this->_subjectId, $id,'subject');
                    $this->getService('SubjectResource')->delete(array($this->_subjectId, $id,'subject'));
                }

                if($bool == false){
                    $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
                }else{
                    $this->_flashMessenger->addMessage(_('Невозможно отменить назначение для некоторых информационных ресурсов'));
                }
            }
        }
        $this->_redirectToIndex();
    }

    public function updateStatus($status)
    {
        $statuses = HM_Resource_ResourceModel::getLocaleStatuses();
        return $statuses[$status];
    }

    public function updateSubject($subject)
    {
        if($subject !=''){
            return _('Да');
        }else{
            return _('Нет');
        }
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
}