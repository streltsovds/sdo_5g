<?php
class TcPrepare01 extends Codeception_Test_Abstract
{
    public function init()
    {
        $this->addActor('admin')
            ->addController('index')
            ->addController('tc/provider/list')
        ;
    }
    
    public function run() 
    {
//         $this->index->login($this->admin);
         $this->directAccess(HM_Role_Abstract_RoleModel::ROLE_DEAN);        

        $provider = $this->addData('new/provider');
        $provider->id = $this->tcProviderList->create($provider);
		
		$this->setRequisite('provider', $provider);
    }
}