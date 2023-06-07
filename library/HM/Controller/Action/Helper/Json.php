<?php

class HM_Controller_Action_Helper_Json extends Zend_Controller_Action_Helper_Json
{
    /**
     * true => используется
     * @see Zend_Json
     *
     * false => используется
     * @see HM_Json
     *
     * Перевести в true, если будут проблемы
     */
    public static $fallbackToZendJson = false;

    public function sendJson($data, $keepLayouts = false)
    {
        if (self::$fallbackToZendJson) {
            $data = $this->encodeJson($data, $keepLayouts);
        } else {
            /**
             * Вместо переопределения файлов классов нужные функции скопированы в этот класс и изменены (см. ниже)
             */
             $data = $this->_encodeJson($data, $keepLayouts);
        }
        $response = $this->getResponse();

        $response->setHeader('Content-Type', 'application/json');

        /**
         * TODO неправильно расчитывается
         *   - из-за этого браузер не догружает контент ajax-запросов в редакторе виджетов.
         *   Поправить расчёт, проверить на сериализации html-кода с нестандартнаыми символами
         */
        //$response->setHeader('Content-Length', mb_strlen($data, '8bit'));

        $response->setBody($data);

        if (!$this->suppressExit) {
            $response->sendResponse();
            exit;
        }

        return $data;
    }

    /**
     * Модификация
     * @see Zend_Controller_Action_Helper_Json::encodeJson()
     */
    public function _encodeJson($data, $keepLayouts = false)
    {
        /**
         * @see Zend_View_Helper_Json
         */
//        require_once 'Zend/View/Helper/Json.php';
//        $jsonHelper = new Zend_View_Helper_Json();
//        $data = $jsonHelper->json($data, $keepLayouts);

        $data = $this->_json($data, $keepLayouts);

        if (!$keepLayouts) {
            /**
             * @see Zend_Controller_Action_HelperBroker
             */
            require_once 'Zend/Controller/Action/HelperBroker.php';
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        }

        return $data;
    }

    /**
     * Модификация
     * @see Zend_View_Helper_Json::json()
     */
    public function _json($data, $keepLayouts = false)
    {
//        $options = array();
//        if (is_array($keepLayouts))
//        {
//            $options     = $keepLayouts;
//            $keepLayouts = (array_key_exists('keepLayouts', $keepLayouts))
//                ? $keepLayouts['keepLayouts']
//                : false;
//            unset($options['keepLayouts']);
//        }
//
//        $data = HM_Json::encodeErrorSkip($data, null, $options);

        $data = HM_Json::encodeErrorThrow($data);

        if (!$keepLayouts) {
            require_once 'Zend/Layout.php';
            $layout = Zend_Layout::getMvcInstance();
            if ($layout instanceof Zend_Layout) {
                $layout->disableLayout();
            }
        }

        $response = Zend_Controller_Front::getInstance()->getResponse();
        $response->setHeader('Content-Type', 'application/json');
        return $data;
    }


}
