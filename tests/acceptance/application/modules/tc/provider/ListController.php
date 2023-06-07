<?php
class Tc_Provider_ListController extends Codeception_Controller_Action
{
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->plan->providers;
    }
    
    /**
     * Создание провайдера
     * 
     * @param stdObj $provider
     * @return int ID созданного провайдера
     */
    public function create($provider) 
    {
        $this->createEntity(array(
            'name' => $provider->name,
            'city' => ['value' => $provider->city, 'type' => 'fcbk']
        ));
        
        $providerId = $this->grabEntityId('provider_name', $provider->name);
        
        $this->rollback('delete from tc_providers where provider_id = %d', $providerId);
        
        return $providerId;      
    }
}