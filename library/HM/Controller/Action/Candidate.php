<?php
class HM_Controller_Action_Candidate extends HM_Controller_Action_Extended
{
   // protected $table='resources';
    protected $service = 'RecruitCandidate';
    protected $idParamName  = 'candidate_id';
    protected $idFieldName = 'candidate_id';

    public function init()
    {
        $this->id = (int) $this->_getParam($this->idParamName, 0);
        $this->view->setExtendedFile('default.tpl');

        $this->view->setContextNavigation(
            'candidate',
            array(
                'candidate_id' => $this->id
            )
        );
        parent::init();
    }
}