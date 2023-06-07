<?php

abstract class HM_Grid_ColumnCallback_AbstractListOfLinks extends HM_Grid_ColumnCallback_AbstractList
{
    /**
     * @param int $id
     * @param HM_Model_Abstract $item
     *
     * @return string
     */
    abstract protected function _getUrl($id, $item);

    protected function _renderItem($id, $item)
    {
        $url = $this->_getUrl($id, $item);

        return '<a href="'.$url.'">'.$this->_getTitle($item).'</a>';
    }

}