<?php
class HM_View_Helper_ActionsData extends HM_View_Helper_Navigation_Menu
{
    public function actionsData()
    {
        $container = $this->view->getActionsNavigation();

        if(!empty($container)) {
            $iterator = new RecursiveIteratorIterator($container,
                RecursiveIteratorIterator::SELF_FIRST);
            //$iterator->setMaxDepth(0);
        } else {
            $iterator = [];
        }

        $actions = [];

        foreach ($iterator as $page) {
            if ($this->accept($page)) {
                $action = [];

                $action['href'] = $page->getHref();
                $action['label'] = $page->getLabel();

                if ($icon = $page->getIcon()) {
                    $action['icon'] = $icon;
                }

                $actions[] = $action;
            }
        }

        return $actions;
    }
}