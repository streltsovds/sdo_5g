<?php


class HM_View_Infoblock_HhBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;

    protected $id = 'hh';

    public function hhBlock($param = null)
    {
        $vacancy = $options['subject']; 
        $services = Zend_Registry::get('serviceContainer');
            
        $this->view->vacancy = $vacancy;
        
        $content = $this->view->render('hhBlock.tpl');
        return $this->render($content);
    }
}