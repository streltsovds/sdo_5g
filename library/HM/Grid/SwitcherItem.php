<?php

class HM_Grid_SwitcherItem
{
    protected $_config = array(
        'name' => '',
        'title' => '',
        'params' => array(
            'all' => null
        ),
        'order' => null,
        'order_dir' => null
    );

    public function __construct($title, $value, $filterParams)
    {
        $config = &$this->_config;
        $configParams = &$config['params'];

        $config['title'] = $title;

        $configParams['all'] = $value;
        $configParams = array_merge($configParams, $filterParams);
    }

    public function setOrder($order, $orderDir = 'DESC')
    {
        $config = &$this->_config;

        $config['order']     = $order;
        $config['order_dir'] = $orderDir;

    }

    public function setClassName($className)
    {
        $this->_config['name'] = $className;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function getValue()
    {
        return $this->_config['params']['all'];
    }
}