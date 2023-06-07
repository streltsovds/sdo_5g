<?php
class HM_View_Helper_MainMenu extends HM_View_Helper_Navigation_Menu
{

    public function mainMenu(Zend_Navigation_Container $container = null)
    {
        $this->setPartial('partials/mainmenu.tpl');
        $menu = $this->render($container);

        return $menu;
    }
}