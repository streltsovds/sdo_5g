<?php
class HM_View_Helper_ContextMenu extends HM_View_Helper_Navigation_Menu
{

    public function contextMenu(Zend_Navigation_Container $container = null, $useAcl = true)
    {
        $this->setUseAcl($useAcl);
        $this->setPartial('partials/contextmenu.tpl');
        $menu = $this->render($container);

        return $menu;
    }
}