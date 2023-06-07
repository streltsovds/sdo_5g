<?php

class HM_View_Helper_MaterialText extends HM_View_Helper_MaterialAbstract
{
    public function materialText($material)
    {
        $fileName = preg_replace('{/$}', '', Zend_Registry::get('config')->path->upload->resource) . '/' . $material->resource_id;
        $material->content = nl2br(file_get_contents($fileName));

        return parent::render($material);
    }
}