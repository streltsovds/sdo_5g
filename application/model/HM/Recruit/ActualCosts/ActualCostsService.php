<?php
class HM_Recruit_ActualCosts_ActualCostsService extends HM_Service_Abstract
{   
    
    public function getActualCostsIndexSelect(){
        
        $select = parent::getSelect();
        
        $select->from(
            array(
                'ac' => 'recruit_actual_costs'
            ),
            array( 
                'ac.actual_cost_id',
                'period' => new Zend_Db_Expr('CONCAT(ac.year, CONCAT(\'_\', (CASE WHEN ac.month<10 THEN CONCAT(\'0\', ac.month) ELSE ac.month END)))'),
                'provider_name' => 'pr.name',
                'ac.document_number',
                'ac.pay_date_document',
                'ac.pay_date_actual',
                'ac.pay_amount',
                'ac.payment_type',
            )
        );
        
        $select->joinLeft(
            array('pr' => 'recruit_providers'),
            'pr.provider_id = ac.provider_id',
            array()
        );
        
        return $select;
    }
    
}