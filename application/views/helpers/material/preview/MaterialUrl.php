<?php
/*
 * Объединенный helper для внешних URLов и внутренних HTML-сайтов
 */
class HM_View_Helper_MaterialUrl extends HM_View_Helper_MaterialAbstract
{
    public function materialUrl($material)
    {
        if ($material->type == HM_Resource_ResourceModel::TYPE_FILESET) {

            $path = explode('public',Zend_Registry::get('config')->path->upload->public_resource);
            $pathToFile = (isset($path[1])) ? $path[1] : '/upload/resources/';
            $pathToFile .=  $material->resource_id . '/' . $material->url;

//            $protocol   = ($this->_request->isSecure())? 'https' : 'http';
//            $host       = $this->_request->getHttpHost();

//            $material->url        = $protocol . '://' .$host . $pathToFile;
            $material->url        =  $pathToFile;

        } elseif (strpos($material->url, 'http') !== 0) {
            $material->url = 'http://' . $material->url;
        }

        return parent::render($material);
    }
}