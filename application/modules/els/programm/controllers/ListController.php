<?php
class Programm_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    private $_programmId = 0;
    private $_groupsByProgrammId = array();

    public function init()
    {
        $this->_programmId = (int) $this->_getParam('programm_id', 0);
        $this->_setForm(new HM_Form_Programm());
        parent::init();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'list', 'programm', array());
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Программа успешно создана'),
            self::ACTION_UPDATE => _('Программа успешно обновлена'),
            self::ACTION_DELETE => _('Программа успешно удалена'),
            self::ACTION_DELETE_BY => _('Программы успешно удалены')
        );
    }

    public function indexAction()
    {

        $subSelect = $this->getService('Programm')->getSelect();

        $subSelect
            ->from(
            'programm',
            array(
                'programm.programm_id',
                'study_groups.group_id',
                'study_groups.name')
        )->joinLeft(
            'study_groups_programms',
            'programm.programm_id = study_groups_programms.programm_id',
            array()
        )->joinLeft(
            'study_groups',
            'study_groups_programms.group_id = study_groups.group_id',
            array()
        );

        $stmt =  $subSelect->query();
        $this->_groupsByProgrammId = $stmt->fetchAll();

        $select = $this->getService('Programm')->getSelectElearningProgramms();

        // Область ответственности
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $programmIds = $this->getService('Responsibility')->get($this->getService('User')->getCurrentUserId(), HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM);
            if ($programmIds) {
                $select->where('p.programm_id in (?)', $programmIds);
            }
        }


        $grid = $this->getGrid(
            $select,
            array(
                'programm_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'programm', 'controller' => 'index', 'action' => 'index', 'programm_id' => '')).'{{programm_id}}">{{name}}</a>'
                ),
                'items' => array(
                    'title' => _('Учебные курсы/сессии'),
                    'callback' => array('function' => array($this, 'updateItems'), 'params' => array('{{items}}')),
                    'color' => HM_DataGrid_Column::colorize('subjects')

                ),
                'groups' => array(
                    'title' => _('Учебные группы'),
                    'callback' => array('function' => array($this, 'updateGroups'), 'params' => array('{{programm_id}}')),
                    'color' => HM_DataGrid_Column::colorize('groups')
                )
            ),
            array(
                'name' => null
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $grid->addAction(
                array('module' => 'programm', 'controller' => 'list', 'action' => 'edit'),
                array('programm_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                array('module' => 'programm', 'controller' => 'list', 'action' => 'delete'),
                array('programm_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array('module' => 'programm', 'controller' => 'list', 'action' => 'delete-by'),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateItems($items)
    {
        $subjectService = $this->getService('Subject');
        static $subjectsCache;
            
        if($subjectsCache === null){
            $subjectsCache = array();
            $subjectsCache = $subjectService->fetchAll()->getList('subid', 'name');
                }
        
        $subjectIds = array();
        if($items != ''){
            $subjectIds = explode(',', $items);
            }
        
        $subjectNames = array();
        foreach ($subjectsCache as $subid => $subject) {
            if(in_array($subid, $subjectIds)){
                $subjectNames[] = $subject;
        }
        }

        if (count($subjectNames)) {
            $ret = sprintf('<p class="total">%s</p>', $this->getService('Subject')->pluralFormCount(count($subjectNames)));

            foreach($subjectNames as $subjectName) {
                $ret .= sprintf('<p>%s</p>', $subjectName);
            }
            return $ret;
        }

        return _('Нет');
    }

    public function updateGroups($programmId)
    {
        $groups = array();
        foreach($this->_groupsByProgrammId as $groupByProgrammId){
            if ($groupByProgrammId['programm_id'] == $programmId){
                if($groupByProgrammId['name']){
                    $groups[] = $groupByProgrammId;
                }
            }
        }

        if (count($groups)) {
            $ret = sprintf('<p class="total">%s</p>', $this->getService('StudyGroup')->pluralFormCount(count($groups)));

            foreach($groups as $group) {
                $link = '<a href="'.$this->view->url(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'index', 'group_id' => $group['group_id'])).'">'. $group['name'] .'</a>';
                $ret .= sprintf('<p>%s</p>', $link);
                //'<a href="'.$this->view->url(array('module' => 'programm', 'controller' => 'index', 'action' => 'index', 'programm_id' => '')).'{{programm_id}}">{{name}}</a>'
            }
            return $ret;
        }

        return _('Нет');
    }


    public function create($form)
    {
        $item = $this->getService('Programm')->insert(
            array(
                'name' => $form->getValue('name'),
                'description' => $form->getValue('description'),
                'programm_type' => HM_Programm_ProgrammModel::TYPE_ELEARNING

            )
        );

        if ($form->getValue('icon') != null) {
            HM_Programm_ProgrammService::updateIcon($item->programm_id, $form->getElement('icon'));
        } else {
            HM_Programm_ProgrammService::updateIcon($item->programm_id, $form->getElement('server_icon'));
        }

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            //вызываем, чтобы накинулось ограничение на созданную программу, если есть ограничение по программам
            $isNeedResponsibilityNotification = $this->getService('Dean')->isNeedResponsibilityNotification(
                $this->getService('User')->getCurrentUserId(), $item->getPrimaryKey(), HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM
            );
            /*if ($isNeedResponsibilityNotification) {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_NOTICE,
                    'message' => _("Вы работаете в режиме ограничения области ответственности; программа может быть включена в Вашу в область ответственности администратором системы.")
                ));
            }*/
        }

    }

    public function setDefaults($form)
    {
        if ($this->_programmId) {
            $program = $this->getOne($this->getService('Programm')->find($this->_programmId));
            if ($program) {
                $imageUrl = $this->view->publicFileToUrlWithHash($program->getUserIcon());

                if ($imageUrl) {
                    $iconElement = $form->getElement('icon');
                    $iconElement->setPreviewImg($imageUrl);
                }
                $values = $program->getValues();
                $form->populate($values);
            }
        }
    }

    public function update($form)
    {
        $programId = (int) $form->getValue('programm_id');
        $program = $this->getService('Programm')->update(
            array(
                'programm_id' => $programId,
                'name' => $form->getValue('name'),
                'description' => $form->getValue('description')
            )
        );

        if ($form->getValue('icon') != null) {
            HM_Programm_ProgrammService::updateIcon($programId, $form->getElement('icon'));
        } else {
            HM_Programm_ProgrammService::updateIcon($programId, $form->getElement('server_icon'));
        }

        return $program;
    }

    public function delete($id)
    {
        return $this->getService('Programm')->delete($id);
    }
}