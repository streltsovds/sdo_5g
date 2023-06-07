<?php
class HM_At_Evaluation_EvaluationService extends HM_Service_Abstract implements HM_Programm_Event_Interface
{
    public function assignToUser($userId, $evaluationTypeId)
    {
        // ничего не надо назначать. всё уже назначено. расслабьтесь..
    }

    // DEPRECATED
    public function insertEvaluationTypeCompetence($profileId, $relationTypeId, $competences, $programmType = HM_Programm_ProgrammModel::TYPE_MIXED)
    {
        if (!count($evaluations = $this->getEvaluationType(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, $profileId, $relationTypeId, $programmType))) {
            $evaluation = parent::insert(array(
                'name' => HM_At_Evaluation_Method_CompetenceModel::getRelationTypeTitle($relationTypeId),
                'method' => HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE,
                'relation_type' => $relationTypeId,
                'profile_id' => $profileId,
                'scale_id' => $this->getService('Option')->getOption('competenceScaleId'),
                'programm_type' => $programmType
            ));
        } else {
            $evaluation = $this->getOne($evaluations); // он здесь может быть только один такой
        }
        $this->getService('AtEvaluationCriterion')->assignCriteria($evaluation->evaluation_type_id, $competences);
        return $evaluation;
    }

    // DEPRECATED
    public function deleteEvaluationTypeCompetence($profileId, $relationTypeId = null, $programmType = null)
    {
        $return = array();
        if (count($evaluations = $this->getEvaluationType(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, $profileId, $relationTypeId, $programmType))) {
            foreach ($evaluations as $evaluation) {
                $return[] = $evaluation->evaluation_type_id;
                $this->delete($evaluation->evaluation_type_id);
            }
        }
        return $return;
    }

    // DEPRECATED
    public function insertEvaluationTypeKpi($profileId, $relationTypeId, $criteria, $programmType = HM_Programm_ProgrammModel::TYPE_MIXED)
    {
        if (!count($evaluations = $this->getEvaluationType(HM_At_Evaluation_EvaluationModel::TYPE_KPI, $profileId, $relationTypeId, $programmType))) {
            $evaluation = parent::insert(array(
                'name' => HM_At_Evaluation_Method_KpiModel::getRelationTypeTitle($relationTypeId),
                'method' => HM_At_Evaluation_EvaluationModel::TYPE_KPI,
                'profile_id' => $profileId,
                'relation_type' => $relationTypeId,
                'scale_id' => $this->getService('Option')->getOption('kpiScaleId'),
                'programm_type' => $programmType
            ));
        } else {
            $evaluation = $this->getOne($evaluations);
        }
        $this->getService('AtEvaluationCriterion')->assignCriteria($evaluation->evaluation_type_id, $criteria);
        return $evaluation;
    }

    // DEPRECATED
    public function deleteEvaluationTypeKpi($profileId, $relationTypeId = null)
    {
        $return = array();
        if (count($evaluations = $this->getEvaluationType(HM_At_Evaluation_EvaluationModel::TYPE_KPI, $profileId, $relationTypeId))) {
            foreach ($evaluations as $evaluation) {
                $return[] = $evaluation->evaluation_type_id;
                $this->delete($evaluation->evaluation_type_id);
            }
        }
        return $return;
    }
    
    // DEPRECATED
    public function deleteEvaluationType($profileId, $method, $programmType = null)
    {
        $return = array();
        $cond = array('profile_id = ?', ' AND method = ?');
        $bind = array($profileId, $method);

        if ( $programmType !== null ) {
            $cond[] = ' AND programm_type = ?';
            $bind[] = $programmType;
        }

        $evaluations = $this->fetchAll($this->quoteInto( $cond, $bind ));
        if (count($evaluations)) {
            foreach ($evaluations as $evaluation) {
                $return[] = $evaluation->evaluation_type_id;
                $this->delete($evaluation->evaluation_type_id);
            }
        }
        return $return;
    }

    // DEPRECATED
    public function insertEvaluationTypeRating($profileId, $criteria)
    {
        $evaluations = $this->fetchAll($this->quoteInto(
            array('profile_id = ?', ' AND method = ?'),
            array($profileId, HM_At_Evaluation_EvaluationModel::TYPE_RATING)
        ));
        if (!count($evaluations)) {
            $evaluation = parent::insert(array(
                'name' => HM_At_Evaluation_Method_RatingModel::getTitle(),
                'method' => HM_At_Evaluation_EvaluationModel::TYPE_RATING,
                'profile_id' => $profileId,
                'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
            ));
        } else {
            $evaluation = $this->getOne($evaluations);
        }
        $this->getService('AtEvaluationCriterion')->assignCriteria($evaluation->evaluation_type_id, $criteria);
        return $evaluation;
    }

    // DEPRECATED?
    public function insertEvaluationTypeTest($profileId, $criteria, $programmType = HM_Programm_ProgrammModel::TYPE_MIXED)
    {
        $evaluations = $this->fetchAll($this->quoteInto(
            array('profile_id = ?', ' AND method = ?', ' AND programm_type = ?'),
            array($profileId, HM_At_Evaluation_EvaluationModel::TYPE_TEST, $programmType)
        ));
        if (!count($evaluations)) {
            $evaluation = parent::insert(array(
                'name' => HM_At_Evaluation_Method_TestModel::getTitle(),
                'method' => HM_At_Evaluation_EvaluationModel::TYPE_TEST,
                'profile_id' => $profileId,
                'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                'programm_type' => $programmType
            ));
        } else {
            $evaluation = $this->getOne($evaluations);
        }

        // отфильтровать только листья
        $criteria = $this->getService('AtCriterionTest')->fetchAll(array(
            'criterion_id IN (?)' => $criteria,
            new Zend_Db_Expr('lft = rgt-1')
        ))->getList('criterion_id');

        $this->getService('AtEvaluationCriterion')->assignCriteria($evaluation->evaluation_type_id, $criteria);
        return $evaluation;
    }

    public function delete($evaluationTypeId)
    {
        $this->getService('AtEvaluationCriterion')->deleteBy($this->quoteInto('evaluation_type_id = ?', $evaluationTypeId));
        parent::delete($evaluationTypeId);
    }

    public function deleteFromProgramm($evaluationTypeId)
    {
        $this->getService('AtEvaluationCriterion')->deleteBy($this->quoteInto('evaluation_type_id = ?', $evaluationTypeId));
        parent::delete($evaluationTypeId);
    }

    public function getEvaluationType($method, $profileId = null, $relationTypeId = null, $programmType = null)
    {
        $conditions = array('method = ?');
        $bind = array($method);
        if (isset($profileId)) {
            $conditions[] = ' AND profile_id = ?';
            $bind[] = $profileId;
        }
        if (isset($relationTypeId)) {
            $conditions[] = ' AND relation_type = ?';
            $bind[] = $relationTypeId;
        }

        if (isset($programmType)) {
            $conditions[] = ' AND programm_type = ?';
            $bind[] = $programmType;
        }
        return $this->fetchAll($this->quoteInto($conditions, $bind));
    }

    /**
     * Агрегирует результаты компетенций/индикаторов оценки разными категоряими пользователей (relation_type)
     */
    public function profileResultsByRelationType(HM_At_Session_User_UserModel $sessionUser, $options = array())
    {
        $profiledResults = $count = $sum = $countIndicators = $sumIndicators = $criteria = $indicators = array();
        if (count($sessionUser->evaluationResults)) {
            list($sum, $count) = $this->_profile($sessionUser->evaluationResults, 'criterion_id', $options+array('position'=>$sessionUser->position->current()));
            if (count($sessionUser->evaluationIndicators) && $options['competenceUseIndicators']) {
                list($sumIndicators, $countIndicators) = $this->_profile($sessionUser->evaluationIndicators, 'indicator_id', $options+array('position'=>$sessionUser->position->current()));
            }
            $criteria = array_keys($sum);
            if (count($sessionUser->evaluationIndicators)) {
                foreach ($sessionUser->evaluationIndicators as $evaluationIndicator) {
                    if (!isset($criteriaIndicators[$evaluationIndicator->criterion_id])) {
                        $criteriaIndicators[$evaluationIndicator->criterion_id] = array();
                    }
                    $criteriaIndicators[$evaluationIndicator->criterion_id][$evaluationIndicator->indicator_id] = null;
                }
            }

            $relationTypes = array_merge(array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()), array(
                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS,
                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL,
                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT,
                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL,
                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_RESERVE
            ));
            foreach ($criteria as $criterionId) {
                 foreach ($relationTypes as $relationType) {
                    if (!isset($profiledResults[$criterionId])) {
                        $profiledResults[$criterionId] = array('criterion' => array(), 'indicators' => array());
                    }
                    // конспирация
                    if (self::isHiddenChildren($relationType, $count[$criterionId][$relationType])) {
                        // внимание! в массиве подчиненные должны быть до коллег
                        $sum[$criterionId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS] += $sum[$criterionId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN];
                        $count[$criterionId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS] += $count[$criterionId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN];
                        $profiledResults[$criterionId]['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN] = null;
                    } else {
//                        if($relationType==HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_LINEAR || $relationType==HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL) {
//                            $profiledResults[$criterionId]['parents'][$relationType] = $count[$criterionId][$relationType] ? round($sum[$criterionId][$relationType] / $count[$criterionId][$relationType], 2) : false;
//                        } else {
                            $profiledResults[$criterionId]['criterion'][$relationType] = $count[$criterionId][$relationType] ? round($sum[$criterionId][$relationType] / $count[$criterionId][$relationType], 2) : false;
//                        }
                    }

                    if (count($sessionUser->evaluationIndicators) && count($criteriaIndicators[$criterionId]) && $options['competenceUseIndicators']) {
                        foreach (array_keys($criteriaIndicators[$criterionId]) as $indicatorId) {
                            if (!isset($profiledResults[$criterionId]['indicators'][$indicatorId])) {
                                $profiledResults[$criterionId]['indicators'][$indicatorId] = array();
                            }

                            // конспирация
                            if (self::isHiddenChildren($relationType, $count[$criterionId][$relationType])) {
                                // внимание! в массиве подчиненные должны быть до коллег
                                $sumIndicators[$indicatorId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS] += $sumIndicators[$indicatorId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN];
                                $countIndicators[$indicatorId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS] += $countIndicators[$indicatorId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN];
                                $profiledResults[$criterionId]['indicators'][$indicatorId][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN] = null;
                            } else {
                                $profiledResults[$criterionId]['indicators'][$indicatorId][$relationType] = $countIndicators[$indicatorId][$relationType] ? round($sumIndicators[$indicatorId][$relationType] / $countIndicators[$indicatorId][$relationType], 2) : false;
                            }
                        }
                    }
                }
            }

            $memos = $this->getService('AtEvaluationMemoResult')->fetchAll(array(
                'evaluation_memo_id IN (?)'=>array(HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG, HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS),
                'session_event_id IN (?)'=>$sessionUser->evaluationResults->getList('session_event_id')
            ));

            $memosResults = array(HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG=>array(), HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS=>array());
            foreach($memos as $memo) {
                $memosResults[$memo->evaluation_memo_id][] = $memo->value;
            }
        }
        return array('results'=>$profiledResults, 'memos'=>$memosResults);
    }

    static public function isHiddenChildren($relationType, $count)
    {
        return false;// разлепляем, тк нам надо для расчетов отдельные показатели
/*        return ($relationType == HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN) &&
            (!empty($count) && ($count <= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_HIDE_CHILDREN));
*/
    }

    private function _profile($results, $key, $options = array())
    {
        $count = $sum = array();
//#17490
        $sessionEvents = $results->getList('session_event_id');
        $sessionEvents = $this->getService('AtSessionEvent')->fetchAll(array('session_event_id IN (?)' => $sessionEvents));
        $sessionEventsStatus = $sessionEvents->getList('session_event_id', 'status');

        foreach ($results as $result) {
            if($sessionEventsStatus[$result->session_event_id]!=HM_At_Session_Event_EventModel::STATUS_COMPLETED) continue;
//
            $value = HM_Scale_Converter::getInstance()->id2value($result->value_id, $options['competenceScaleId']);
            if ($value == HM_Scale_Value_ValueModel::VALUE_NA) continue;

            if (!isset($sum[$result->$key])) $sum[$result->$key] = array();
            if (!isset($count[$result->$key])) $count[$result->$key] = array();

            $sum[$result->$key][$result->relation_type] += $value;
            $count[$result->$key][$result->relation_type]++;

            // здесь можно и взвесить..
            // HM_At_Evaluation_Method_CompetenceModel::getRelationWeight()
            if ($result->relation_type != HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF) {
                $sum[$result->$key][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] += $value;
                $count[$result->$key][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS]++;
            }
            $sum[$result->$key][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL] += $value;
            $count[$result->$key][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL]++;
        }

        return array($sum, $count);
    }
    
    public function getProgrammTitle($evaluation)
    {
        $methodClass = 'HM_At_Evaluation_Method_' . ucfirst($evaluation->method) . 'Model';
        $subMethods = call_user_func(array($methodClass, 'getSubMethods'), $methodClass);
        $key = trim($evaluation->submethod);
        if ($key && isset($subMethods[$key])) {
            return $subMethods[$key];
        }     
        return $evaluation->name;   
    }
    
    public function copy($evaluationTypeId)
    {
        $newEvaluation = false;
        if ($collection = $this->findDependence(array('EvaluationCriterion', 'EvaluationMemo'), $evaluationTypeId)) {
            $evaluation = $this->getOne($collection); // copy dependences?
            $data = $evaluation->getData();
            unset($data['evaluation_type_id']);
            $newEvaluation = parent::insert($data);
            
            if (count($evaluation->evaluation_criterion)) {
                foreach ($evaluation->evaluation_criterion as $criterion) {
                    $data = $criterion->getData();
                    $data['evaluation_type_id'] = $newEvaluation->evaluation_type_id;
                    $this->getService('AtEvaluationCriterion')->insert($data);
                }
            }
            
            if (count($evaluation->evaluation_memo)) {
                foreach ($evaluation->evaluation_memo as $memo) {
                    $data = $memo->getData();
                    unset($data['evaluation_memo_id']);
                    $data['evaluation_type_id'] = $newEvaluation->evaluation_type_id;
                    $this->getService('AtEvaluationMemo')->insert($data);
                }
            }
        }
        return $newEvaluation;
    }
    
    // при включении evaluation'а в программу нужно обновить критерии, им оцениваемые
    public function updateCriteria($evaluation)
    {
        if (is_int($evaluation)) $evaluation = $this->getService('AtEvaluation')->getOne($this->getService('AtEvaluation')->find($evaluation));

        if ($evaluation) {
            
            // возможно, стоит кэшировать в базе profile_id для всех evaluation'ов
            if (!$profileId = $evaluation->profile_id) {
                if (!empty($evaluation->newcomer_id) && ($newcomer = $this->getService('RecruitNewcomer')->getOne($this->getService('RecruitNewcomer')->find($evaluation->newcomer_id)))) {
                    $profileId = $newcomer->profile_id;
                } elseif (!empty($evaluation->vacancy_id) && ($vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->find($evaluation->vacancy_id)))) {
                    $profileId = $vacancy->profile_id;
                } elseif (!empty($evaluation->reserve_id) && ($reserve = $this->getService('HrReserve')->getOne($this->getService('HrReserve')->find($evaluation->reserve_id)))) {
                    $profileId = $reserve->profile_id;
                }
            }
            if ($criterionType = HM_At_Evaluation_EvaluationModel::getMethodSubject($evaluation->method)) {
                
                // критерии оценки способов достижения нигде не редактируются (только в глобальном справочнике)
                // используем все какие есть
                if ($criterionType == HM_At_Evaluation_EvaluationModel::SUBJECT_KPI) {
                    $criteria = $this->getService('AtCriterionKpi')->fetchAll();
                } elseif ($profileId) {
                    
                    // остальные типы критериев настраиваются в профиле
                    // даже если это вакансия (а у вакансии критерии настраиваюся отдельно, через карандашик),
                    // в момент включения вида оценки в программу надо подтянуть критерии из профиля
                    $criteria = $this->getService('AtProfileCriterionValue')->fetchAll(array(
                        'profile_id = ?' => $profileId,
                        'criterion_type = ?' => $criterionType,
                    ));
                }
                $this->getService('AtEvaluationCriterion')->deleteBy(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id));
                foreach ($criteria as $criterion) {
                    $data = array(
                        'evaluation_type_id' => $evaluation->evaluation_type_id,
                        'criterion_id' => $criterion->criterion_id,
                    );
                    $this->getService('AtEvaluationCriterion')->insert($data);
                }
            }
        }
    }
    
    // для вакансии получить список назначенных критериев сложнее, нежели для профиля
    // в вакансии возможна тонкая настройка evaluation_type (через карандашик в программе);
    // есть похожий метод HM_Recruit_Vacancy_VacancyService::getVacancyCriteria(),
    // который возвращает только компетенции, но независимо от наличия оценки по компетенциям в программе
    public function getVacancyCriteria($vacancyId)
    {
        $evaluations = $criteriaTypes = array();
        if (count($collection = $this->getService('AtEvaluation')->fetchAllDependence('EvaluationCriterion', array('vacancy_id = ?' => $vacancyId)))) {
            foreach ($collection as $evaluation) {
                if (count($evaluation->evaluation_criterion)) {
                    $criteriaTypes[$evaluation->method] = $evaluation->evaluation_criterion->getList('criterion_id');
                }
            }
        }        
        return $criteriaTypes;
    }
    
    public function getMaxScaleValue($methodId)
    {
        switch ($methodId) {
            case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                return 100;
            case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                // не совсем так; шкалы могут быть разные в оценке и подборе
                return $this->getService('Scale')->getMaxValue($this->getService('Option')->getOption('competenceScaleId'));
        }
        return 10;        
    }
    
    public function overrideQuest($evaluation)
    {
        if (count($collection = $this->getService('AtEvaluationCriterion')->fetchAllDependence('Quest', array(
            'evaluation_id = ?' => $evaluation->evaluation_type_id,     
            'quest_id != ?' => 0     
        )))) {
        }
        
    }
}