<?php
trait HM_Controller_Action_Trait_Ajax
{
    public function initAjax()
    {
        $this->_helper->layout()->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        return $this;
    }

    public function postDispatchAjax()
    {
        if (isset($this->dataGrid) && $this->dataGrid)     {
            $output = $this->dataGrid->buildGrid();

            /**
             * раньше body перезаписывалось в
             * @see HM_Controller_Plugin_Output::postDispatch(),
             * теперь там добавлена проверка на `$view->gridAjaxRequest`
             */
//            echo $output;
//            die();
        }
        if (isset($this->view->grid) && $this->view->grid) {
            $output = $this->view->grid->deploy();
        }

        if (isset($output)) {
            echo $output;
        }

        // не уверен, что это нужно, но на всякий случай
        $headers = $this->getResponse()->getHeaders();
        $hasHeader = false;
        foreach ($headers as $key => $header) {
            if ('content-type' == strtolower($header['name'])) {
                $hasHeader = true;
                break;
            }
        }

        if (!$hasHeader) {
            $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset, true);
        }
    }

    /**
     * TODO Было удалено, но вернул, т. к. происходила ошибка на форме логина /Komarov
     *
     * Послать данные через ajax в JSON
     *
     * Кодирует массив данных в JSON и отправляет в теле ответа
     * со всеми нужными заголовками и прекращает выполнение скрипта
     *
     * @param array|StdClass|string $data массив данных для передачи
     * @return void
     */
    public function sendAsJsonViaAjax($data = null)
    {
        /** @var HM_Controller_Action_Helper_Json $jsonHelper */
        $jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Json');
        $jsonHelper->sendJson($data);
    }
}
