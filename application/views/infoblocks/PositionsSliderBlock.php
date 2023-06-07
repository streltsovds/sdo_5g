<?php

class HM_View_Infoblock_PositionsSliderBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'positionssliderblock';
    
    public function positionsSliderBlock($param = null)
    {
        $services = Zend_Registry::get('serviceContainer');
        $positions = $services->getService('HrReservePosition')->fetchAllDependence('ReserveRequest', array('in_slider = ?' => 1));
        if (!$positions || count($positions) == 0) return false;

        $this->view->positions = $positions;
        $this->view->userId = $services->getService('User')->getCurrentUserId();

        $content = $this->view->render('positionsSliderBlock.tpl');

        
        return $this->render($content);

    }
}