<?php
class HM_Navigation_Page_Mvc extends Zend_Navigation_Page_Mvc
{
    const RESOURCE_PREFIX = 'nav';
    const RESOURCE_ACTIVITY_PREFIX = 'act';

    protected $icon;

    public function isActive($recursive = false)
    {
        // ???
        if (isset($this->_params['baseUrl']) && empty($this->_params['baseUrl'])) unset($this->_params['baseUrl']);

        $module = $this->getModule();
        $parts = explode('/', $module);
        if (count($parts)) {
            $this->setModule(array_pop($parts));
        }

        $return = parent::isActive($recursive);

        if (!$return && isset($this->_properties['modes'])) {
            foreach ($this->_properties['modes'] as $aliasParams) {
                $return = $return || self::isActiveAlias($aliasParams);
            }
        }

        if (!$return && isset($this->_properties['aliases'])) {
            foreach ($this->_properties['aliases'] as $aliasParams) {
                $return = $return || self::isActiveAlias($aliasParams);
            }
        }

        $front = Zend_Controller_Front::getInstance();
        $reqParams = $front->getRequest()->getParams();

        foreach ($this->_params as $paramKey => $param) {
            if (!isset($reqParams[$paramKey]) or
                (false === strpos($param, '%') and (string) $reqParams[$paramKey] !== (string) $param)
            ) {
                    $return = false;
            }
        }

        // вернуть на место application
        $this->setModule($module);
        return $return;
    }

    public function isHiddenInMenu()
    {
        return !empty($this->_properties['hidden']);
    }

    public function getResource()
    {
        if (isset($this->_resource)) {
            return $this->_resource;
        } else {
            $resource = $this->application
                ? sprintf('%s:%s/%s:%s:%s', self::RESOURCE_PREFIX, $this->application, $this->module, $this->controller, $this->action)
                : sprintf('%s:%s:%s:%s', self::RESOURCE_PREFIX, $this->module, $this->controller, $this->action);

            if(is_array($this->getParams())) {
                foreach ($this->getParams() as $paramName => $param) {
                    $resource .= ":$paramName:$param";
                }
            }

            return $resource;
        }
    }

    public function getHref()
    {
        if ($this->_hrefCache) {
            return $this->_hrefCache;
        }

        $params = $this->getParams();

        $params['baseUrl'] = '';
        if ($param = $this->getModule()) {
            $params['module'] = $param;
        }

        if ($param = $this->getController()) {
            $params['controller'] = $param;
        }

        if ($param = $this->getAction()) {
            $params['action'] = $param;
        }



        $url = Zend_Registry::get('view')->url($params,
            $this->getRoute(),
            $this->getResetParams());

        // чтоб работал 'application'
        $url = urldecode($url);

        return $this->_hrefCache = $url;
    }

    public function getLabelShort()
    {
        return (isset($this->_properties['label_short']) && $this->_properties['label_short']) ?  $this->_properties['label_short'] : false;
    }

    public function setIcon($icon)
    {
        return $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function isActiveAlias($aliasData = [])
    {
        $result = $this->_active;

        if (!$result) {
            $front = Zend_Controller_Front::getInstance();

            $mca = array_flip(['module', 'controller', 'action']);
            $reqParams = array_intersect_key($front->getRequest()->getParams(), $mca);
            $aliasData = array_intersect_key($aliasData, $mca);

            if (count(array_intersect_assoc($reqParams, $aliasData)) ==
                count($aliasData)) {
                $result = true;
            }

            $front = Zend_Controller_Front::getInstance();
            $reqParams = $front->getRequest()->getParams();
            if (isset($aliasData['params'])) {
                $params = array_filter((array) $aliasData['params'], 'strlen');

                foreach ($params as $paramKey => $param) {
                    if (!isset($reqParams[$paramKey]) or
                        (false === strpos($param, '%') and (string) $reqParams[$paramKey] !== (string) $param)
                    ) {
                        $result = false;
                    }
                }
            }
        }

        $this->_active = $result;
        return $result;
    }

    public function hasPages()
    {
        $pages = array_filter($this->_pages, function($page){
            return !$page->hidden;
        });
        return count($pages);
    }

    /**
     * Проверяет на основе параметров URL, что это форум курса (для гл. меню)
     * @return bool
     */
    public function isSubjectForum()
    {
        $front = Zend_Controller_Front::getInstance();
        $params = $front->getRequest()->getParams();
        if (array_key_exists('forum_id', $params) && array_key_exists('subject_id', $params)) {
            return true;
        }
        return false;
    }
}