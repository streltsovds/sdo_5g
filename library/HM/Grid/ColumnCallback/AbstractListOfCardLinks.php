<?php

abstract class HM_Grid_ColumnCallback_AbstractListOfCardLinks extends HM_Grid_ColumnCallback_AbstractList
{
    protected $_cardLinkCallback = null;

    /**
     * @return HM_Grid_ColumnCallback_Abstract
     */
    protected function _getCardLinkCallback()
    {
        if ($this->_cardLinkCallback === null) {
            $this->_cardLinkCallback = $this->_createCardLinkCallback();
        }

        return $this->_cardLinkCallback;

    }

    protected function _getTitle($item)
    {
        return $item->getValue($this->_titleFieldName);
    }

    protected function _renderItem($id, $item)
    {
        $cardLink = $this->_getCardLinkCallback();

        return $cardLink($id, $this->_getTitle($item));
    }

    abstract protected function _createCardLinkCallback();

}