<?php
/**
 * Created by PhpStorm.
 * User: dvukhzhilov
 * Date: 10-Oct-18
 * Time: 7:08 PM
 * Компонент для слайдера
 */
class HM_View_Helper_Swiper extends Zend_View_Helper_Abstract {

    private $_mode;
    private $_config;

    public function Swiper($mode = null, $config = null)
    {
        if (!is_null($mode)) $this->_mode = $mode;
        if (!is_null($config)) $this->_config = $config;
        return $this;
    }

    public function captureStart() {
        ob_start();
    }

    public function captureEnd() {
        $data = ob_get_clean();

        $start = "<hm-swiper";
        $markup = $start;

        $markup.=$this->getMode();

        $end = ">{$data}</hm-swiper>";

        echo $markup.$end;
    }

    private function getMode() {
        if (is_null($this->_mode)) return '';
        if ($this->_mode === 'scroll') return ' horizontal-scroll-mode';
    }
}