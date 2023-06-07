<?php
class Newcomer_SubjectsController extends HM_Controller_Action
{

    protected $_user;
    protected $_newcomer;

    public function init()
    {
        if ($newcomerId = $this->_getParam('newcomer_id', 0)) {
            $this->_newcomer = $this->getService('RecruitNewcomer')->getOne($this->getService('RecruitNewcomer')->findDependence(array('User', 'Cycle'), $newcomerId));
            if ($this->_newcomer->newcomer_id && count($this->_newcomer->user)) {
                $this->_user = $this->_newcomer->user->current();
            }
//            $this->view->setExtended(
//                array(
//                    'subjectName' => 'RecruitNewcomer',
//                    'subjectId' => $newcomerId,
//                    'subjectIdParamName' => 'newcomer_id',
//                    'subjectIdFieldName' => 'newcomer_id',
//                    'subject' => $this->_newcomer
//                )
//            );
        }
        parent::init();
    }


    public function indexAction()
    {

        $select = $this->getService('RecruitNewcomer')->getSubjectsGridSelect($this->_newcomer->newcomer_id);
        $grid = HM_Newcomer_Grid_RecruitNewcomerSubjectsGrid::create(array('ajax' => $this->isAjaxRequest()));

        $isAjax = $this->isAjaxRequest();
        if ($isAjax) {
            $qq = $grid->init($select);
            echo $qq;
            die();
        } else {
            $this->view->grid = $this->getDataGridMarkup();
        }
    }

}