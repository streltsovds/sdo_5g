<?php
class HM_View_Helper_FooterMenuData extends Zend_View_Helper_Navigation_Menu
{
//    public function footerMenu(Zend_Navigation_Container $container = null)
    public function footerMenuData(Zend_Navigation_Container $container = null) {
        $result = [];

        $iterator = new RecursiveIteratorIterator($container,
            RecursiveIteratorIterator::SELF_FIRST);
        $iterator->setMaxDepth(0);

        foreach ($iterator as $page) {
            if ($this->accept($page)) {
                if ($page->hasPages()) {
                    foreach($page->getPages() as $subPage) {
                        $result[] = [
                          'subpage' => true,
                          'href' => $subPage->getHref() . '/htmlpage_id/' . $subPage->get('htmlpage_id'),
                          'active' => $subPage->isActive(true),
                          'label' => $subPage->getLabel(),
                        ];
                    }
                } else {
                    $result[] = [
                        'href' => $page->getHref(),
                        'active' => $page->isActive(true),
                        'label' => $page->getLabel(),
                    ];
                }
            }
        }

        return $result;
    }
}