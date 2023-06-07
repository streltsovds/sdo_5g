<?php
class HM_Navigation_Page_Uri extends Zend_Navigation_Page_Uri
{
    const RESOURCE_PREFIX = 'nav';

    protected $icon;

    public function isActive($recursive = false)
    {
        return parent::isActive($recursive);
    }

    public function isHiddenInMenu()
    {
        return !empty($this->_properties['hidden']);
    }

    public function getHref()
    {
        return $this->getUri();
    }

    public function getLabelShort()
    {
        return $this->_properties['label_short'] ? : false;
    }

    public function getResource()
    {
        return sprintf(
            '%s:%s',
            HM_Navigation_Page_Mvc::RESOURCE_PREFIX,
            $this->getUri()
        );
    }

    public function setIcon($icon)
    {
        return $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function isActiveAlias($aliasParams = [])
    {
        if (!$this->_active) {
            $front = Zend_Controller_Front::getInstance();

            $mca = array_flip(['module', 'controller', 'action']);
            $reqParams = array_intersect_key($front->getRequest()->getParams(), $mca);
            $aliasParams = array_intersect_key($aliasParams, $mca);

            if (count(array_intersect_assoc($reqParams, $aliasParams)) ==
                count($aliasParams)) {
                $this->_active = true;
                return true;
            }
        }

        return false;
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