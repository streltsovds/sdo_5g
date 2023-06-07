<?php
class Sample01 extends Codeception_Test_Abstract
{
    public function init()
    {
        $this->I->wantTo('Create user, subject; assign user to subject; enter as a user and see the subject.');
        
        $this->addActor('admin')
            ->addController('index')
            ->addController('user/list')
            ->addController('subject/list')
            ->addController('assign/student');
    }
    
    public function run() 
    {
        $this->index->login($this->admin);
//         $this->directAccess(HM_Role_Abstract_RoleModel::ROLE_ADMIN);        
        
        $user = $this->addData('new/user');
        $user->id = $this->userList->create($user);
        
        $this->index->switchRole(HM_Role_Abstract_RoleModel::ROLE_DEAN);
//         $this->directAccess(HM_Role_Abstract_RoleModel::ROLE_DEAN);
        
        $subject = $this->addData('new/subject');
        $subject->id = $this->subjectList->create($subject);
        
        $this->assignStudent->assign($user, $subject);
        
        $this->index->logout();
                

        /****** as user ******/
        
        $this->index->login($user);
        
        $this->subjectList->open($subject);
    }
}