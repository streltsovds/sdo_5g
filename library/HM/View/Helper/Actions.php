<?php
class HM_View_Helper_Actions extends HM_View_Helper_Navigation_Menu
{
    public function actions()
    {
        $actions = $this->view->actionsData();

        return
            "<hm-partials-actions
                :actions='" . json_encode($actions, JSON_PRETTY_PRINT | JSON_HEX_APOS) . "'
            >
            </hm-partials-actions>";
    }
}