<?php

require_once 'Zend/View/Helper/Abstract.php';

class HM_View_Infoblock_Abstract extends ZendX_JQuery_View_Helper_UiWidget implements HM_View_Cacheable_Interface
{
    public $disableCache = false;

    public function getCachedContent()
    {
        // кэшируем только enduser'ов
        $currentRole = $this->getService('User')->getCurrentUserRole();
        if (!$this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_GUEST))) return false;

        $cache = Zend_Registry::get('cache');
        $userId = $this->getService('User')->getCurrentUserId();

        $key = implode('_', array(
            'widget',
            get_class($this),
            $userId
        ));
        return $cache->load($key);

        return false;
    }

    public function getNotCachedContent()
    {
    }

    public function render($content)
    {
        $cache = Zend_Registry::get('cache');
        $userId = $this->getService('User')->getCurrentUserId();

        $key = implode('_', array(
            'widget',
            get_class($this),
            $userId
        ));
        $cache->save($content, $key, array(
            sprintf('widget_%s', get_class($this)),
            sprintf('user_%s', $userId),
        ));


        if (method_exists($this, 'getNotCachedContent')) {
            $this->getNotCachedContent();
        }

        return $content;
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;        
        $config = Zend_Registry::get('config');
        $this->view->addScriptPath($config->path->infoblocks->default . 'views/');
        parent::setView($view);
        return $this;
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    /**
     * @param  HM_Collection $collection
     * @return HM_Model_Abstract
     */
    public function getOne($collection)
    {
        if (count($collection)) {
            return $collection->current();
        }
        return false;
    }
}