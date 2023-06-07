<?php

class HM_View_Infoblock_DummySecond extends HM_View_Infoblock_Abstract
{                                          
    
    protected $id = 'dummySecond';
    
    public function dummySecond($param = null)
    {
        $content = $this->view->render('dummySecondBlock.tpl');
        
        return $this->render($content);
    }
}