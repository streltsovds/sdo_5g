<?php
class Session_MonitoringController extends HM_Controller_Action_Session
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction()
    {

        $session_id = $this->_request->getParam("session_id");

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'fio_ASC');
        }

        $this->_request->setParam('decimalPlaces', 0);

        $select = $this->getService('AtSessionUser')->getSelect();
        $select0 = clone $select;
        $subselect1 = clone $select;
        $subselect2 = clone $select;

        $subselect1->from(
            array('asu' => 'at_session_users'),
            array(
                'session_id' => 'asu.session_id',
                'owner_soid' => 'so.owner_soid',
                'amount_user' => new Zend_Db_Expr("COUNT(asu.session_user_id)"),
                'passed' => new Zend_Db_Expr("SUM(CASE WHEN asu.status = 2 THEN 1 ELSE 0 END)")
            )
        )->joinInner(
            array('so' => 'structure_of_organ'),
            "asu.position_id = so.soid",
            array()
        )->where(
            "asu.session_id = ?", $session_id
        )->group(
            array("asu.session_id", "so.owner_soid")
        );

        $subselect2->from(
            array('ase' => 'at_session_events'),
            array(
                'session_id' => 'asr.session_id',
                'owner_soid' => 'so.owner_soid',
                'amount_event' => new Zend_Db_Expr("COUNT(ase.session_event_id)"),
                'passed' => new Zend_Db_Expr("SUM(CASE WHEN ase.status = 1 THEN 1 ELSE 0 END)")
            )
        )->joinInner(
            array('asr' => 'at_session_respondents'),
            "ase.session_respondent_id = asr.session_respondent_id",
            array()
        )->joinInner(
            array('so' => 'structure_of_organ'),
            "so.soid = asr.position_id",
            array()
        )->where(
            "asr.session_id = ?", $session_id
        )->group(
            array("asr.session_id", "so.owner_soid")
        );

        $select0->from(
            array('z' => $subselect1),
            array(
                'soid' => 'z.owner_soid',
                'department' => 'so.name',
                'amount_user' => 'z.amount_user',
                'percentage_user' => new Zend_Db_Expr("CASE WHEN z.amount_user IS NULL OR z.amount_user = 0 THEN 0 ELSE z.passed * 100 / z.amount_user END"),
                'amount_event' => 'z2.amount_event',
                'percentage_respondent' => new Zend_Db_Expr("CASE WHEN z2.amount_event IS NULL OR z2.amount_event = 0 THEN 0 ELSE z2.passed * 100 / z2.amount_event END"),
            )
        )->joinInner(
            array('so' => 'structure_of_organ'),
            "z.owner_soid = so.soid",
            array()
        )->joinLeft(
            array('z2' => $subselect2),
            "z.owner_soid = z2.owner_soid and z.session_id = z2.session_id",
            array()
        );

        $select->from(array('s' => $select0), array(
            'soid' => 's.soid',
            'department' => 's.department',
            'amount_user' => 's.amount_user',
            'percentage_user' =>'s.percentage_user',
            'amount_event' => 's.amount_event',
            'percentage_respondent' => 's.percentage_respondent',
        ));


        $grid = $this->getGrid(
            $select,
            array(
                'soid' => array('hidden' => true),
                'department' => array(
                    'title' => _('Подразделение'),
//                'callback' => array(
//                    'function'=> array($this, 'departmentsCache'),
//                    'params' => array('{{department}}', $select)
//                )
                ),
                'amount_user' => array(
                    'title' => _('Количество участников'),
                ),
                'percentage_user' => array(
                    'title' => _('Процент завершения сессии участниками'),
                ),
                'amount_event' => array(
                    'title' => _('Количество оценочных форм'),
                ),
                'percentage_respondent' => array(
                    'title' => _('Процент заполнения форм '),
                )

            ),
            array(
                'department' => array(),
                'amount_user' => array('render' => 'number'),
                'percentage_user' => array('render' => 'number'),
                'amount_event' => array('render' => 'number'),
                'percentage_respondent' => array('render' => 'number'),
            ),
            'grid'
        );

        $grid->setPrimaryKey(array('soid'));

        $grid->addMassAction($this->view->url(array(
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index',
            'session_id' => null,
            'baseUrl' => '',
        )),
            _('Отправить сообщение руководителю')
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }
    // @todo: оптимизировать!! это всё загнётся при реальной оц.сессии на всю компанию
    // Переделал все (см. ниже)
    public function indexAction______________________()
    {
        $session_id = $this->_request->getParam("session_id");

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'fio_ASC');
        }

        $select = $this->getService('Orgstructure')->getSelect();

        $select->from(array('so' => 'structure_of_organ'), array(
            'soid' => 'so.owner_soid',
            'MID' => new Zend_Db_Expr("COUNT(p.MID)"),
            'department' => 'so.owner_soid',
            'amount_user' => 'so.owner_soid',
            'percentage_user' => 'so.owner_soid',
            'amount_event' => 'so.owner_soid',
            'percentage_respondent' => 'so.owner_soid',
        ));

        $select->join(array('asu' => 'at_session_users'), 'so.soid = asu.position_id', array());
        $select->join(array('p' => 'People'), 'p.MID = asu.user_id', array());

        $select->where('asu.session_id = ?',  $session_id);

        $select->group(array('so.owner_soid'));

        $grid = $this->getGrid($select, array(
            'MID' => array('hidden' => true),
            'soid' => array('hidden' => true),
            //'soid' => array('hidden' => true),
            'department' => array(
                'title' => _('Подразделение'),
                'callback' => array(
                    'function'=> array($this, 'departmentsCache'),
                    'params' => array('{{department}}', $select)
                )
            ),
            'amount_user' => array(
                'title' => _('Количество участников'),
                'callback' => array(
                    'function'=> array($this, 'getAmountUser'),
                    'params' => array('{{amount_user}}', $session_id)
                )
            ),
            'percentage_user' => array(
                'title' => _('Процент завершения сессии участниками'),
                'callback' => array(
                    'function'=> array($this, 'getPercentageUser'),
                    'params' => array('{{percentage_user}}', $session_id)
                )
            ),
            'amount_event' => array(
                'title' => _('Количество оценочных форм'),
                'callback' => array(
                    'function'=> array($this, 'getAmountEvent'),
                    'params' => array('{{amount_event}}', $session_id)
                )
            ),
            'percentage_respondent' => array(
                'title' => _('Процент заполнения форм '),
                'callback' => array(
                    'function'=> array($this, 'getPercentageRespondent'),
                    'params' => array('{{percentage_respondent}}', $session_id)
                )
            ),
        ),
            array(
                'department' => array(
                    'callback' => array(
                        'function'=>array($this, 'departmentFilter'),
                        'params'=>array()
                    )
                ),
            )
        );

        $grid->addMassAction($this->view->url(array(
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index',
            'session_id' => null,
            'baseUrl' => '',
        )),
            _('Отправить сообщение руководителю')
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

    }

}
