<?php


class HM_View_Infoblock_DeputyBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'deputy';

    public function deputyBlock($param = null)
    {
        $service = Zend_Registry::get('serviceContainer')->getService('Deputy');

        if (! $service->testDeputy() ) return null;

        $deputy = $service->whoIsMyDeputy(); // Есть ли у меня заместитель на тек. дату?
        $user = $service->whoseDeputyIam(); // Замещаю ли я кого-либо на тек. дату?


        $this->view->deputy = $deputy;
        $this->view->user = $user;



    	$content = $this->view->render('deputyBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/deputy/style.css');
//        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/claims/script.js');

        
        return $this->render($content);
    }
}