<?php
class HM_View_Sidebar_Search extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'search';
    }

    public function getTitle()
    {
        return _('Поиск');
    }

    public function getContent()
    {
        $options = $this->getOptions();

        $data = [
            'content' => $options['content'],
            'model' => $this->getModel(),
        ];

        return $this->view->partial('search.tpl', $data);
    }
}
