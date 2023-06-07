<?php
class AtPrepare02 extends Codeception_Test_Abstract
{
    public function init()
    {
        $this->addActor('admin')
            ->addController('index')
            ->addController('assign/atmanager')
        ;
    }
    
    public function run() 
    {
        $this->index->login($this->admin);
        
        $user1 = $this->getRequisite('user1');
        $this->assignAtmanager->assign($user1);
		$this->setRequisite('user1:atmanager', $user1);
         
        $orgstructure = $this->getRequisite('orgstructure');
        foreach ($orgstructure as $department) {
            if (!count($department->parent)) {
                break; // первый попавшийся корневой
            }
        }
         
        $user2 = $this->getRequisite('user2');
        $this->assignAtmanager->assign($user2);
        $this->assignAtmanager->assignResponsibility($user2, $department);
		$this->setRequisite('user2:atspecialist', $user2);
    }
}