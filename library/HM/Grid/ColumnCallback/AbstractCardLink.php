<?php

class HM_Grid_ColumnCallback_AbstractCardLink extends HM_Grid_ColumnCallback_Abstract
{
    public function __construct($hmGrid = null)
    {
        parent::__construct($hmGrid);

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        $this->_checkRights($acl);
    }

    protected $_viewLinkAllowed = true;
    protected $_cardLinkAllowed = true;

    protected function _checkRights(HM_Acl $acl)
    {

    }

    protected function _getViewUrl($id)
    {
        return '/';
    }

    protected function _getCardUrl($id)
    {
        return $this->_getViewUrl($id);
    }

    protected function _getCardTitle()
    {
        return null;
    }

    public function __invoke($id, $name)
    {
        if (!$id) {
            return '';
        }

        $view = $this->getView();

        $url = '';

        if ($this->_viewLinkAllowed) {
            $url = $this->_getViewUrl($id);
        }

        $cardTitle = $this->_getCardTitle();
        $title     = $this->_escape($name);

        $a = $url ? '<a href="'.$url.'">'.$title.'</a>' : $title;

        if (!$this->_cardLinkAllowed) {
            return $a;
        }

        $cardUrl = $this->_getCardUrl($id);

        return $view->cardLink($cardUrl, $cardTitle).$a;

    }

}