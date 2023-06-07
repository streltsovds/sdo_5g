<?php
class User_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->administration->users;
    }
    
    /**
     * Создаёт пользователя
     * 
     * @param stdObj $user
     * @return int
     */
    public function create($user) 
    {
        $data = array(
            'userlogin' => $user->login,
            'lastname' => $user->lastName,
            'firstname' => $user->firstName,
            'email' => $user->email,
            'userpassword' => $user->password,
            'userpasswordrepeat' => $user->password,
        );
        
//         $this->createEntity($data); // нельзя использовать по причине generatepassword
        
        $this->openMenuMain($this->page->menu);
        $this->I->click($this->page->links->create);
        $this->I->click('#generatepassword');
        
        $this->I->submitForm('.els-content form', $data);
        $this->I->see($this->pageCommon->grid->messages->success);        
        
        $userId = $this->grabEntityId('login', $user->login);
        
        $this->rollback('delete from People where MID = %d', $userId);
        
        return $userId;
    }
}