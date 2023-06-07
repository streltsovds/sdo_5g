<?php

/*abstract*/ class HM_View_Helper_MaterialAbstract extends HM_View_Helper_Abstract
{
    public function render($material)
    {
        $this->view->material = $material;

        $this->view->materialContentUrl = $this->view->url(array(
            'module'=> 'resource',
            'controller' => 'index',
            'action' => 'data',
            'resource_id' => $material->resource_id,
        ), null, true);

        $type = strtolower(str_replace('HM_View_Helper_Material', '', get_class($this)));
        return $this->view->render("material/preview/{$type}.tpl");
    }
}