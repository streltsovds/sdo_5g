<?php
class HM_Recruit_Provider_ProviderService extends HM_Service_Abstract
{
    
    public function getSelect($controller, $action) {
        $methodName = 'get' . ucfirst($controller) . ucfirst($action) . 'Select';
        if(method_exists($this, $methodName)){
            return call_user_method($methodName, $this);
        }
    }
    
    public function getListIndexSelect(){
        
        $select = parent::getSelect();
        
        $select->from(
            array(
                'rp' => 'recruit_providers'
            ),
            array( 
                'provider_id' => 'rp.provider_id',
                'name'        => 'rp.name',
                'status'      => 'rp.status',
                'locked'      => 'rp.locked',
            )
        );
        
        return $select;
    }


    public function getList($type = 'actual')
    {
        switch ($type) {
            case 'actual':
                $list = $this->fetchAll($this->quoteInto(array('status = ?'), array(HM_Recruit_Provider_ProviderModel::STATUS_ACTUAL)),'provider_id ASC');
                break;
            case 'userform':
                $list = $this->fetchAll($this->quoteInto(
                    array(
                        'status = ? AND ',
                        'userform = ?'
                    ),
                    array(
                        HM_Recruit_Provider_ProviderModel::STATUS_ACTUAL,
                        HM_Recruit_Provider_ProviderModel::USERFORM_YES

                    )),'provider_id ASC');
                break;
            case 'cost':
                $list = $this->fetchAll($this->quoteInto(
                    array(
                        'status = ? AND ',
                        'cost = ?'
                    ),
                    array(
                        HM_Recruit_Provider_ProviderModel::STATUS_ACTUAL,
                        HM_Recruit_Provider_ProviderModel::COST_YES

                    )),'provider_id ASC');
                break;
            default:
                $list = $this->fetchAll(null,'provider_id ASC');
                break;
        }
        $providers = $list->getList('provider_id', 'name');

        if ($type != 'cost') {
            $provider = $this->getService('RecruitProvider')->fetchOne(array(
                'provider_id = ?' => HM_Recruit_Provider_ProviderModel::ID_ELSE
            ));

            $providers = array(HM_Recruit_Provider_ProviderModel::ID_ELSE => $provider->name) + $providers;
        }
        return $providers;
    }

}