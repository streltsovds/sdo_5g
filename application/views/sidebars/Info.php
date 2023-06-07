<?php
class HM_View_Sidebar_Info extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'info';
    }

    public function getContent()
    {
        $options = $this->getOptions();
        return $this->view->partial('info.tpl', ['content' => $options['content']]);
    }
}