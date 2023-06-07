<?php

class HM_Grid_Action extends HM_Grid_AbstractClass {

    protected $_url = '';
    protected $_params = array();
    protected $_code = '';
    protected $_title = '';

    /**
     * Список иконок, которые уже определены в HM_View_Helper_Icon
     *
     * @var array
     */
    protected $_actionIcons = array(
        'workflow',
        'print',
        'view',
        'add',
        'look',
        'delete',
        'cancel',
        'edit',
        'ok',
        'note2',
        'archive',
        'develop',
        'useradd',
        'usernotadd',
        'calendar',
        'close_cross',
        'type_1',
        'type_2',
        'type_3',
        'type_4',
        'type_5',
        'type_6',
        'type_7',
        'type_8',
        'type_9',
        'type_10',
        'type_11',
        'type_12',
        'type_13',
        'no_type',
        'card',
    );

    public function __construct($code, $url)
    {
        $this->_code = $code;

        if (in_array($code, $this->_actionIcons)) {
            $this->_title = $this->getView()->icon($code);
        } else {
            $this->_title = $code;
        }

        if (is_array($url)) {
            $url = $this->getView()->url($url);
        }

        $this->_url  = $url;

    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setParams($params)
    {
        $this->_params = $params;

        return $this;
    }

    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    protected function _getUrl($row)
    {
        $url = $this->_url;

        if (is_array($this->_params))  {
            foreach($this->_params as $colName => $name) {

                if (is_numeric($colName)) {
                    $colName = $name;
                }

                $url.= '/'.urlencode($name).'/'.urlencode(isset($row[$colName]) ? $row[$colName] : '') ;
            }
        }

        return $url;

    }

    public function toString($row)
    {
        $view = $this->getView();

        return '<a href ="'.$view->escape($this->_getUrl($row)).'">'.$this->_title.'</a>';
    }

}