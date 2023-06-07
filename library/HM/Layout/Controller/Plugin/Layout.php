<?php

/**
 * Используется для показа ошибок во View Helper'ах
 *
 * Используется в
 * @see HM_Resource_View::init()
 */
class HM_Layout_Controller_Plugin_Layout extends Zend_Layout_Controller_Plugin_Layout {

    /**
     * postDispatch() plugin hook -- render layout
     *
     * скопировано с
     * @see Zend_Layout_Controller_Plugin_Layout::postDispatch(),
     * изменён конец
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = $this->getLayout();
        $helper = $this->getLayoutActionHelper();

        // Return early if forward detected
        if (!$request->isDispatched()
            || $this->getResponse()->isRedirect()
            || ($layout->getMvcSuccessfulActionOnly()
                && (!empty($helper) && !$helper->isActionControllerSuccessful())))
        {
            return;
        }

        // Return early if layout has been disabled
        if (!$layout->isEnabled()) {
            return;
        }

        $response   = $this->getResponse();
        $content    = $response->getBody(true);
        $contentKey = $layout->getContentKey();

        if (isset($content['default'])) {
            $content[$contentKey] = $content['default'];
        }
        if ('default' != $contentKey) {
            unset($content['default']);
        }

        $layout->assign($content);

        $fullContent = null;
        $obStartLevel = ob_get_level();
        try {
            $fullContent = $layout->render();
            $response->setBody($fullContent);
        } catch (Exception $e) {
            while (ob_get_level() > $obStartLevel) {
                $fullContent .= ob_get_clean();
            }
            $request->setParam('layoutFullContent', $fullContent);
            $request->setParam('layoutContent', $layout->content);
            $response->setBody(null);

            /**
             * До этой точки всё как в
             * @see Zend_Layout_Controller_Plugin_Layout::postDispatch()
             *
             * Показ ошибок во View Helper
             * Vue-шаблон не может быть отрендерен из-за ошибки в его подготовке, поэтому замещаем body
             */

            $traceMod = $e->getTrace();

            foreach ($traceMod as &$level) {
                unset($level['args']);
            }

            $errorText = 'Error' . $e->getMessage() . ' at ' . var_export($traceMod, 1);

            index_php_log('Error catched at ' . __METHOD__ . '() ' . $errorText);

            if (APPLICATION_ENV == 'development') {

                $errorDescriptionHtml = '<h3>Error catched at ' . __METHOD__ . '():</h3>';
                if (isset($e->xdebug_message)) {
                    $errorDescriptionHtml .= $e->xdebug_message;
                } else {
                    $errorDescriptionHtml .= '<pre>' . $errorText . '</pre>';
                }

                $response->setBody($errorDescriptionHtml);
            }

            throw $e;
        }

    }
}
