<?php

class HM_View_Infoblock_CarouselBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'carousel';

    public function carouselBlock($param = null)
    {
        $result = $this->getService('User')->getOnlineMates();
        $currentItems = iterator_to_array($result->getCurrentItems());
        $this->view->data = HM_Json::encodeErrorSkip($currentItems);
        $this->view->searchUrl = HM_Json::encodeErrorSkip(Zend_Registry::get('view')->url([
            'module' => 'user',
            'controller' => 'index',
            'action' => 'get-online-mates',
        ]));
        $content = $this->view->render('carouselBlock.tpl');


        return $this->render($content);
    }
}
