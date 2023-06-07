<?php


class HM_View_Infoblock_ProgressBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'Progress';
    
    public function progressBlock($param = null)
    {
        $this->view->url = "/infoblock/progress";

        $content = $this->view->render('ProgressBlock.tpl');

        return $this->render($content);
    }
}