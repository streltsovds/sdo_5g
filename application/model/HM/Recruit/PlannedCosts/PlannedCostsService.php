<?php
class HM_Recruit_PlannedCosts_PlannedCostsService extends HM_Service_Abstract
{   
    
    public function getPlannedCostsIndexSelect(){    
        $select = parent::getSelect();
        
        $select->from(
            array(
                'pc' => 'recruit_planned_costs'
            ),
            array( 
                'pc.planned_cost_id',
                'period' => new Zend_Db_Expr('CONCAT(pc.year, CONCAT(\'_\', (CASE WHEN pc.month<10 THEN CONCAT(\'0\', pc.month) ELSE pc.month END)))'),
                'provider_name' => 'pr.name',
                'pc.base_sum',
                'pc.corrected_sum',
                'pc.status',
                'year',
                'month',
            )
        );
        
        $select->joinLeft(
            array('pr' => 'recruit_providers'),
            'pr.provider_id = pc.provider_id',
            array()
        );
        
        $select->order(array('year DESC', 'month DESC'));
        
        return $select;
    }
    
}