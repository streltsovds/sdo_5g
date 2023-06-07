<?php
class HM_Navigation_Modifier_Remove_Action extends HM_Navigation_Modifier
{

    private $_property = null;
    private $_value = null;

    public function __construct($findByProperty, $findByValue)
    {
        $this->_property = $findByProperty;
        $this->_value = $findByValue;

        $this->_function = array($this, 'removeAction');
    }

    public function removeAction(HM_Navigation $container = null)
    {
        if (null !== $container) {
            $container->findAndRemoveActionBy($this->_property, $this->_value);
        }
    }
}