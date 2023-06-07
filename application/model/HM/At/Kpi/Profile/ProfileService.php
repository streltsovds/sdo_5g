<?php
class HM_At_Kpi_Profile_ProfileService extends HM_Service_Abstract
{
    public function assign($profileId, $kpiId)
    {
        $res = $this->getService('AtKpiProfile')->fetchAll(array(
            'kpi_id = ?' => $kpiId,
            'profile_id = ?' => $profileId, 
        ));
        if (count($res)) return true; // уже назначен

        $this->insert(array(
            'kpi_id' => $kpiId,
            'profile_id' => $profileId,
        ));     
    }
    
    public function assignUserKpisByCycle($cycle)
    {
        $profiles = $this->getService('AtProfile')->fetchAllHybrid('Position', 'Kpi', 'ProfileKpi', array('1 = 1'));
        if (count($profiles)) {
            foreach ($profiles as $profile) {
                $this->assignUserKpisByProfile($profile->profile_id, $cycle);
            }
        }
    }

    public function assignUserKpisByProfile($profileId, $cycle = false)
    {
        $this->unassignUserKpisByProfile($profileId);
        
        if (!$cycle) {
            $cycle = $this->getService('Cycle')->getCurrent();
        }

        if ($cycle) {
            $profile = $this->getService('AtProfile')->fetchAllHybrid('Position', 'Kpi', 'ProfileKpi', array('profile_id = ?' => $profileId));
            if (count($profile)) {
                if (count($positions = $profile->current()->positions)) {
                    $mids = $positions->getList('mid', 'mid');
                    if (count($profileKpis = $profile->current()->kpi)) {
                        foreach ($profileKpis as $profileKpi) {
                            foreach ($mids as $mid) {
                                if (!$mid) continue;
                                $this->getService('AtKpiUser')->insert(array(
                                    'kpi_id' => $profileKpi->kpi_id,        
                                    'value_plan' => $profileKpi->value_plan,        
                                    'weight' => $profileKpi->weight,        
                                    'cycle_id' => $cycle->cycle_id,        
                                    'user_id' => $mid,        
                                ));
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function unassignUserKpisByProfile($profileId)
    {
        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            $profile = $this->getService('AtProfile')->findDependence('Position', $profileId);
            if (count($profile)) {
                if (count($positions = $profile->current()->positions)) {
                    $mids = $positions->getList('mid', 'mid');
                    // @todo: тем самым удалили и индивидуальные цели, что неправильно
                    $this->getService('AtKpiUser')->deleteBy(array(
                        'cycle_id = ?' => $cycle->cycle_id,        
                        'user_id IN (?)' => $mids,        
                    ));
                }
            }
        }
    }
    
    public function unassignUserKpisBySoids($soids)
    {
        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            $positions = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $soids));
            if (count($positions)) {
                $mids = $positions->getList('mid', 'mid');
                // @todo: тем самым удалили и индивидуальные цели, что неправильно
                $this->getService('AtKpiUser')->deleteBy(array(
                    'cycle_id = ?' => $cycle->cycle_id,        
                    'user_id IN (?)' => $mids,        
                ));
            }
        }
    }
    
    
    public function unassignUserKpis($mids)
    {
        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            if (count($mids)) {
                // @todo: тем самым удалили и индивидуальные цели, что неправильно
                $this->getService('AtKpiUser')->deleteBy(array(
                    'cycle_id = ?' => $cycle->cycle_id,        
                    'user_id IN (?)' => $mids,        
                ));
            }
        }
    }
    
    public function unassign($profileId, $kpiId)
    {
        $res = $this->getService('AtKpiProfile')->deleteBy(array(
            'kpi_id = ?' => $kpiId,
            'profile_id = ?' => $profileId, 
        ));      

        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            $profile = $this->getService('AtProfile')->findDependence('Position', $profileId);
            if (count($profile)) {
                $mids = $profile->current()->positions->getList('mid', 'mid');
                $collection = $this->getService('AtKpiUser')->deleteBy(array(
                    'cycle_id = ?' => $cycle->cycle_id,         
                    'kpi_id = ?' => $kpiId,         
                    'user_id IN (?)' => $mids,         
                ));
            }
        }        
    }

    public function update($data) 
    {
        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            $profile = $this->getService('AtProfile')->findDependence('Position', $data['profile_id']);
            if (count($profile) && count($profile->current()->positions)) {
                $mids = $profile->current()->positions->getList('mid', 'mid');
                if (count($mids)) {
                    $this->getService('AtKpiUser')->updateWhere(array(
                        'weight' => $data['weight'],        
                        'value_plan' => $data['value_plan'],        
                    ), array(
                        'cycle_id = ?' => $cycle->cycle_id,        
                        'kpi_id = ?' => $data['kpi_id'],        
                        'user_id IN (?)' => $mids,        
                    ));                    
                }
            }
        }
        parent::update($data);        
    }
}