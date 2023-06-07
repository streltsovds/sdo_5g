<?php
class HM_View_Helper_SearchItem extends HM_View_Helper_Abstract
{
    public function searchItem($item, $count, $highlights = array(), $unsetParams = array(), $checkbox = false, $disabledMsg = false)
    {
        if (empty($item)) return '';
        
        $description = $item->getDescription();

        if (count($highlights)) {
            foreach ($highlights as $word => $stats) {
                $description = str_replace($word, self::wrap($word), $description);
            }
        }
        $this->view->unsetParams = array();
        if (count($unsetParams)) {
            foreach ($unsetParams as $key) {
                $this->view->unsetParams[$key] = null;
            }
        }
        
        $this->view->item = $item;
        $this->view->count = $count;
        $this->view->description = $description;
        $this->view->checkbox = $checkbox;
        $this->view->disabledMsg = $disabledMsg;
        
        return $this->view->render('search-item.tpl');
    }

    static public function wrap($str)
    {
        return sprintf('<span class="highlight">%s</span>', $str);
    }
}