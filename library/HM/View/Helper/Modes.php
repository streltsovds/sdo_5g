<?php
class HM_View_Helper_Modes extends HM_View_Helper_Navigation_Menu
{
    public function modes($container, $disabledModes = array())
    {
        if (!$container || !count($container)) return;

        // актуально когда вьюха с гридом
        if (Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest()) return;

        $this->view->navigation()->findActive($container);

        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);
        $buttons = [];
        foreach($iterator as $page) {
            $params = $page->getParams();
            $switcher = ($params['switcher'] && (false === strpos($params['switcher'], 'program'))) ? $params['switcher'] : 'programm';
            if (!$this->accept($page) or
                in_array($switcher, $disabledModes)
            ) {
                continue;
            }
            $buttons[] = [
                'icon' => $page->getIcon(),
                'isActive' => $page->isActive(),
                'label' => $page->getLabel(),
                'href' => $page->getHref()
            ];
        }

        // если 1 режим, не выводить переключатель
        if (count($buttons) <= 1) {
            return '';
        }

        $this->view->buttonsJson = HM_Json::encodeErrorThrow($buttons);

        return $this->view->render('partials/modes.tpl');
    }
}