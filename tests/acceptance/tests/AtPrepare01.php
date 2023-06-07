<?php
class AtPrepare01 extends Codeception_Test_Abstract
{
    public function init()
    {
        $this->addActor('admin')
            ->addController('index')
            ->addController('user/list')
            ->addController('orgstructure/list')
            ->addController('orgstructure/import')
        ;
    }
    
    public function run() 
    {
//         $this->index->login($this->admin);
        $this->directAccess(HM_Role_Abstract_RoleModel::ROLE_ADMIN);        

//          $department1 = $this->addData('new/department1');
//          $department1->id = $this->orgstructureList->create($department1);

//          $department11 = $this->addData('new/department11');
//          $this->orgstructureList->create($department11, $department1);

//          $department111 = $this->addData('new/department111');
//          $this->orgstructureList->create($department111, $department11);

//          $department112 = $this->addData('new/department112');
//          $this->orgstructureList->create($department112, $department11);        
        
        $structure = $this->orgstructureImport->import('orgstructure.csv');
        $this->setRequisite('orgstructure', $structure);
        
        $user1 = $this->addData('new/user1');
        $user1->id = $this->userList->create($user1);
        $this->setRequisite('user1', $user1);
        
        $user2 = $this->addData('new/user2');
        $user2->id = $this->userList->create($user2);
        $this->setRequisite('user2', $user2);        

        $this->index->logout();
    }
}