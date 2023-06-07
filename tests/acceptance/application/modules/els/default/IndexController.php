<?php
class IndexController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->main;
    }
    
    /**
     * Авторизация через ссылку "Войти в систему"
     * 
     * @param stdObj $user
     */
    public function login($user) 
    {
        $this->I->amOnPage($this->page->url);
        $this->I->click($this->page->links->loginFormOpen);
        $this->I->waitForElement('#authorization input[type=submit]', Codeception_Registry::get('config')->global->timeToWait); // secs
        
        $this->I->fillField('login', $user->login);
        $this->I->fillField('password', $user->password);
        $this->I->click('#authorization input[type=submit]');
        
        $this->I->waitForElement('.edit-profile', Codeception_Registry::get('config')->global->timeToWait); 
        $this->I->see($user->firstName);
        
        $role = isset($user->role) ? $user->role : HM_Role_Abstract_RoleModel::ROLE_ENDUSER;
        $this->scenario->setCurrentRole($role);
    }
 
    /**
     * Logout
     */
    public function logout() 
    {
        $this->I->click($this->page->links->logout);
        $this->waitForDialog();
        $this->I->click($this->page->dialog->logout, '.ui-dialog');
        $this->scenario->setCurrentRole(HM_Role_Abstract_RoleModel::ROLE_GUEST);
        $this->I->see($this->page->links->loginFormOpen);
    }
    
    
    /**
     * Смена роли текущего actor'а
     * Предполагаем, что она у него есть
     * 
     * @param string $role
     */
    public function switchRole($role)
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles();
        if (array_key_exists($role, $roles)) {

            $this->I->click('.hm-user-roleSwitcher-center');
            $this->I->waitForText($roles[$role], Codeception_Registry::get('config')->global->timeToWait); // secs
            $this->I->click('.hm-user-roleSwitcher-menu-item_' . $role);
            $this->I->see($roles[$role]);
            
            $this->scenario->setCurrentRole($role);
            
        } else {
            throw Exception ('Role not found');
        }
    }
}