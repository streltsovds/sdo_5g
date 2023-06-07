<?php

class Cycle_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    /** @var  HM_Cycle_CycleModel $cycle */
    protected $cycle;

    public function init()
    {
        $form = new HM_Form_Cycle();

        $cycleId = $this->_getParam('cycle_id');
        if ($cycleId) {
            $cycle = $this->getService('Cycle')->getOne($this->getService('Cycle')->find($cycleId));
            if ($cycle) {
                $this->cycle = $cycle;
                $form->getElement('type')
                    ->setAttrib('disabled', true)
                    ->setValue($cycle->type);
            }
        }
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {

        if($this->_getParam('ordergrid', '') == ''){
            $this->_setParam('ordergrid', 'begin_date_DESC');
        }

        $select = $this->getService('Cycle')->getSelect();
        $select->from(
                array('c' => 'cycles'),
                array('c.cycle_id', 'c.name', 'cycle_type' => 'c.type','cycle_type_kod' => 'c.type', 'c.begin_date', 'c.end_date','created_by')
            )
            ->where(new Zend_Db_Expr('newcomer_id IS NULL'));

        $grid = $this->getGrid(
            $select,
            array(
                'cycle_id'   => array('hidden' => true),
                'created_by'   => array('hidden' => true),
                'cycle_type_kod'   => array('hidden' => true),
                'name'       => array('title'  => _('Название')),
                'cycle_type' => array(
                    'title'    => _('Область применения'),
                    'callback' => array(
                        'function' => 'HM_Cycle_CycleModel::getCycleType',
                        'params'   => array('{{cycle_type}}'))
                ),
                'begin_date' => array('title'  => _('Даты цикла')),
                'end_date'   => array('hidden' => true)
            ),
            array(
                'name'       => null,
                'cycle_type' => array('values' => HM_Cycle_CycleModel::getCycleTypes())
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_RecruiterModel::ROLE_HR_LOCAL
        ))) {
            $grid->addAction(
                array('module' => 'cycle', 'controller' => 'list', 'action' => 'edit'),
                array('cycle_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                array('module' => 'cycle', 'controller' => 'list', 'action' => 'delete'),
                array('cycle_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

//         $grid->addMassAction(
//             array('module' => 'cycle', 'controller' => 'list', 'action' => 'delete-by'),
//             _('Удалить'),
//             _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
//         );

            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                    'params'   => array(array('created_by' => '{{created_by}}','cycle_type_kod' => '{{cycle_type_kod}}'))
                )
            );



            $grid->updateColumn('begin_date',
                array(
                    'callback' =>
                        array(
                            'function' => array($this, 'getDateString'),
                            'params' => array('{{begin_date}}', '{{end_date}}', '{{timetype}}')
                        )
                )
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }


    public function getDateString($begin_date, $end_date)
    {
        $begin = new HM_Date($begin_date);
        $end   = new HM_Date($end_date);
        return sprintf('%s - %s', $begin->get(Zend_Date::DATE_MEDIUM), $end->get(Zend_Date::DATE_MEDIUM));
    }

    public function create(Zend_Form $form)
    {
        $values = $form->getValues();
        $begin = substr($values['begin_date'], 6, 4) . '-' . substr($values['begin_date'], 3, 2) . '-' . substr($values['begin_date'], 0, 2);
        $end = substr($values['end_date'], 6, 4) . '-' . substr($values['end_date'], 3, 2) . '-' . substr($values['end_date'], 0, 2);


        $cycle = $this->getService('Cycle')->insert(
            array(
                'name'       => $form->getValue('name'),
                'type'       => $form->getValue('type'),
                'begin_date' => $begin,
                'end_date'   => $end,
                'created_by'   => $this->getService('User')->getCurrentUserId(),
            )
        );

        if (
            !$this->getService('Extension')->getRemover('HM_Extension_Remover_AtKpiRemover') &&
            ($cycle->type == HM_Cycle_CycleModel::CYCLE_TYPE_ASSESMENT)
        ) {
            $this->getService('AtKpiProfile')->assignUserKpisByCycle($cycle);
        }
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getParams();
            if ($form->isValid($post)) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            } else {
                $post['type'] = $this->cycle->type;
                $form->populate($post);
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function update(Zend_Form $form)
    {
        if(!$this->cycle){
            return false;
        }

        $values = $form->getValues();
        $begin = substr($values['begin_date'], 6, 4) . '-' . substr($values['begin_date'], 3, 2) . '-' . substr($values['begin_date'], 0, 2);
        $end = substr($values['end_date'], 6, 4) . '-' . substr($values['end_date'], 3, 2) . '-' . substr($values['end_date'], 0, 2);

        $this->getService('Cycle')->update(
             array(
                 'cycle_id'   => $form->getValue('cycle_id'),
                 'name'       => $form->getValue('name'),
                 'begin_date' => $begin,
                 'end_date'   => $end,
                 'created_by'   => $this->getService('User')->getCurrentUserId(),
             )
         );
    }

    public function delete($id)
    {
        if ($this->cycle) {
            // DEPRECATED
            /*if ($this->cycle->type == HM_Cycle_CycleModel::CYCLE_TYPE_STUDYCENTER) {
                $this->getService('ScPollSession')->deletePollSession($this->cycle->cycle_id);
            }*/
        }
        $this->getService('Cycle')->delete($id);
        return true;
    }


    public function setDefaults(Zend_Form $form)
    {
        if ($this->cycle) {
            $data = $this->cycle->getValues();
            $data['begin_date'] = date('d.m.Y', strtotime($data['begin_date']));
            $data['end_date']   = date('d.m.Y', strtotime($data['end_date']));

            $form->setDefaults($data);
        }
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Cycle')->getDefaults();
        $defaults['name'] = $this->_getParam('title');
        if (strlen($defaults['name'])) {
            if ($type = $this->_getParam('type')) {
                if (HM_Cycle_CycleModel::getCycleTypes($type)) {
                    $defaults['type'] = $type;
                }
            }

            if ($cycle = $this->getService('Cycle')->insert($defaults)) {
                $result = $cycle->cycle_id;
            }
        }
        exit(HM_Json::encodeErrorSkip($result));
    }

    public function updateActions($params, $actions)
    {
        $atManager = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER));
        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL)) &&
            ($params['created_by'] != $this->getService('User')->getCurrentUserId())
            || ($params['cycle_type_kod'] == HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING
                && $atManager)
        ) {
            $this->unsetAction($actions, array('controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('controller' => 'list', 'action' => 'delete'));
        }
        return $actions;
    }
    
}