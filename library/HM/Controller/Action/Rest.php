<?php

class HM_Controller_Action_Rest extends Zend_Rest_Controller
{

    const EVENT_GET_REQUEST_PRE = "restGetRequest.pre";
    const EVENT_UPDATE_REQUEST_PRE = "restUpdateRequest.pre";
    const EVENT_INIT_REQUEST = "restRequestInit";

    /**
     * @var HM_Acl
     */
    protected $_acl;

    /**
     * @var HM_Controller_Request_Http
     */
    protected $_request;

    /**
     * MUST BE OVERRIDEN
     *
     * @var HM_Service_Rest_Interface _defaultService
     */
    protected $_defaultService = null;
    protected $_orderFieldName = 'name';

    /**
     * @var HM_Collection
     */
    protected $_collection = null;

    /**
     * @var HM_Model_Abstract
     */
    protected $_item = null;

    /**
     *
     * @var sfEventDispatcher
     */
    protected $eventDispatcher = null;

    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        /* @var $eventDispatcher sfEventDispatcher */
        $eventDispatcher = Zend_Registry::get('serviceContainer')->getService('EventDispatcher');
        $eventDispatcher->connect(self::EVENT_GET_REQUEST_PRE, $this->getRequestInitializer());
        $eventDispatcher->connect(self::EVENT_INIT_REQUEST, $this->pluginRegistrator());
        $eventDispatcher->connect(self::EVENT_INIT_REQUEST, $this->contextSwitcher());
        $eventDispatcher->connect(self::EVENT_UPDATE_REQUEST_PRE, $this->updateRequestInitializer());
        $this->setEventDispatcher($eventDispatcher);

        parent::__construct($request, $response, $invokeArgs);
    }

    public function init()
    {
        $this->_acl = $this->getService('Acl');
        $params = array('helper' => $this->_helper);

        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_INIT_REQUEST, $params));
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_GET_REQUEST_PRE));
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_UPDATE_REQUEST_PRE));
    }

    protected function getRequestInitializer()
    {
        return function ($ev) {
            /**
             * @var sfEvent $ev
             * @var HM_Controller_Action_Rest $controller
             * @var Zend_Controller_Request_Http $request
             */
            $controller = $ev->getSubject();
            $request = $controller->getRequest();

            if ($request->isGet()) {
                $params = $controller->_getAllParams();

                if (!isset($params['id'])) {
                    $path = explode('/', $request->getPathInfo());

                    if (is_numeric($path[2])) {
                        $this->_routeWithoutMainAction($path, $controller);
                    } else {
                        $this->_routeWithAction($path, $controller);
                    }

                }

            }
        };
    }

    protected function updateRequestInitializer()
    {
        /** @var sfEvent $ev */
        return function ($ev) {
            /** @var Zend_Rest_Controller $controller */
            $controller = $ev->getSubject();

            /** @var Zend_Controller_Request_Http $request */
            $request = $controller->getRequest();

            if ($request->isPost() || $request->isPut() || $request->isDelete()) {
                $contentType = $request->getHeader('Content-Type');

                $controller->_setParam('controller', 'index');
                $request->setControllerName('index');

                $controller->_setParam('action', $request->getServer('REQUEST_METHOD'));
                $request->setActionName($request->getServer('REQUEST_METHOD'));

                if (!isset($params['id'])) {
                    $path = explode('/', $request->getPathInfo());

                    self::_routeWithoutMainAction($path, $controller);
                }

                if (strlen($contentType) && (false !== strstr($contentType, 'json'))) {
                    $body = $request->getRawBody();
                    if (strlen($body)) {
                        if ($values = Zend_Json::decode($body)) {
                            if (is_array($values) && count($values)) {
                                $request->setPost($values);
                            }
                        }
                    }
                }
            }
        };
    }

    protected function contextSwitcher()
    {
        return function ($ev) {
            /** @var $ev sfEvent */
            $params = $ev->getParameters();
            $helper = $params['helper'];

            $helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext($this->getRequest()->getActionName(), 'json')->initContext('json');
        };
    }

    protected function pluginRegistrator()
    {
        return function ($ev) {
            Zend_Controller_Front::getInstance()->registerPlugin(
                new Zend_Controller_Plugin_ErrorHandler(
                    array(
                        'module' => 'default',
                        'controller' => 'error',
                        'action' => 'json'
                    )
                )
            );
        };
    }

    /**
     * Роутинг по шаблону /{object}/{object_id}/{additional-action}/{action-id}
     * с количеством переменных от 1 до 4
     * например:
     * 1: /subjects
     * 2: /user/4,
     * 3: /user/19/lesson-assignments
     * 4: /subject/5/apply/12,
     *  Основной экшн определяется по запросу (get/post/delete/put)
     *
     * @param array $path
     * @param HM_Controller_Action_Rest $controller
     */
    public function _routeWithoutMainAction(array $path, HM_Controller_Action_Rest $controller)
    {
        $request = $controller->getRequest();

        if (isset($path[1]) && is_string($path[1])) {
            $controller->_setParam('module', $path[1]);
            $request->setModuleName($path[1]);
        }

        if (isset($path[2]) && is_string($path[2])) {
            $controller->_setParam('id', $path[2]);
        }

        if (isset($path[3]) && is_string($path[3])) {
            $controller->_setParam('action', $path[3]);
            $request->setActionName($path[3]);
        }

        if (isset($path[4]) && is_string($path[4])) {
            $controller->_setParam('action_id', $path[4]);
        }

        $params = $controller->_getAllParams();
        if (!isset($params['id'])) {
            $controller->_setParam('action', 'index');
            $request->setActionName('index');
        }
    }

    /**
     * Роутинг по шаблону /{object}/{action}/{field}/{field-value}
     * с количеством переменных от 2 до 4
     * например:
     * 2: [дополняется]
     * 3: [дополняется]
     * 4: /resources/findByKeyword/keyword/мяу
     *
     *  Основной экшн определяется по запросу (get/post/delete/put)
     *
     * @param array $path
     * @param HM_Controller_Action_Rest $controller
     */
    public function _routeWithAction(array $path, HM_Controller_Action_Rest $controller)
    {
        $request = $controller->getRequest();

        $controller->_setParam('controller', 'index');
        $request->setControllerName('index');

        if (isset($path[1]) && is_string($path[1])) {
            $controller->_setParam('module', $path[1]);
            $request->setModuleName($path[1]);
        }

        if (isset($path[2]) && is_string($path[2])) {
            $classMethods = get_class_methods($controller);

            if (in_array($path[2], $classMethods)) {
                $controller->_setParam('action', $path[2]);
                $request->setActionName($path[2]);
            } else {
                $this->setStatusInvalidInput();
            }
        }

        if (isset($path[3]) && is_string($path[3])) {
            $controller->_setParam('param', $path[3]);
        }

        if (isset($path[4]) && is_string($path[4])) {
            $controller->_setParam('value', $path[4]);
        }
    }

    protected function pluginUnregistrator()
    {
        return function ($ev) {
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        };
    }

    public function indexAction()
    {
        return $this->getAction();
    }

    public function getAction()
    {
        $id = $this->_getParam('id');

        if(!is_numeric($id)){
            $this->setStatusBadRequest();
            return false;
        }

        if ($id > 0 && $this->canGet()) {

            $this->_item = $this->getOne($this->_defaultService->find($id));

            if ($this->_item && is_callable([$this->_item, 'getRestDefinition'])) {
                $this->view->assign($this->_item->getRestDefinition());
            } else {
                $this->setStatusNotFound();
            }
        } else {
            $this->setStatusInvalidInput();
        }
    }

    public function postAction()
    {
        $json = $this->getRequest()->getRawBody();
        try {

            /** @var HM_Rest_Interface $model */
            if (is_callable([$this->_defaultService, 'getDataFromRest']) && $this->canCreate()) {
                $convertedData = $this->_defaultService->getDataFromRest(json_decode($json, true));
                $model = $this->_defaultService->insert($convertedData);

                if ($model) {
                    $this->view->assign($model->getRestDefinition());
                    $this->setStatusSuccessfulCreated();
                }
            } else {
                $this->setStatusInvalidInput();
            }
        } catch (Exception $e) {
            $this->setStatusInvalidInput();
        }
    }

    public function putAction()
    {
        $id = $this->_getParam('id');
        $json = $this->getRequest()->getRawBody();

        try {

            if(!is_numeric($id)){
                $this->setStatusInvalidInput();
                return false;
            }

            if ($id > 0 && $this->canUpdate()) {

                $decodedData = json_decode($json, true);
                $decodedData['id'] = $id;

                $convertedData = $this->_defaultService->getDataFromRest($decodedData);

                /** @var HM_Rest_Interface $model */
                $model = $this->_defaultService->update($convertedData);

                if ($model) {
                    $this->view->assign($model->getRestDefinition());
                }

            } else {
                $this->setStatusInvalidInput();
            }
        } catch (Exception $e) {
            $this->setStatusInvalidInput();
        }
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id', 0);

        if (is_numeric($id) && (int)$id > 0) {

            if ($this->canDelete()) {
                $result = $this->_defaultService->delete((int)$id);
            }

            // Ничего не передаём, вернётся Status 200 OK
            // Или сделать setStatusOK?

            if (!$result) {
                $this->setStatusNotFound();
            }
        } else {
            $this->setStatusInvalidInput();
        }
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return $this->_helper->ServiceContainer($name);
    }

    protected function setDefaultService($service)
    {
        $this->_defaultService = $service;
    }

    public function getOne(HM_Collection $collection)
    {
        if (($collection instanceof HM_Collection_Abstract) && count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function quoteInto($where, $args)
    {
        return $this->getService('User')->quoteInto($where, $args);
    }

    public function postDispatch()
    {
        if ($this->getRequest()->isXmlHttpRequest()
            || $this->_getParam('ajax', false)) {
            $headers = $this->getResponse()->getHeaders();
            $hasHeader = false;
            foreach ($headers as $key => $header) {
                if ('content-type' == strtolower($header['name'])) {
                    $hasHeader = true;
                    break;
                }
            }

            if (!$hasHeader) {
                $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset, true);
            }
        }
    }

    /**
     *
     * @return sfEventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     *
     * @param sfEventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(sfEventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Получить все поля из закодированного в JSON запроса по AJAX
     *
     * Используется когда с фронтенда передаются данные, закодированные
     * в JSON в теле запроса.
     * Обязательно при запросе в заголовках должен быть
     * "X_REQUESTED_WITH" "XMLHttpRequest"
     *
     * @return array|StdClass|string|null массив данных, переданных в запросе либо null
     */
    public function getJsonParams()
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        /** @var boolean $isXmlHttpRequest */
        $isXmlHttpRequest = $request->isXmlHttpRequest();

        if (!$request || !$isXmlHttpRequest) {
            return null;
        }

        $body = !empty($request->getRawBody()) ? $request->getRawBody() : '{}';

        try {
            /** @var array|StdClass|string $decodedData */
            $decodedData = Zend_Json::decode($body);
        } catch (Zend_Json_Exception $e) {
            /** @var string $decodedData */
            $decodedData = $e;

        }

        return $decodedData;
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


    /**
     * Уточнение, что именно не найдено, можно сделать в соответствующем контроллере
     *
     * @return string
     */
    public function getNotFoundMessage()
    {
        return 'Object not found';
    }

    /**
     * @return string
     */
    public function getBadRequestMessage()
    {
        return 'Invalid ID supplied';
    }

    /**
     * @return string
     */
    public function getBadInputMessage()
    {
        return 'Invalid input';
    }

    /**
     * TODO: Реализовать корректные проверки в контроллерах / через ACL / как-то ещё
     *
     * @return bool
     */
    public function canGet()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canDelete()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canCreate()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canUpdate()
    {
        return true;
    }

    public function setStatusSuccessfulCreated()
    {
        $this->getResponse()->setHttpResponseCode(201);
    }

    public function setStatusBadRequest()
    {
        $this->_disableJsonResponse();
        $this->getResponse()->setHttpResponseCode(400);
    }

    public function setStatusNotFound()
    {
        $this->_disableJsonResponse();
        $this->getResponse()->setHttpResponseCode(404);
    }

    public function setStatusInvalidInput()
    {
        $this->_disableJsonResponse();
        $this->getResponse()->setHttpResponseCode(405);
    }

    private function _disableJsonResponse(): void
    {
        $this->_helper->ContextSwitch()->setAutoJsonSerialization(false)->removeActionContext($this->getRequest()->getActionName(), 'json');
    }
}