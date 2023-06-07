<?php

/*abstract*/ class HM_View_Helper_MaterialAbstract extends HM_View_Helper_Abstract
{
    public function render($material)
    {
        $this->view->material = $material;

        $this->view->materialContentUrl = $this->view->url(array(
            'module'=> 'file',
            'controller' => 'get',
            'action' => 'resource',
            'resource_id' => $material->resource_id,
        ), null, true);




//        $this->view->materialContentUrl = 'http://develop50/upload/resources/174.mp4';


        $type = lcfirst(str_replace('HM_View_Helper_Material', '', get_class($this)));
        return $this->view->render("material/{$type}.tpl");
    }
}