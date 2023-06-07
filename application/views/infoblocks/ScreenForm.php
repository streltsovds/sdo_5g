<?php
/*
 *  DEPRECATED!!!
 *
 *
 */
class HM_View_Infoblock_ScreenForm extends HM_View_Infoblock_Abstract
{
    protected $id = 'screenform';
    protected $_isVisible = true;

    public function setVisible($visible = true) {
        $this->_isVisible = $visible;
    }

    public function screenForm($title, $content, $attribs)
    {
        if (null === $title) return $this->render($content);
        $infoblockTitle = str_replace('HM_View_Infoblock_', '', get_class($this));        
        $infoblockTitle[0] = strtolower($infoblockTitle[0]);

        if(!isset($attribs['id'])){
            $attribs['id'] = $infoblockTitle; 
        } else if ($attribs['id'] === '') {
            unset($attribs['id']);
            $this->id = null;
        }else{
            $this->id = $attribs['id'];
        }
        
        if($this->class!=''){
           $attribs = $this->view->htmlAttribsPrepare($attribs, array('class' => $this->class));
        }

        if (!isset($attribs['data-infoblock'])) {
            $attribs['data-infoblock'] = $infoblockTitle;
        }
        //if (!isset($attribs['data-undeletable'])) {
        //    $attribs['data-undeletable'] = <value>;
        //}
        
        $attribs = $this->view->htmlAttribsPrepare($attribs, array('class' => array('infoblock-'.$attribs['data-infoblock'])));

        if ($this->_isVisible) {
            $this->view->attribs = $attribs;
            $this->view->title   = $title;
            $this->view->content = $content;
            return $this->view->render('screenform.tpl');
        }
        return '';
    }

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
    }

    public function getNotCachedContent()
    {
    }
}