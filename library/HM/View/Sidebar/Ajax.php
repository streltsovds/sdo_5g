<?php
abstract class HM_View_Sidebar_Ajax extends HM_View_Sidebar_Abstract
{
    abstract public function getAjaxUrl();

    public function getContent()
    {
        $ajaxUrl = $this->getAjaxUrl();
        $sidebarName = $this->getName();

        $js = <<<JS
            $(function(){
                $("#sidebar-{$sidebarName}").load("{$ajaxUrl}");
            });
JS;
        $this->view->inlineScript()->appendScript($js);
        return '';
    }
}