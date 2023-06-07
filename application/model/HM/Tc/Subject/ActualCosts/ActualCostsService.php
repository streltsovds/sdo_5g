<?php
class HM_Tc_Subject_ActualCosts_ActualCostsService extends HM_Service_Abstract
{   
    
    public function getActualCostsIndexSelect(){
        
        $select = parent::getSelect();
        
        $select->from(
            array(
                'ac' => 'subjects_actual_costs'
            ),
            array( 
                'ac.actual_cost_id',
                'cycle_name' => 'c.name',
                'provider_name' => 'pr.name',
                'ac.document_number',
                'ac.pay_date_document',
                'ac.pay_date_actual',
                'ac.pay_amount',
                'ac.subject_id'
            )
        );
        
        $select->joinLeft(
            array('pr' => 'tc_providers'),
            'pr.provider_id = ac.provider_id',
            array()
        );

        $select->joinLeft(
            array('c' => 'cycles'),
            'c.cycle_id = ac.cycle_id',
            array()
        );
        
        return $select;
    }
    
}