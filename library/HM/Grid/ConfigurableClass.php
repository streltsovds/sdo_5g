<?php

class HM_Grid_ConfigurableClass extends HM_Grid_AbstractClass
{
    protected $_options = array();

    protected static $_defaultOptions = array('ajax' => false);

    protected static function _getDefaultOptions()
    {
        return static::$_defaultOptions;
    }

    public static function getDefaultOptions($className)
    {
        $options = static::_getDefaultOptions();

        $parentOptions = array();

        if ($className !== __CLASS__) {
            $parentClassName = get_parent_class($className);
            $parentOptions = $parentClassName::getDefaultOptions($parentClassName);
        }

        return array_merge($parentOptions, $options);
    }

    protected function _initOptions($options)
    {
        $className = get_class($this);

        $defaultOptions = static::getDefaultOptions($className);

        $optionNames = array_keys($defaultOptions);

        foreach ($optionNames as $optionName) {
            if (isset($options[$optionName])) {
                $defaultOptions[$optionName] = $options[$optionName];
            }
        }

        $this->_options = $defaultOptions;

    }

    public function __construct($options = array())
    {
        $this->_initOptions($options);
    }

    public static function create($options = array())
    {
        return new static($options);
    }

    public function getOption($name)
    {
        if (!isset($this->_options[$name])) {
            return false;
        }

        return $this->_options[$name];
    }
}