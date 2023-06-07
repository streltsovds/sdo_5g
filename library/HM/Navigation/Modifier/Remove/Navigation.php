<?php
class HM_Navigation_Modifier_Remove_Navigation extends HM_Navigation_Modifier
{

    public function __construct()
    {
        $this->_function = array($this, 'removeNavigation');
    }

    public function removeNavigation(HM_Navigation $container = null)
    {
        $container->removePages();
    }
}