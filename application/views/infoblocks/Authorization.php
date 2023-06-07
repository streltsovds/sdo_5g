<?php

require_once APPLICATION_PATH . '/modules/els/default/forms/Authorization.php';
class HM_View_Infoblock_Authorization extends HM_View_Infoblock_Abstract
{

    protected $id = 'authorization';

    public function authorization($param = null)
    {
        $form = new HM_Form_Authorization();
        $this->view->form = $form;
        $content = $this->view->render('Authorization.tpl');
        //$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/authorization/style.css');
        return $this->render($content);
    }
}