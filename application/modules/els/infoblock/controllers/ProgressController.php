<?php

class Infoblock_ProgressController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

	public function init()
	{
		parent::init();
        $this->_helper->layout()->setLayout('ajax.tpl');
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
	}

    public function indexAction()
    {
        $service = Zend_Registry::get('serviceContainer');

        $subjects = $service->getService('Dean')->getSubjectsResponsibilities($service->getService('User')->getCurrentUserId());

        $list = $subjects->getList('subid', 'name');

        $keys = array_keys($list);

        if (!count($keys)) {
            $keys = array(0);
        }

        if (!count($keys)) {
            $keys = array(0);
        }

        $select = $service->getService('Dean')->getSelect();

        $select->from(
            array('s' => 'Students'),
            array(
                'subject_id' => 's.CID',
                'amount' => new Zend_Db_Expr('COUNT(s.MID)'))
        )
            ->join(array('p' => 'People'),
                's.MID = p.MID AND p.blocked = 0',
                array()
            )
            ->where('s.CID IN (?)', $keys)
            ->group(array('s.CID', 'p.MID'));

        $fetch = $select->query()->fetchAll();
        $students = $this->expand($fetch);


        $select = $service->getService('Dean')->getSelect();

        $select->from(
            array('g' => 'graduated'),
            array(
                'subject_id' => 'g.CID',
                'amount' => new Zend_Db_Expr('COUNT(g.MID)'))
        )
            ->join(array('p' => 'People'),
                'g.MID = p.MID AND p.blocked = 0',
                array()
            )
            ->where('g.CID IN (?)', $keys)
            ->where('g.certificate_id IS NOT NULL')
            ->group(array('g.CID', 'p.MID'));

        $fetch = $select->query()->fetchAll();
        $graduated = $this->expand($fetch);

        $result = array();
        foreach($subjects as $key => $value){

            $resultRow  = $this->createRow($value, $students, $graduated);
            if($resultRow['all'] != 0){
                $result[] = $resultRow;
            }
        }

        $grid = $this->getGrid($result,  array(
            'subid' => array('hidden' => true),
            'name' => array(
                'title' => _('Название курса'),
                'decorator' => $this->view->cardLink(
                    $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => ''), null, true) . '{{subid}}', _('Карточка учебного курса'))
                    . "<a href='{$this->view->url(array('module' => 'marksheet', 'controller' => 'index', 'action' => 'index', 'subject_id' => ''))}{{subid}}'>{{name}}</a>"
            ),
            'all' => array('title' => _('Записаны')),
            'students' => array('title' => _('Учатся')),
            'graduated' => array('title' => _('Завершили')),
            'percent' => array('title' => _('%')),
        ), array('name' => null));

        $grid->setNumberRecordsPerPage(5);

        $grid->deploy();
	}

    public function expand($array){

        $ret = array();

        foreach($array as $val){
            $ret[$val['subject_id']] = $val['amount'];
        }

        return $ret;
    }

    public function createRow($model, $students, $graduated){
        $all = $students[$model->subid] + $graduated[$model->subid];
        $allDiv = $all == 0 ? 1 : $all;

        return array(
            'subid' => $model->subid,
            'name' => $model->name,
            'all' => $all,
            'students' => (int)$students[$model->subid],
            'graduated' => (int)$graduated[$model->subid],
            'percent' => round($graduated[$model->subid] / $allDiv * 100)
        );
    }

	
	public function getCvsAction(){
	    $result = $this->view->progressBlock('','',array('format' => 'array'));
	    $this->view->data = $result;
	}
	
	
	
}