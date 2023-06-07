<?php

class HM_Grid_ListColumn extends HM_Grid_SimpleColumn
{
    const COLUMN_CALLBACK_CLASS_NAME = '';

    protected static function _getDefaultOptions()
    {
        return array(
            'order' => false,
        );
    }

    protected $_columnCallBack = null;

    public function __construct($options = array())
    {
        parent::__construct($options);

        $callBack = static::COLUMN_CALLBACK_CLASS_NAME;

        $this->_columnCallBack = new $callBack($this->getGrid(), $this->getFieldName());
    }

    public function getCallBack()
    {
        return $this->_columnCallBack->getCallback('{{'.$this->getFieldName().'}}');
    }

    public function getFilter()
    {
        return array(
            'callback' => array(
                'function' => array($this->_columnCallBack, 'filterCallback')
            )
        );
    }

}