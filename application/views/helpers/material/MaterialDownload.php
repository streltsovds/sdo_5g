<?php

class HM_View_Helper_MaterialDownload extends HM_View_Helper_MaterialAbstract
{
    public function materialDownload($material)
    {
        if ($material->external_viewer == HM_Resource_ResourceModel::EXTERNAL_VIEWER_GOOGLE) {

            $path = $this->view->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $material->resource_id, 'revision_id' => 0));
            $baseUrl = $this->getService('Option')->getOption('externalUrl');
            $this->view->externalViewerUrl = sprintf('http://docs.google.com/viewer?url=%s&embedded=true', urlencode($baseUrl . $path));
        }

        return parent::render($material);
    }
}