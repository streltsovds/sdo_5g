<?php
/*
 * Контейнер для групповых действий со списком; визуально похож на грид, но никакого отношения не имеет
 * Из контроллера нужно подключить grid.css и grid.js 
 * Список должен быть обёрнут в <div class="els-grid patched" id="grid">
 * Элементы списка иметь checkbox'ы как в SearchItem
 */
class HM_View_Helper_ListMassActions extends HM_View_Helper_Abstract
{
    public function listMassActions($options = array())
    {
        if (isset($options['pagination'])) {
            list($paginator, $scrollingStyle, $partial, $params) = $options['pagination'];
            $this->view->pagination = $this->view->paginationControl($paginator, $scrollingStyle, $partial, $params);
        }

        $this->view->action_title = (isset($options['action_title'])) ? $options['action_title'] : _('Для выбранных элементов');

        $this->view->actions = $options['actions'];
        $this->view->export = $options['export'];
        $this->view->customFormElements = $options['customFormElements'];

        return $this->view->render('list-mass-actions.tpl');
    }
}