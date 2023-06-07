<?php
class HM_View_Helper_Sidebars extends HM_View_Helper_Abstract
{
    public function sidebars()
    {
        return $this;
    }

    // Throwing exceptions from __toString() in php < 7.4 is currently forbidden and will result in a fatal error.
//    public function __toString()
    public function render()
    {
//        try {
            return $this->view->render('sidebars.tpl');
//        } catch (Exception $e) {
//            if (APPLICATION_ENV == 'development') {
//                return $e->getMessage();
//            }
//            return '';
//        }
    }

    public function togglers()
    {
        $return = array();
        foreach ($this->view->getSidebars() as $name => $sidebar) {
            $return[] = $sidebar->getToggle();
        }
        return implode(array_reverse($return));
    }

    /**
     * Получить все названия сайдбаров
     *
     * Нужно для работы переключения сайдбаров
     *
     * @return string[] массив названий классов
     */
    public function getSwitchProperties()
    {
        $return = array();
        foreach ($this->view->getSidebars() as $name => $sidebar) {
            $return[] = $sidebar->getName();
        }
        return $return;
    }
}