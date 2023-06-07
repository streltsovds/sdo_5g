<?php
require_once 'FLV/flvinfo.php';

class HM_View_Helper_MaterialFlash extends HM_View_Helper_MaterialAbstract
{
    public function materialFlash($material)
    {
        $resourceType = pathinfo($material->filename, PATHINFO_EXTENSION);

        if (strtolower($resourceType) == 'flv'){

            $flvInfo = new Flvinfo();
            $meta = $flvInfo->getInfo(Zend_Registry::get('config')->path->upload->resource.'/'.$material->resource_id, true);

            $material->width = (int) $meta->video->width;
            $material->height = (int) $meta->video->height;
        }

        return parent::render($material);
    }
}