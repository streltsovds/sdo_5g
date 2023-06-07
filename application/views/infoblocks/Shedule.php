<?php

class HM_View_Infoblock_Shedule extends HM_View_Infoblock_Abstract
{
    
    protected $id = 'shedule';
    
    public function shedule($param = null)
    {
        $content = $this->view->render('sheduleBlock.tpl');
        
        return $this->render($content);
    }
}