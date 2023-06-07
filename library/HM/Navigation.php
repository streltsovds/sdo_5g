<?php
class HM_Navigation extends Zend_Navigation
{
    const ACTIVITY_SOURCE_TYPE = 'activity';

    private $_substitutions = null;

    private $_activities = [];

    private $_acl = null;

    public function __construct($pages = null, $substitutions = null, $parentResource = null) // $substitutions[HM_Controller_Action_Activity::PARAM_CONTEXT_TYPE] - вирт.кабинет
    {
        /** @var HM_Acl _acl */
        $this->_acl = Zend_Registry::get('serviceContainer')->getService('Acl');

        parent::__construct($pages);
        $this->_substitutions = $substitutions;

// сервисы взаимодейстия отключены до выяснения их нужности
//        $this->_setActivities();

        if ($pages) {
            foreach ($pages as $page) {
                $this->_setAcl($page, $parentResource);
            }
        }

        if ($this->_pages) {
            foreach ($this->_pages as $page) {
                $page->isActive(true); // проставляем _active
            }
        }

        $this->_processSubstitutions();
    }

    public function hasVisiblePages()
    {
        foreach ($this->_pages as $page) {
            if(Zend_Registry::get('view')->getHelper('navigation')->accept($page)) {
                return true;
            }
        }
        return false;
    }

	/**
	 * @param Zend_Config $item
	 * @param $parentResource
	 */
	private function _setAcl($item, $parentResource = null)
    {
        if ('object' === getType($item)) {
            $resource = $item->resource ?: $this->_generateResource($item);
        } else {
            return false;
        }

        if (!empty($resource)) {

            $this->_acl->addResource(new Zend_Acl_Resource($resource), $parentResource);

            if (strpos($resource, HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX . ':') === 0) {

                // сервисы взаимодействия разрешаются не через allow/deny в конфиге
                $this->_setActivityAcl($item);

            } else {

                foreach (['allow', 'deny'] as $rule) {
                    if (isset($item->$rule)) {
                        foreach($item->$rule as $role) {
                            if ($role == HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL) $role = null;
                            $this->_acl->$rule($role, $resource);
                        }
                    }
                }
            }
        }

        foreach (['pages', 'actions', 'modes'] as $itemProp) {
            if (isset($item->$itemProp) && count($item->$itemProp)) {
                foreach($item->$itemProp as $subItem) {
                    $this->_setAcl($subItem, $resource);
                }
            }
        }
    }

    private function _processSubstitutions()
    {
        if (is_null($this->_substitutions)) return;
        $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        foreach($iterator as $page) {
            if ($page instanceof Zend_Navigation_Page_Mvc) {
                $this->_initNavigationPage($page);
            }
            if ($page instanceof Zend_Navigation_Page_Uri) {
                $this->_initUriPage($page);
            }
        }
    }

    private function _initNavigationPage(Zend_Navigation_Page_Mvc $page)
    {
        $params = $this->getMcaSubstitutions($page->getParams());
        $page->setParams($params);
    }

    private function _initUriPage(Zend_Navigation_Page_Uri $page)
    {
        $page->uri = $this->getUriSubstitutions($page->uri);
    }

    private function getMcaSubstitutions($originalParams)
    {
        $result = [];
        if (is_array($originalParams) or $originalParams instanceof Zend_Config) {
            foreach($originalParams as $key => $value) {


                if (isset($this->_substitutions[$key])) {
                    $result[$key] = strtolower($this->_substitutions[$key]);

                // TODO проверить; подстановка параметров по имени для actions в application/settings/navigation/main.php не работала иначе (#34298)
//                } elseif (false !== strpos($value, '%') and $keyParam) {
//                    $result[$key] = $keyParam;
                } elseif (false !== strpos($value, '%')) {
                    $requestParamName = trim($value, '%');
                    if (!$requestParamName) {
                        $requestParamName = $key;
                    }
                    $requestParamValue = Zend_Controller_Front::getInstance()->getRequest()->getParam($requestParamName, false);

                    if ($requestParamValue !== false) {
                        $result[$key] = $requestParamValue;
                    }
                } elseif(strlen($value) and false === strpos($value, '%')) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private function getUriSubstitutions($originalUri)
    {
        $result = $originalUri;

        if (is_array($this->_substitutions) && count($this->_substitutions)) {
            foreach($this->_substitutions as $key => $value) {
                $result = str_replace("%$key%", $value, $originalUri);
            }
        }

        if (strlen($result) && (false == strstr('http://', $result))) {
            $result = Zend_Registry::get('view')->baseUrl($result);
        }

        return $result;
    }

    private function _setActivityAcl($page)
    {
        $resourceArray = explode(':', $page->resource);
        $activity = end($resourceArray);
        $acl = Zend_Registry::get('serviceContainer')->getService('Acl');

        if (in_array((int) $activity, $this->_activities)) {
            $acl->allow(null, $page->resource);
        } else {
            $acl->deny(null, $page->resource);
        }
    }

    /*
     * В конфиг.файле меню перечислены все потенциально возможные активности
     * здесь вычисляются  разрешенные активности для данного контекста (в т.ч. глобального)
     * в _setActivityAcl() они сравниваются и разрешаются/запрещаются
     */

    private function _setActivities()
    {
        $activities = array();

        if (!empty($this->_substitutions[HM_Controller_Action_Activity::PARAM_CONTEXT_TYPE])) {

            // если это контекстное меню в HM_Controller_Action_Trait_Context

            $serviceName = $this->_substitutions[HM_Controller_Action_Activity::PARAM_CONTEXT_TYPE];
            $service = Zend_Registry::get('serviceContainer')->getService($serviceName);
            $subject = $service->findOne($this->_substitutions[HM_Controller_Action_Activity::PARAM_CONTEXT_ID]);

            if ($subject) {
                $names = HM_Activity_ActivityModel::getTabActivities();
                foreach($names as $activityId => $activityName) {
                    if (($subject->services & $activityId)) {
                        $activities[] = $activityId;
                    }
                }
            }
        } else {

            // если это главное меню

            $enabledActivities = unserialize(Zend_Registry::get('serviceContainer')->getService('Option')->getOption('activity'));

            $enabledActivities = $enabledActivities ? $enabledActivities : [];

            foreach ($enabledActivities as $activityId => $enabledActivity) {
                $activities[] = $activityId;
            }
        }

        $this->_activities = $activities;
    }

    protected function _generateResource($item)
    {
        if(isset($item->module) && !empty($item->module)) {
            $result = $item->application
                ? sprintf('%s:%s/%s:%s:%s', HM_Navigation_Page_Mvc::RESOURCE_PREFIX, $item->application, $item->module, $item->controller, $item->action)
                : sprintf('%s:%s:%s:%s', HM_Navigation_Page_Mvc::RESOURCE_PREFIX, $item->module, $item->controller, $item->action);


            $substitutedParams = $this->getMcaSubstitutions($item->params);

            if(is_array($substitutedParams)) {
                foreach ($substitutedParams as $paramName => $param) {
                    if($paramName == 'baseUrl') continue;
                    $result .= ":$paramName:$param";
                }
            }
//        } elseif (isset($item->uri) && !empty($item->uri)) {
        } else {

            $substitutedUri = $this->getUriSubstitutions($item->uri);
            $result = sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_PREFIX, $substitutedUri);
        }

        return $result;
    }

    public function findAndRemoveActionBy($property, $value)
    {
        $iterator = new RecursiveIteratorIterator($this,
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $page) {
            $actions = $page->get('actions');

            foreach ($actions as $key => $action) {
                if ($action[$property] == $value) {

                    unset($actions[$key]);
                    $page->set('actions', $actions);
                    break;
                }
            }
        }

        return null;
    }

}