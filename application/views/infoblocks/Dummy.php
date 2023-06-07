<?php

class HM_View_Infoblock_Dummy extends HM_View_Infoblock_Abstract
{                                          
    
    protected $id = 'dummy';
    
    public function dummy($param = null)
    {
        $content = $this->view->render('dummyBlock.tpl');
        
        return $this->render($content);
    }
}