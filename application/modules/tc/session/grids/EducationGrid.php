<?php

use HM_Grid_ColumnCallback_Els_CitiesList   as CitiesList;
use HM_Grid_ColumnCallback_Els_UserCardLink as UserCardLink;
use HM_Tc_Application_ApplicationModel      as TcApplicationModel;

class HM_Session_Grid_EducationGrid extends HM_Grid
{
    protected $applicationsCache = array();

    protected static $_defaultOptions = array(
        'sessionId'    => 0,
        'defaultOrder' => 'fio_ASC',
        'showApplyMassAction' => false
    );

    protected function _initColumns()
    {
        $userCardLink   = new UserCardLink();

        $view       = $this->getView();
        $controller = $this->getController();

        $this->_columns = array(
            'application_id'        => array('hidden' => true),
            'user_id'               => array('hidden' => true),
            'criterion_id'          => array('hidden' => true),
            'subject_id'            => array('hidden' => true),
            'session_department_id' => array('hidden' => true),

            'fio'             => array(
                'title'    => _('ФИО'),
                'callback' => $userCardLink->getCallback('{{user_id}}', '{{fio}}')
            ),
//            'position_full' => array('title'  => _('Подразделение')),
            'department'      => array('title'  => _('Подразделение'), 'hidden' => true),
            'position_id'      => array('hidden' => true),
            'is_manager'      => array('hidden' => true),
            'position' => array(
                'title' => _('Должность'),
                'callback' => array(
                    'function' => array($this, 'updatePositionName'),
                    'params' => array(
                        '{{position}}',
                        '{{position_id}}',
                        HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                        '{{is_manager}}'
                    )
                )
            ),
            'manager_id'      => array('hidden' => true),
            'user_city'       => array('hidden' => true),
            'criterion'       => array('title'  => _('Квалификация')),
            'expire'          => array('hidden' => true),
            'period'          => array(
                'title'    => _('Планируемый срок обучения'),
                'callback' => array(
                    'function' => array($controller, 'monthDate'),
                    'params'   => array('{{period}}')
                )
            ),
            'status'          => array('hidden' => true),
            'subject_name'    => array(
                'title'     => _('Курс'),
                'callback'  => array(
                    'function' => array($this, 'removeEmptyCard'),
                    'params'   => array('{{subject_id}}', $view->cardLink($view->url(array('module' => 'subject',  'controller' => 'fulltime', 'action' => 'card', 'subject_id' => '')) . '{{subject_id}}', _('Карточка внешнего курса')) . ' <a href="' . $view->url(array('module' => 'subject',  'controller' => 'fulltime', 'action' => 'view', 'subject_id' => '{{subject_id}}'), null, true, false) . '">{{subject_name}}</a>'))
            ),
            'subject_city'    => array('hidden' => true),
            'longtime'        => array('title'  => _('Длительность')),
            'price'          => array(
                'title'     => _('Стоимость'),
                'style'     => "text-align:right",
                'callback'  => array(
                    'function' => 'number_format',
                    'params'   => array('{{price}}', 0, '.', ' ')
                )
            )
        );

        if ($controller->getGridCategory() == TcApplicationModel::CATEGORY_REQUIRED) {
            $this->_columns['expire'] = array(
                'title'    => _('Срок окончания действия'),
                'callback' => array(
                    'function' => array($controller, 'monthDate'),
                    'params'   => array('{{expire}}')
                )
            );

            $this->_columns['price'] = array('hidden' => true);
        } elseif($controller->getGridCategory() == TcApplicationModel::CATEGORY_RECOMENDED) {
            $this->_columns['period'] = array('hidden' => true);
        }
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $controller = $this->getController();

        $filters->add(array(
            'fio'       => true,
//            'department' => array('render' => 'department'),
            'position'  => array('render' => 'department'),
//            'position_full' =>array(
//                'callback' => array(
//                    'function'=>array($this, 'positionFullFilter'),
//                    'params'=>array()
//                )
//            ),
            'user_city' => array('callback' => array(
                'function' => array($controller, 'filterCities'),
                'params'   => array('type' =>  HM_Classifier_Link_LinkModel::TYPE_STRUCTURE))),
            'criterion' => true,
            'expire'    => array('render' => 'Date'),
            'period'    => array('render' => 'Date'),
            'subject_name' => true,
            'subject_city' => array('callback' => array(
                'function' => array($controller, 'filterCities'))),
            'longtime'  => array('render' => 'Number'),
            'price'     => array('render' => 'Number')
        ));
    }



    public function positionFullFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){

            $value = '%' . $value . '%';

            $select->where("(so.name LIKE ?", $value);
            $select->orWhere("so2.name LIKE ?", $value);
            $select->orWhere("so3.name LIKE ?)", $value);


        }

    }

    protected function _initActions(HM_Grid_ActionsList $actions)
    {
        $session = $this->getController()->getSession();
        if (!$this->getService('TcSession')->isApplicable($session)) return;

        if ($this->getController()->getGridCategory() == TcApplicationModel::CATEGORY_REQUIRED) {
            $action = $actions->add('edit', array(
                'module' => 'application',
                'controller' => 'list',
                'action' => 'edit',
                'session_id' => $session->session_id
            ));
        } else {
            $action = $actions->add(_('Подать заявку'), array(
                'module' => 'application',
                'controller' => 'list',
                'action' => 'create-recommended',
                'session_id' => $session->session_id
            ));
        }

        $action->setParams(array(
            'application_id'
        ));
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        $session = $this->getController()->getSession();
        if (!$this->getService('TcSession')->isApplicable($session)) return;

        if ($this->getService('TcSession')->isApplicable($session)) {

// @todo: реализовать тут же выбор периода и раскомментировать
//            $massActions->add(
//                array(
//                    'module'     => 'session',
//                    'controller' => 'education',
//                    'action'     => 'mass-apply',
//                    'session_id' => $session->session_id,
//                    'category'   => $this->getController()->getGridCategory()
//                ),
//                _('Включить в консолидированную заявку'),
//                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
//            );
        }
    }

    public function updateManager($field)
    {
        static $managersCache = null;
        if($managersCache === null){
            $res = $this->_grid->getResult();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['manager_id'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $managersCache = $this->getService('User')->fetchAll(array('MID IN (?)' => $tmp));
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Supervisor')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $managersCache->exists('MID', $value);
            $name = implode(' ', array($tempModel->LastName, $tempModel->FirstName, $tempModel->Patronymic));
            $result[] = "<p>{$name}</p>";
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }

    public function removeEmptyCard($sourceField, $cardField)
    {
        if (!$sourceField) {
            return '';
        }

        return $cardField;
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        $controller = $this->getController();

        switch ($controller->getGridCategory()) {
            case TcApplicationModel::CATEGORY_REQUIRED:
                if (
                    ($row['status'] == TcApplicationModel::STATUS_COMPLETE) ||
                    empty($row['subject_id'])
                ) {
                    $actions->setInvisibleActions(array(
                        'edit'
                    ));
                }
                break;
        }
    }
}