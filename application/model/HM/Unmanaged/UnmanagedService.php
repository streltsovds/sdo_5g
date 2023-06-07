<?php
class HM_Unmanaged_UnmanagedService extends HM_Service_Abstract
{
    private $_navigationContainer = null;

    public function getController()
    {
        return Zend_Registry::get('unmanaged_controller');
    }

    public function getActionsXmlObject()
    {
        return $GLOBALS['domxml_object'];
    }

    public function removeActionsXmlId($id)
    {
        $xml = $this->getActionsXmlObject();
        if ($xml) {
            if ($node = $xml->get_element_by_id($id)) {
                if ($parent = $node->parent_node()) {
                    $parent->remove_child($node);
                }
            }
        }
    }

    /**
     * @return Zend_Navigation
     */
    public function initNavigationContainer()
    {
        $pages = array();
        $groupId = substr($this->getController()->page_id, 0, 3);
        if ($this->getActionsXmlObject()) {
            if ($group = $this->getActionsXmlObject()->get_element_by_id($groupId)) {
                $pages[$groupId] = array(
                    'id'    => $group->get_attribute('id'), 
                    'label' => iconv('UTF-8', Zend_Registry::get('config')->charset, $group->get_attribute('name')),
                    'module' => 'default',
                    'controller' => 'index',
                    'action' => 'index'
                );
                $pageId = substr($this->getController()->page_id, 0, 5);
                if ($page = $this->getActionsXmlObject()->get_element_by_id($pageId)) {
                    $url = $page->get_attribute('url');
                    if (strstr($url, '.php') !== false) {
                        $pages[$groupId]['pages'] = array(
                            'id'    => $page->get_attribute('id'),
                            'label' => iconv('UTF-8', Zend_Registry::get('config')->charset, $page->get_attribute('name')),
                            'uri'   => $url,
                            'pages' => array(array('uri' => '', 'active' => true))
                        );
                    } else {
                        $parts = explode('/', $url);
                        $pages[$groupId]['pages'] = array(array(
                            'id'         => $page->get_attribute('id'),
                            'label'      => iconv('UTF-8', Zend_Registry::get('config')->charset, $page->get_attribute('name')),
                            'module'     => (isset($parts[0]) ? $parts[0] : 'default'),
                            'controller' => ((isset($parts[1]) && strlen($parts[1])) ? $parts[1] : 'index'),
                            'action'     => ((isset($parts[2]) && strlen($parts[2])) ? $parts[2] : 'index'),
                            'pages' => array(array('uri' => '', 'active' => true))
                        ));
                    }

                    if (count($group->get_elements_by_tagname('page')) == 1) {
                        $pages[$groupId] = $pages[$groupId]['pages'][0];
                    }
                }
            }
        }
        $this->_navigationContainer = new Zend_Navigation($pages);
        return $this->_navigationContainer;
    }

    /**
     * @return Zend_Navigation
     */
    public function getNavigationContainer()
    {
        if (null === $this->_navigationContainer) {
            $this->initNavigationContainer();
        }
        return $this->_navigationContainer;
    }

    public function getPeopleFilter()
    {
        return new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
    }


    /**
     * @return string Return current page_id m....
     */
    public function getCurrentPageId(){
    	return $this->getController()->page_id;
    }

    public function setCurrentPageId($pageId)
    {
        $this->getController()->page_id = $pageId;
    }
    
    /**
     * Adapter for ActionsUtil::getLinksDefault
     * @param unknown_type $pageId
     */
    public function getCurrentPageLinks($pageId){
		
        $container = Zend_Registry::get('serviceContainer');
        
    	$return = array();
    	
    	$res = ActionsUtil::getLinksDefault($pageId);
    	
    	foreach($res as $val){
		    $link = $GLOBALS['domxml_object']->get_element_by_id($val);
		    if($link->has_attribute('name') && $link->has_attribute('url')){
		        //pr($link->get_attribute('name'));
		        if(!$link->has_attribute('profiles')){
		        
		        $return[] = array('title' => $link->get_attribute('name'),
		                          'url'   => $link->get_attribute('url')
		                    );
		        }else{
		            $roles = explode(',', $link->get_attribute('profiles'));
		            
		            if(in_array($container->getService('User')->getCurrentUserRole(), $roles)){
		                $return[] = array('title' => $link->get_attribute('name'),
		                                  'url'   => $link->get_attribute('url')
		                            );
		            }
		        }            
		                    
		    }

        }
    	return $return;
    }
    
    public function setHeader($header){
        $this->getController()->setHeader($header);
    }

    public function setSubHeader($header){
        $this->getController()->setSubHeader($header);
    }

    public function disableBreadcrumbs(){
        $this->getController()->disableBreadcrumbs();
    }

    public function translit($str) {
        $str=str_replace(
           array('Ш', 'Щ',  'Ж', 'Я', 'Ч', 'Ю', 'Ё', 'ш', 'щ',  'ж', 'я', 'ч', 'ю', 'ё', 'Й','Ц','У','К','Е','Н','Г','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Э','С','М','И','Т','Ь','Б','й','ц','у','к','е','н','г','з','х','ъ','ф','ы','в','а','п','р','о','л','д','э','с','м','и','т','ь','б',' '),
           array('SH','SCH','ZH','YA','CH','YU','YO','sh','sch','zh','ya','ch','yu','yo', 'J', 'C', 'U', 'K', 'E', 'N', 'G', 'Z', 'H', '_', 'F', 'Y', 'V', 'A', 'P', 'R', 'O', 'L', 'D', 'E', 'S', 'M', 'I', 'T', '_', 'B', 'j', 'c', 'u', 'k', 'e', 'n', 'g', 'z', 'h', '_', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'e', 's', 'm', 'i', 't', '_', 'b', '_'),
           $str);
        return $str;
    }

     
    public function deleteFolder($dir){
        $d = dir($dir);
        while($entry = $d->read()) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($dir."/".$entry))
                    $this->deleteFolder($dir."/".$entry);
                else
                    unlink ($dir."/".$entry);
            }
        }
        $d->close();
        @rmdir($dir);
    }

    public function detectEncoding($str)
    {
        $encodings = array('UTF-8', 'Windows-1251');
        foreach($encodings as $encoding) {
            if ($str == iconv($encoding, $encoding, $str)) {
                return $encoding;
            }
        }
        return 'UTF-8';
    }

    public function detectFileEncoding($filename)
    {
        if ($content = file_get_contents($filename)) {
            $content = iconv($this->detectEncoding($content), Zend_Registry::get('config')->charset, $content);
            if (strlen($content)) {
                if (strtolower(Zend_Registry::get('config')->charset) == 'utf-8') {
                    $content = preg_replace('/\xEF\xBB\xBF/', '', $content);
                }
                if (is_writeable($filename)) {
                    file_put_contents($filename, $content);
                }
            }
        }
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function removeRoles($roles)
    {
        $user = $this->getController()->user;
        if (is_array($user->profiles) && count($user->profiles)) {
            foreach($user->profiles as $index => $profile) {
                if (in_array($profile->basic_name, $roles)) {
                    unset($user->profiles[$index]);
                }
            }
        }
    }

    public function removeRoleActions($actions)
    {
        $user = $this->getController()->user;
        if (isset($user->profile_current->actions) && is_array($user->profile_current->actions) && count($user->profile_current->actions)) {
            foreach($user->profile_current->actions as $index => $action) {
                if (in_array($action->id, $actions)) {
                    unset($user->profile_current->actions[$index]);
                }
            }
        }
    }
    public function removeSearch()
    {
    	if ($view = $this->getController()->view_root) {
    		$view->disable_search = true;
    	}
    }

    public function initUnmanagedSession($user, $systemInfo = false)
    {
        $s = new Zend_Session_Namespace('s');
        $s->mid   = $user->MID;
        $s->login = $user->Login;
        $s->perm  = $this->getService('Unmanaged')->getPermission($user->role);
        $s->user['lname'] = $user->LastName;
        $s->user['fname'] = $user->FirstName;
        $s->user['patronymic'] = $user->Patronymic;

        $sessionData = array(
            'mid'   => $user->MID,
            'start' => $this->getDateTime(),
            'stop'  => $this->getDateTime(),
            'ip'    => $_SERVER["REMOTE_ADDR"]
        );

        // если была передана информация о системе, то сохраняем её
        if ($systemInfo) {
            $systemData = array(
                'browser_name'           => !empty($systemInfo['browser']['name']) ? $systemInfo['browser']['name'] : null,
                'browser_version'        => !empty($systemInfo['browser']['value']) ? $systemInfo['browser']['value'] : null,
                'flash_version'          => !empty($systemInfo['flash']['value']) ? $systemInfo['flash']['value'] : null,
                'os'                     => !empty($systemInfo['os']['value']) ? $systemInfo['os']['value'] : null,
                'screen'                 => !empty($systemInfo['screen']['value']) ? $systemInfo['screen']['value'] : null,
                'cookie'                 => (int) !empty($systemInfo['cookie']['value']),
                'js'                     => (int) !empty($systemInfo['js']['value']),
                'java_version'           => !empty($systemInfo['java']['value']) ? $systemInfo['java']['value'] : null,
                'silverlight_version'    => !empty($systemInfo['silverlight']['value']) ? $systemInfo['silverlight']['value'] : null,
                'acrobat_reader_version' => !empty($systemInfo['acrobat_reader']['value']) ? $systemInfo['acrobat_reader']['value'] : null,
                'msxml_version'          => !empty($systemInfo['msxml']['value']) ? $systemInfo['msxml']['value'] : null
            );
            $sessionData = array_merge($sessionData, $systemData);
        }

        $session = $this->getService('Session')->insert($sessionData);

        $s->sessid = $session->sessid;

    }

    public function getPermission($role)
    {
        switch($role) {
            case HM_Role_Abstract_RoleModel::ROLE_USER:
                return 0.5;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:
                return 0.75;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_STUDENT:
                return 1;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_TEACHER:
                return 2;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_DEAN:
                return 3;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_DEVELOPER:
                return 3.3;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_MANAGER:
                return 3.6;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_ADMIN:
                return 4;
                break;
        }

        if (false !== strstr($role, HM_Role_Custom_CustomModel::PREFIX)) {
            pr(
                $this->getService('RoleCustom')->getBasicRole(
                    str_replace(HM_Role_Custom_CustomModel::PREFIX, '', $role)
                )
            );
            return $this->getPermission($this->getService('RoleCustom')->getBasicRole(
                str_replace(HM_Role_Custom_CustomModel::PREFIX, '', $role)
            ));
        }

        return 0;
    }
}