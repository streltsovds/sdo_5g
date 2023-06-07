<?php
class Codeception_Test_Abstract
{
    public $I;
    protected $config;
    protected $currentRole;
    
    private $_data = array();
    private $_actors = array();
    private $_controllers = array();
    private $_rollbacks = array();
    
    
    public function __construct($scenario) 
    {
        $this->I = new AcceptanceTester($scenario);

        $path = TEST_ROOT . sprintf('/application/settings/local/%s.ini', get_class($this));
        if (file_exists($path)) {
            $this->config = new Zend_Config_Ini($path);
        }
        
        if (method_exists($this, 'init')) {
            $this->init();
        }
        return $this;
    }
    
    protected function addActor($login)
    {
        if ($actor = Codeception_Registry::get('config')->users->$login) {
            $this->_actors[$login] = $actor;
        } else {
            throw new Exception('User not found');
        }        
        return $this;
    }
    
    private function _getDefaultActor()
    {
        if (count($this->_actors)) {
            $logins = array_keys($this->_actors);
            return $this->_actors[array_shift($logins)]; // first added
        }
    }
    
    
    /**
     * @param string $name 'index' or 'user/list' or 'at/session/report'
     * @throws Exception
     */
    protected function addController($name)
    {
        $parts = explode('/', $name);
        if (count($parts) == 1) {
            array_unshift($parts, 'default');
        }
        if (count($parts) == 2) {
            array_unshift($parts, 'els');
        }
        
        require_once TEST_ROOT . sprintf('/application/modules/%s/%s/%sController.php', $parts[0], $parts[1], ucfirst($parts[2]));

        $parts = explode('/', $name);
        foreach ($parts as &$part) $part = ucfirst($part);
        $className = sprintf('%sController', implode('_', $parts));

        if (class_exists($className)) {

            $parts = explode('/', $name);
            foreach ($parts as &$part) $part = ucfirst($part);
            $parts[0] = strtolower($parts[0]);
            
            $propertyName = implode('', $parts);

            $this->_controllers[$propertyName] = new $className($this); 
        } else {
            throw new Exception('Controller not found');
        }
                
        return $this;
    }
    
    protected function directAccess($role, $login = false)
    {
        $page = Codeception_Registry::get('config')->pages->main;
        
        if (empty($login)) {
            $user = $this->_getDefaultActor();
            $login = $user->login; 
        }
        
        $directAccessParams = sprintf('?login=%s&role=%s&direct-access=1', $login, $role);
        $this->I->amOnPage($page->url . $directAccessParams);
        
        $this->setCurrentRole($role);
        
        return $this;
    }
    
    protected function addData($keys)
    {
        if (isset($this->_data[$keys])) return $this->_data[$keys];
        
        $parts = explode('/', $keys);
        $obj = Codeception_Registry::get('config')->data;
        while ($part = array_shift($parts)) {
            $obj = $obj->$part;
        } 
        // we do not want it to be read-only config
        if ($obj) {
            $this->_data[$keys] = new stdClass();
            foreach ($obj as $key => $value) {
                $this->_data[$keys]->$key = $value;
            }
        }
        return $this->_data[$keys];
    }
    
    protected function setRequisite($key, $value)
    {
        $requisites = Codeception_Registry::get('requisites');
        $requisites[$key] = $value;
        Codeception_Registry::set('requisites', $requisites);
        return true;
    }
    
    protected function getRequisite($key)
    {
        $requisites = Codeception_Registry::get('requisites');
        return isset($requisites[$key]) ? $requisites[$key] : false; 
    }
    
    public function addRollback($sql)
    {
        $this->_rollbacks[] = $sql;
    }
    
    public function logout()
    {
        if ($this->currentRole != HM_Role_Abstract_RoleModel::ROLE_GUEST) {
            $this->index->logout();
        }
    }
    
    public function rollback()
    {
        if (Codeception_Registry::get('config')->global->rollback) {
            $adapter = Zend_Registry::get('serviceContainer')->getService('User')->getMapper()->getAdapter()->getAdapter();
            $this->_rollbacks = array_reverse($this->_rollbacks);
            foreach ($this->_rollbacks as $sql) {
                cd('-------------------->' . $sql);
                $adapter->query($sql);
            }
        }
    }
    
    public function getCurrentRole() 
    {
        return $this->currentRole;
    }
    
    public function setCurrentRole($role) 
    {
        $this->currentRole = $role;
        return $this;
    }
    
    public function __get($property)
    {
        if (isset($this->_actors[$property])) {
            return $this->_actors[$property];
        } elseif (isset($this->_controllers[$property])) {
            return $this->_controllers[$property];
        }
    }
    
    public function __call($method, $args)
    {
        if (!isset($this->_controllers['index'])) {
            $this->addController('index');
        }
        
        switch ($method) {
            case 'menu':
                return call_user_func_array(array($this->index, 'openMenuMain'), $args);
                break;
        }
    }
}
?>
