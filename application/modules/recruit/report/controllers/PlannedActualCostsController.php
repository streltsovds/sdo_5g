<?php
class Report_PlannedActualCostsController extends HM_Controller_Action
{
    public function indexAction(){

        if (! $this->_getParam('ordergrid')) {
            $this->_setParam('ordergrid', 'period_ASC');
        }

        $periodFrom = ($this->_getParam('period_from', '') != '0') ? $this->_getParam('period_from', '') : '1_1900';
        $periodTo   = ($this->_getParam('period_to',   '') != '0') ? $this->_getParam('period_to',   '') : '12_2100';

        $plannedCostsService = $this->getService('PlannedCosts');
        
        $select = $plannedCostsService->getSelect();
        $subSelectPaymentActual = clone $select;
        $subSelectPaymentDocument = clone $select;
        $selectMinPeriod = clone $select;
        
        $select->from(
            array(
                'pc' => 'recruit_planned_costs'
            ),
            array(                
                'period' => new Zend_Db_Expr('CONCAT(pc.month, CONCAT(\'_\', pc.year))'),
                'pc_base_sum'            => new Zend_Db_Expr('SUM(pc.base_sum)'),
                'pc_corrected_sum'       => new Zend_Db_Expr('SUM(pc.corrected_sum)'),
                'ac_document_pay_amount' => 'ac_document.pay_amount',
                'ac_actual_pay_amount'   => 'ac_actual.pay_amount',
                'total'                  => new Zend_Db_Expr('(SUM(pc.corrected_sum) - ac_actual.pay_amount)'),
            )
        );
        
        $subSelectPaymentActual->from('recruit_actual_costs', array(
            'pay_amount' => new Zend_Db_Expr('SUM(pay_amount)'),
            'month',
            'year'
        ));
        $subSelectPaymentActual->where($plannedCostsService->quoteInto(
            'payment_type = ?',
            HM_Recruit_ActualCosts_ActualCostsModel::PAYMENT_TYPE_ACTUAL
        ));
        $subSelectPaymentActual->group(array('month', 'year'));
        
        $select->joinLeft(
            array('ac_actual' => $subSelectPaymentActual),
            'ac_actual.year = pc.year AND ac_actual.month = pc.month',
            array()
        );
        
        
        $subSelectPaymentDocument->from('recruit_actual_costs', array(
            'pay_amount' => new Zend_Db_Expr('SUM(pay_amount)'),
            'month',
            'year'
        ));
        $subSelectPaymentDocument->where($plannedCostsService->quoteInto(
            'payment_type = ?',
            HM_Recruit_ActualCosts_ActualCostsModel::PAYMENT_TYPE_DOCUMENT
        ));
        $subSelectPaymentDocument->group(array('month', 'year'));
        
        $select->joinLeft(
            array('ac_document' => $subSelectPaymentDocument),
            'ac_document.year = pc.year AND ac_document.month = pc.month',
            array()
        );
        
        if($periodFrom && $periodTo){
            list($monthFrom, $yearFrom) = explode('_', $periodFrom);
            list($monthTo, $yearTo)     = explode('_', $periodTo);

            $whereQuery = array(
                'CAST(
                    CONCAT(
                        pc.year, 
                        CONCAT(
                            \'-\', 
                            CONCAT(
                                pc.month, 
                                CONCAT(
                                    \'-\', 
                                    \'01\'
                                )
                            )
                        )
                    ) AS date
                ) >= ? ',
                'AND CAST(
                    CONCAT(
                        pc.year, 
                        CONCAT(
                            \'-\', 
                            CONCAT(
                                pc.month, 
                                CONCAT(
                                    \'-\', 
                                    \'01\'
                                )
                            )
                        )
                    ) AS date
                ) <= ?',
            );

            $select->where($plannedCostsService->quoteInto(
                $whereQuery,
                array($yearFrom.'-'.$monthFrom.'-01', $yearTo.'-'.$monthTo.'-01')
            ));
        }
        
        $select->group(array(
            'pc.month',
            'pc.year',
            'ac_actual.pay_amount',
            'ac_document.pay_amount',
        ));

        $s = (string) $select;

        $columnsOptions = array(
            'period'          => array(
                'title' => _('Период'),
                'callback' => array(
                    'function' => array($this, 'updatePeriod'),
                    'params'   => array('{{period}}')
                )
            ),
            
            'pc_base_sum'               => array(
                'title' => _('Базовые плановые затраты'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{pc_base_sum}}')
                )
            ),
            'pc_corrected_sum'          => array(
                'title' => _('Скорректированные плановые затраты'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{pc_corrected_sum}}')
                )
            ),
            'ac_actual_pay_amount'   => array(
                'title' => _('Фактические затраты (по факту оплаты)'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{ac_actual_pay_amount}}')
                )
            ),
            'ac_document_pay_amount' => array(
                'title' => _('Фактические затраты (по актам)'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{ac_document_pay_amount}}')
                )
            ),
            'total'                  => array(
                'title' => _('Итоги, руб'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params'   => array('{{total}}')
                )
            ),
        );

        
        $summaryOptions = array(
            'total' => 'sum'
        );

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            array(),
            'grid',
            $summaryOptions
        );        
        
        $selectMinPeriod->from(
            array('recruit_planned_costs'),
            array(
                'year' => new Zend_Db_Expr('MIN(year)'),
            )
        );
        
        $minPeriod = $selectMinPeriod->query()->fetchAll();
        
        $fromYear  = $minPeriod[0]['year'];
        
        $periodsList = array_merge(array('0' => ''), HM_Recruit_PlannedCosts_PlannedCostsModel::getPeriodsFromTo($fromYear));
        
        $selectFrom = new Zend_Form_Element_Select('period_from',
            array(
                'Label' => _('Период от:'),
                'multiOptions' => $periodsList,
                'Filters' => array('StripTags'),
                'value'   => $periodFrom
            )
        );
        
        $selectTo   = new Zend_Form_Element_Select('period_to', 
            array(
                'Label' => _('Период до:'),
                'multiOptions' => $periodsList,
                'Filters' => array('StripTags'),
                'value'   => $periodTo
            )
        );
            
        $submit     = new Zend_Form_Element_Submit('submit', array('Label' => _('Сохранить'))); 
        
        $this->view->selectFrom = $selectFrom->render();
        $this->view->selectTo   = $selectTo->render();
        $this->view->submit     = $submit->render();
        
        $this->view->grid          = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }
    
    public function updatePeriod($period) {
        $monthYear = explode('_', $period);
        $months = HM_Recruit_PlannedCosts_PlannedCostsModel::getMonths();
        return $months[$monthYear[0]] . ' ' . $monthYear[1];
    }
    
    public function updateFloatField($value) {
        return round($value, 2);
    }
    
}
