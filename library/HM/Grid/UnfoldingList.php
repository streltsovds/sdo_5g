<?php

class HM_Grid_UnfoldingList extends HM_Grid_ConfigurableClass
{
    protected $_title = '';
    protected $_items = array();

    protected static $_defaultOptions = array(
        'title' => '',
        'items' => array(),
        'escapeItems' => true,
        'escapeTitle' => true,
        'width' => 'auto',
        'emptyText' => ''
    );

    public function __construct($options = array())
    {
        parent::__construct($options);

        $items = $this->getOption('items');

        if ($this->getOption('escapeItems')) {
            foreach ($items as &$item) {
                $item = $this->_escape($item);
            }
        }

        $this->_items = $items;

        $title = $this->getOption('title');

        if ($this->getOption('escapeTitle')) {
            $this->_title = $this->_escape($title);
        } else {
            $this->_title = $title;
        }

    }

    protected function _escape($text)
    {
        return Zend_Registry::get('view')->escape($text);
    }

    public function __toString()
    {
        if (empty($this->_items)) {
            return $this->getOption('emptyText');
        }

        $result = array();

        if ($this->_title) {
            $result[] = '<p class="total" style="width: '.$this->getOption('width').';">'.$this->_title.'</p>';
        }

        foreach($this->_items as $item){
            $result[] = '<p>'.$item.'</p>';
        }

        return implode('', $result);

    }

}