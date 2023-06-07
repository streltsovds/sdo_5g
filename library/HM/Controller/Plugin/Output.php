<?php
class HM_Controller_Plugin_Output extends HM_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        $className = $dispatcher->loadClass($dispatcher->getControllerClass($request));
        $action = $this->getRequest()->getActionName() . 'Plainify';
        $action = str_replace('-', '', $action);

        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;

        $response = $this->getResponse();

        /*
         *  Подготовить плоские данные для Vue
         */
        if (method_exists($className, $action)) {
            $plainData = $className::$action($view->getVars(), $view);
            $view->clearVars();
            $view->assign($plainData);
        }

        /*
         *  Послать в JSON если это ajax-запрос
         */
        if ($this->isAjaxRequest($request)) {
            $vars = $view->getVars();

            if (isset($vars['gridAjaxRequest']) && $vars['gridAjaxRequest']) {
                $body = $response->getBody();
                if ($body) {
                    /**
                     * #33915
                     * не перезаписываем body, которое уже было сформировано в
                     * @see HM_Controller_Action_Trait_Ajax::postDispatchAjax()
                     */
                    return;
                }
            }

            $responseErrors = $response->getException();

            if (isset($responseErrors[0])) {
                $errorText = APPLICATION_ENV == 'development' ? $responseErrors[0] . "" : '';

                $this->sendAsJsonViaAjax([
                   'error' => $errorText,
                ]);

                return;
            }

            if (count($vars) > 1) {
                $this->sendAsJsonViaAjax($vars);
            } elseif (count($vars)) {
                $var = array_shift($vars);
                $this->sendAsJsonViaAjax($var);
            }
        }
    }


    /**
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

    public function isAjaxRequest($request)
    {
        return $request->isXmlHttpRequest() || $request->getParam('ajax', false);
    }
}
