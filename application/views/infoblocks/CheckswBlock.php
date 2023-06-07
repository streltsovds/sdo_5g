<?php


class HM_View_Infoblock_CheckswBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'resources';
    protected $session;

    public function checkswBlock($param = null)
    {
    	$content = $this->view->render('checkswBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/checksw/style.css');

        
        return $this->render($content);
    }
}