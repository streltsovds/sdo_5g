<?php

class HM_View_Helper_MainNavMenuActionWithTooltip extends HM_View_Helper_Abstract
{
    /**
     * @param HM_Navigation_Page_Mvc $page
     */
    public function mainNavMenuActionWithTooltip($page) {
        $this->view->iconName = $page->icon;
        $this->view->label = $page->getLabel();

        return $this->view->render('mainNavMenuActionWithTooltip.tpl');
    }

}
