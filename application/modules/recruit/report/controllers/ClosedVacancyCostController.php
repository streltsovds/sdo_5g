<?php
class Report_ClosedVacancyCostController extends HM_Controller_Action
{
    public function indexAction()
    {

        $periodFrom = $this->_getParam('period_from', '');
        $periodTo = $this->_getParam('period_to', '');
        $type = $this->_getParam('type', HM_Recruit_ActualCosts_ActualCostsModel::PAYMENT_TYPE_ACTUAL);

        $plannedCostsService = $this->getService('PlannedCosts');
        $recruitVacancyCandidateService = $this->getService('RecruitVacancyAssign');

        $select = $plannedCostsService->getSelect();

        $selectMinPeriod = clone $select;

        $select->from(
            array(
                'rac' => 'recruit_actual_costs'
            ),
            array(
                'period' => new Zend_Db_Expr('CONCAT(rac.month, CONCAT(\'_\', rac.year))'),
                'provider_name' => 'pr.name',
                'pay_summ' => new Zend_Db_Expr('SUM(rac.pay_amount)'),
                'candidates' => new Zend_Db_Expr('subs.candidates'),
                'per_candidate' => new Zend_Db_Expr('1'),
            )
        );

        $select->joinLeft(
            array('pr' => 'recruit_providers'),
            'pr.provider_id = rac.provider_id',
            array()
        );


        $candidateSelect = $recruitVacancyCandidateService->getSelect();

        $candidateSelect->from(
            array(
                'rc' => 'recruit_candidates'
            ),
            array(
                'provider_id' => 'rc.source',
                'year' => 'rv.complete_year',
                'month' => 'rv.complete_month',
                'candidates' => new Zend_Db_Expr('COUNT(rvc.vacancy_candidate_id)'),
            )
        );

        $candidateSelect->joinInner(
            array('rvc' => 'recruit_vacancy_candidates'),
            'rc.candidate_id = rvc.candidate_id AND rvc.result = ' . HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS,
            array()
        );

        $candidateSelect->joinLeft(
            array('rv' => 'recruit_vacancies'),
            'rvc.vacancy_id = rv.vacancy_id',
            array()
        );

        $candidateSelect->group(array(
            'rv.complete_month',
            'rv.complete_year',
            'rc.source',

        ));

        $select->joinLeft(
            array('subs' => $candidateSelect),
            'subs.provider_id = rac.provider_id AND subs.year = rac.year AND subs.month = rac.month',
            array()
        );



        $select2 = $plannedCostsService->getSelect();

        $select2->from(
            array(
                'rac' => 'recruit_actual_costs'
            ),
            array(
                'period' => new Zend_Db_Expr('CONCAT(rac.month, CONCAT(\'_\', rac.year))'),
                'provider_name' => 'pr.name',
                'pay_summ' => new Zend_Db_Expr('SUM(rac.pay_amount)'),
                'candidates' => new Zend_Db_Expr('subs.candidates'),
            )
        );

        $select2->joinLeft(
            array('pr' => 'recruit_providers'),
            'pr.provider_id = rac.provider_id',
            array()
        );


        $candidateSelect2 = $recruitVacancyCandidateService->getSelect();

        $candidateSelect2->from(
            array(
                'rc' => 'recruit_candidates'
            ),
            array(
                'provider_id' => 'rc.source',
                'year' => 'rv.complete_year',
                'month' => 'rv.complete_month',
                'candidates' => new Zend_Db_Expr('COUNT(rvc.vacancy_candidate_id)'),
            )
        );

        $candidateSelect2->joinInner(
            array('rvc' => 'recruit_vacancy_candidates'),
            'rc.candidate_id = rvc.candidate_id AND rvc.result = ' . HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS,
            array()
        );

        $candidateSelect2->joinLeft(
            array('rv' => 'recruit_vacancies'),
            'rvc.vacancy_id = rv.vacancy_id',
            array()
        );

        $candidateSelect2->group(array(
            'rc.source',
            'rv.complete_year',
            'rv.complete_month'
        ));
        
        $select2->joinLeft(
            array('subs' => $candidateSelect2),
            'subs.provider_id = rac.provider_id AND subs.year = rac.year AND subs.month = rac.month',
            array()
        );



        list($monthFrom, $yearFrom) = array('', '');
        list($monthTo, $yearTo) = array('', '');

        if ($periodFrom ) {
            list($monthFrom, $yearFrom) = explode('_', $periodFrom);
        }

        if ($periodTo) {
            list($monthTo, $yearTo) = explode('_', $periodTo);
        }


        if ($monthFrom && $yearFrom && $monthTo && $yearTo) {
            if ($yearFrom == $yearTo) {
                $where = $plannedCostsService->quoteInto(
                    array(
                        'rac.year = ?',
                        ' AND rac.month >= ?',
                        ' AND rac.month <= ?',
                    ),
                    array(
                        $yearFrom,
                        $monthFrom,
                        $monthTo,
                    )
                );
                $select->where($where);
                $select2->where($where);
            } else {
                $whereArray = array();
                $valueArray = array();
                for ($year = $yearFrom; $year <= $yearTo; $year++) {
                    if ($year == $yearFrom) {
                        for ($month = $monthFrom; $month <= 12; $month++) {
                            $whereArray[] = array(
                                'rac.year = ?',
                                ' AND rac.month = ?',
                            );
                            $valueArray[] = array(
                                $year,
                                $month,
                            );
                        }
                    } else if ($year == $yearTo) {
                        for ($month = 1; $month <= $monthTo; $month++) {
                            $whereArray[] = array(
                                'rac.year = ?',
                                ' AND rac.month = ?',
                            );
                            $valueArray[] = array(
                                $year,
                                $month,
                            );
                        }
                    } else {
                        for ($month = 1; $month <= 12; $month++) {
                            $whereArray[] = array(
                                'rac.year = ?',
                                ' AND rac.month = ?',
                            );
                            $valueArray[] = array(
                                $year,
                                $month,
                            );
                        }
                    }
                }
                foreach ($whereArray as $key => $where) {
                    if ($key != 0) {
                        $where[0] = ' OR ' . $where[0];
                    }
                    $whereArray[$key] = $where;
                }

                $resultWhere = array();
                foreach ($whereArray as $where) {
                    $resultWhere = array_merge($resultWhere, $where);
                }

                $resultValue = array();
                foreach ($valueArray as $value) {
                    $resultValue = array_merge($resultValue, $value);
                }

                $select->where($plannedCostsService->quoteInto(
                    $resultWhere,
                    $resultValue
                ));

                $select2->where($plannedCostsService->quoteInto(
                    $resultWhere,
                    $resultValue
                ));
            }
        }

        if ($type)
        {
            $select->where($plannedCostsService->quoteInto(
                array(
                    'rac.payment_type LIKE ?',
                ),
                array(
                    $type,
                )
            ));

            $select2->where($plannedCostsService->quoteInto(
                array(
                    'rac.payment_type LIKE ?',
                ),
                array(
                    $type,
                )
            ));
        }

        $select2->group(array(
            'rac.provider_id',
            'pr.name',
            'rac.month',
            'rac.year',
            'subs.candidates',
        ));

        $smt = $select2->query();

        $rows = $smt->fetchAll();
        $subGrid = array();
        foreach ($rows as $row) {
            $subGrid[$row['provider_name']] =
                $this->updateFloatField($row['candidates']?($row['pay_summ']/$row['candidates']):$row['pay_summ']). ' руб';
        }

        $select->group(array(
            'rac.month',
            'rac.year',
            'rac.provider_id',
            'pr.name',
            'subs.candidates',
        ));

        $columnsOptions = array(
            'period' => array(
                'title' => _('Период'),
                'callback' => array(
                    'function' => array($this, 'updatePeriod'),
                    'params' => array('{{period}}')
                )
            ),
            'provider_name' => array(
                'title' => _('Канал поиска'),
            ),
            'candidates' => array(
                'title' => _('Количество вакансий'),
            ),

            'pay_summ' => array(
                'title' => _('Сумма затрат, руб'),
                'callback' => array(
                    'function' => array($this, 'updateFloatField'),
                    'params' => array('{{pay_summ}}')
                )
            ),

            'per_candidate' => array(
                'title' => _('Стоимость вакансии, руб'),
                'callback' => array(
                    'function' => array($this, 'updatePerCandidate'),
                    'params' => array('{{pay_summ}}', '{{candidates}}')
                )
            )


        );
        
        $filters = array(
            'period'        => null,
            'provider_name' => null,
            'candidates'    => null,
            'pay_summ'      => null,
        );
        
        $grid = $this->getGrid(
            $select,
            $columnsOptions,
            $filters,
            'grid'
        );





        $selectMinPeriod->from(
            array('recruit_planned_costs'),
            array(
                'year' => new Zend_Db_Expr('MIN(year)'),
            )
        );
        $minPeriod = $selectMinPeriod->query()->fetchAll();

        $fromYear = $minPeriod[0]['year'];

        $periodsList = array_merge(array('0' => '-'), HM_Recruit_PlannedCosts_PlannedCostsModel::getPeriodsFromTo($fromYear));

        $selectFrom = new Zend_Form_Element_Select('period_from',
            array(
                'Label' => _('Период от:'),
                'multiOptions' => $periodsList,
                'Filters' => array('StripTags'),
                'value' => $periodFrom
            )
        );

        $selectTo = new Zend_Form_Element_Select('period_to',
            array(
                'Label' => _('Период до:'),
                'multiOptions' => $periodsList,
                'Filters' => array('StripTags'),
                'value' => $periodTo
            )
        );

        $selectType = new Zend_Form_Element_Select('type',
            array(
                'Label' => _('Тип платежа:'),
                'multiOptions' =>  HM_Recruit_ActualCosts_ActualCostsModel::getPaymentTypes(),
                'Filters' => array('StripTags'),
                'value' => $type
            )
        );

        $submit = new Zend_Form_Element_Submit('submit', array('Label' => _('Сохранить')));


        $this->view->selectFrom = $selectFrom->render();
        $this->view->selectTo = $selectTo->render();
        $this->view->type = $selectType->render();
        $this->view->submit = $submit->render();
        $this->view->subGrid = $subGrid;

        $this->view->grid = $grid;
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

    public function updatePerCandidate($sum, $candidates) {
        if ($candidates > 0) {
            return round($sum/$candidates, 2);
        } else {
            return $sum;
        }
    }
}
