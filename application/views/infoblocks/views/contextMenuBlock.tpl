<?php 
    $iterator = new RecursiveIteratorIterator($this->menu,
    RecursiveIteratorIterator::SELF_FIRST);
    $iterator->setMaxDepth(1);
    foreach ($iterator as $page) {
        $properties = $page->toArray();
        if (isset($properties['application'])) {
            $page->_module = $properties['application'] . '/' . $page->_module;
        }
    }

    // generate context menu always without super-module
    $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
    Zend_Controller_Front::getInstance()->setBaseUrl('');
    //markup was broked and we must delete all tags exclude ul, a, li
    $menu = strip_tags($this->navigation()->extendedMenu()->renderMenu($this->menu, array('maxDepth' => 1)), '<ul><li><a>');
    Zend_Controller_Front::getInstance()->setBaseUrl($baseUrl);

    // allow super-module inside module (e.g. at/kpi)
    $menu = urldecode($menu);
    // Remove empty blocks
    $preg = preg_split('#<li>\s*</li>#iUm', $menu);

    echo implode("\n", $preg);
    /*//markup was broked and we must delete all tags exclude ul, a, li
    $menu = strip_tags($this->navigation()->extendedMenu()->renderMenu($this->menu, array('maxDepth' => 1)), '<ul><li><a>');
    // Remove empty blocks
    $preg = preg_split('#<li>\s*</li>#iUm', $menu);

    echo implode("\n", $preg);*/
