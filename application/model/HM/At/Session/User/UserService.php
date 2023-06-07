<?php
class HM_At_Session_User_UserService extends HM_Service_Abstract
{
    // определяет по объекту sessionUser кто является носителем процесса:
    // сам sessionUser, или это кандидат в процессе подбора или участник адаптации;
    // от него можно получить сам процесс
    public function getProcessSubject($sessionUser)
    {
        if (!is_a($sessionUser, 'HM_At_Session_User_UserModel')) {
            $sessionUser = $this->getOne($this->findDependence(array('VacancyAssign', 'Newcomer', 'Session', 'SessionEvents'), $sessionUser));
        }
        if ($sessionUser) {
            if (count($sessionUser->vacancyAssign)) {
                return $sessionUser->vacancyAssign->current();
            } elseif (count($sessionUser->newcomer)) {
                return $sessionUser->newcomer->current();
            }
        }    
        return $sessionUser;   
    }
    
    public function getProgress($sessionUser)
    {
        $this->events = $this->getService('AtSessionEvent')->fetchAll(array('session_id = ?' => $this->session_id));
        $countUser = $countRespondent = $totalUser = $totalRespondent = 0;
        $currentUserId = $this->getService('User')->getCurrentUserId();
        foreach ($this->events as $event) {
            if ($event->user_id == $currentUserId) {
                if ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) $countUser++;
                $totalUser++;
            }
            if ($event->respondent_id == $currentUserId) {
                if ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) $countRespondent++;
                $totalRespondent++;
            }
        }
        $progressUser = ($totalUser) ? ceil(100 * $countUser/$totalUser) : false;
        $progressRespondent = ($totalRespondent) ? ceil(100 * $countRespondent/$totalRespondent) : false;
        switch ($participantType) {
            case HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_USER:
                return $progressUser;
            case HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_RESPONDENT:
                return $progressRespondent;
            default:
                return array(
                    HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_USER => $progressUser,
                    HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_RESPONDENT => $progressRespondent,
                );
        }
    }

    public function isSessionUser($sessionId, $userId)
    {
        $collection = $this->fetchAll(array(
            'session_id = ?' => $sessionId,
            'user_id = ?' => $userId,
        ));
        return count($collection);
    }

    public function isActiveSessionUser($sessionId, $userId)
    {
        $collection = $this->fetchAll(array(
            'session_id = ?' => $sessionId,
            'user_id = ?' => $userId,
            'status != ?' => HM_At_Session_User_UserModel::STATUS_COMPLETED,
        ));
        return count($collection);
    }

    // wrapper для getAnalyticsChartData т.к. для js-графиков нужен другой формат данных
    public function getAnalyticsChartDataJs($sessionUserId, $analyticsTypes)
    {
        $return = array();
        $chartData = $this->getAnalyticsChartData($sessionUserId, $analyticsTypes);
        $colors = HM_At_Evaluation_Method_CompetenceModel::getAnalyticsColors();
        
        foreach ($chartData['graphs'] as $key => $value) {
            $return['graphs'][$key] = array(
                'legend' => $chartData['legend'][$key],        
                'color' => $colors[$key],        
            );
        }
        
        foreach ($chartData['series'] as $key => $value) {
            if (empty($value)) continue;
            $columnsSet = array(
                'title' => wordwrap($value, 15, "\n")
            );
            foreach($chartData['graphs'] as $columnsKey => $columns){
                if ($columnsKey == 'sessions') {
                    foreach ($columns as $sessionKey => $sessionColumns) {
                        if(isset($sessionColumns[$key])){
                            $columnsSet[$columnsKey . '_'. $sessionKey] = $sessionColumns[$key];
                        }
                    }
                } else {
                    if(isset($columns[$key])){
                        $columnsSet[$columnsKey] = $columns[$key];
                    }
                }
            }
            $return['data'][] = $columnsSet;
        }

        // #27946
        foreach ($return['data'] as $key => $datum) {
            $return['data'][$key]['profile'] = ((int)$datum['profile'] > 0) ? $datum['profile'] : 0;
        }

        return $return;    
    }
    
    // данные для xml-графиков на странице "Анализ результатов"
    public function getAnalyticsChartData($sessionUserId, $analyticsTypes)
    {
        $series = $graphs = $legend = array();

        if ($sessionUserId) {
            $sessionUser = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators'), $sessionUserId)->current();
            // $scaleId = $this->getService('Option')->getOption('competenceScaleId');
            $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $sessionUser->session->current()->getOptionsModifier()); 
            $criteria = $this->getService('AtCriterion')->fetchAll();
            $criteriaCache = $criteria->getList('criterion_id', 'name');

            if ($analyticsTypes[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER]) {
                $results = $this->getService('AtSessionUserCriterionValue')->fetchAll(array(
                    'session_user_id = ?' => $sessionUserId,
                    'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
                ));
                foreach ($results as $result) {
                    $series[$result->criterion_id] = $criteriaCache[$result->criterion_id];
                    $graphs[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER][$result->criterion_id] = $result->value;
                }
            }

            if ($analyticsTypes[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE]) {
                $profileId = count($sessionUser->position) ? $sessionUser->position->current()->profile_id : 0;
                $profile = $this->getService('AtProfile')->findDependence(array('CriterionValue'), $profileId)->current();
                if(count($profile->criteriaValues)){
                    foreach ($profile->criteriaValues as $criteriaValue) {
                        if ($criteriaValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) continue;
                        $series[$criteriaValue->criterion_id] = $criteriaCache[$criteriaValue->criterion_id];
                        $graphs[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE][$criteriaValue->criterion_id] = HM_Scale_Converter::getInstance()->id2value($criteriaValue->value_id, $options['competenceScaleId']);
                    }
                }
            }

            if ($sessions = $analyticsTypes[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS]) {
                if (is_array($sessions) && count($sessions)) {
                    $sessionUsers = $this->getService('AtSessionUser')->fetchAll(array(
                        'user_id = ?' => $sessionUser->user_id,
                        'session_id IN (?)' => implode($sessions),
                    ), 'session_user_id ASC')->getList('session_user_id');
                    $results = $this->getService('AtSessionUserCriterionValue')->fetchAll(array(
                        'session_user_id IN (?)' => implode($sessionUsers),
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,        
                    ));
                    foreach ($results as $result) {
                        $series[$result->criterion_id] = $criteriaCache[$result->criterion_id];
                        $graphs[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS][$result->session_user_id][$result->criterion_id] = $result->value;
                    }
                }
            }

            if ($positionId = $analyticsTypes[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION]) {
                $position = $this->getService('Orgstructure')->find($positionId)->current();
                if ($profileId = $position->profile_id) {
                    $profile = $this->getService('AtProfile')->findDependence(array('CriterionValue'), $profileId)->current();
                    foreach ($profile->criteriaValues as $criteriaValue) {
                        if ($criteriaValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) continue;
                        $series[$criteriaValue->criterion_id] = $criteriaCache[$criteriaValue->criterion_id];
                        $graphs[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION][$criteriaValue->criterion_id] = HM_Scale_Converter::getInstance()->id2value($criteriaValue->value_id, $options['competenceScaleId']);
                    }
                }
            }

            $legend = array(
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => _('Профиль успешности должности пользователя'),
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => _('Профиль пользователя по итогам текущей оценочной сессии'),
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION => _('Профиль успешности другой должности'),
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS => _('Профиль пользователя по итогам прошлой оценочной сессии'),
            );
        }
        $result = array();


	foreach($graphs as $i=>$graph) // Чарт не потребляет запятые в числах - меняем на точку
	    foreach($graph as $j=>$gr)
		$graphs[$i][$j] = str_replace(',', '.', $gr);

        $result['legend'] = $legend;
        $result['series'] = $series;
        $result['graphs'] = $graphs;

        return $result;
    }

    // нужен для построения матрицы
    public function getKpiTotal($sessionUser)
    {
        // на случай если $sessionUser пришёл неподготовленный
        if (!count($sessionUser->sessionEvents)) {
            $sessionUser->sessionEvents = $this->getService('AtSessionEvent')->fetchAll(array('session_user_id = ?' => $sessionUser->session_user_id, 'method = ?'=>HM_At_Evaluation_EvaluationModel::TYPE_KPI));
        }
        if (!count($sessionUser->session)) {
            $sessionUser->session = $this->getService('AtSession')->find($sessionUser->session_id);
        }
//
        $kpiTotal = $kpiRatio = 1;
        $userKpis = $criteriaValues = $kpiValueWeighted = array();

        if (count($sessionUser->userKpis)) {
            $userKpis = $sessionUser->userKpis;
        } else {
            try {

                $relationType = HM_At_Evaluation_EvaluationModel::RELATION_TYPE_PARENT;
                if (count($sessionUser->session) && ($sessionUser->session->current()->programm_type == HM_Programm_ProgrammModel::TYPE_RESERVE)) {
                    $relationType = HM_At_Evaluation_EvaluationModel::RELATION_TYPE_PARENT_RESERVE;
                }

                $cycleId = count($sessionUser->session) ? $sessionUser->session->current()->cycle_id : 0;
                $userKpis = $this->getService('AtKpiUser')->getUserKpis($sessionUser->user_id, $cycleId, $relationType);
            } catch (Exception $e) {

            }
        }

        if (count($userKpis)) {
            foreach ($userKpis as $clusterId => $kpis) {
                foreach ($kpis as $kpiUser) {

                    $weight = ($kpiUser['weight'] !== null) ? $kpiUser['weight'] : 1;

                    switch ($kpiUser['value_type']) {
                        case HM_At_Kpi_User_UserModel::TYPE_QUALITATIVE:
                            $value = $kpiUser['value_fact'];
                            break;
                        case HM_At_Kpi_User_UserModel::TYPE_QUANTITATIVE:
                            $value = $kpiUser['value_plan'] ? $kpiUser['value_fact'] / $kpiUser['value_plan'] : 1;
                            break;
                    }

                    $kpiValueWeighted[] = $weight * $value; // 0 делить на 0 равно 1
                }
            }
        } else {
            $kpiValueWeighted = array(1);
        }

        $kpiTotal = round(array_sum($kpiValueWeighted) / count($kpiValueWeighted), 2);

        // Считаем данные по оценкам     
        
        if (count($sessionUser->sessionEvents)) {
            foreach ($sessionUser->sessionEvents as $sessionEvent) {

                if ($sessionEvent->method != HM_At_Evaluation_EvaluationModel::TYPE_KPI) continue;

                $kpiCriteriaResults = $this->getService('AtEvaluationResults')->fetchAllDependence(array('ScaleValue', 'CriterionKpi'), array('session_event_id = ?' => $sessionEvent->session_event_id));
                if (count($kpiCriteriaResults)) {
                    foreach ($kpiCriteriaResults as $result) {
                        if (!count($result->criterionKpi)) continue; // impossible
                        $criterionKpi = $result->criterionKpi->current();
                        $scaleValue = $result->scale_value->current();
                        $criteriaValues[] = $scaleValue->value;
                    }
                    $kpiRatio = $this->getService('AtKpi')->mapKpiRatio($criteriaValues);
                    break; // первая найденная результативная анкета KPI 
                }
            }
        }

        // взвешенное значение всех показателей помноженное на хитрый коэффициент поправки в зависимости от оценки способов достижения
        // в базовой версии at хитрый коэффициент не используется (равен 1)
        return $kpiTotal * $kpiRatio;
    }

    // просто среднее - если нет требуемых уровней у профиля
    public function getResultsAvg($sessionUser)
    {
        $analyticsTypes = array(
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
        );

        // @todo: убрать chartData
        $competenceData = $this->getAnalyticsChartData($sessionUser->session_user_id, $analyticsTypes);
        $competences = $competenceData['graphs'];

        if ($competences['user']) {
            $sum = $cnt = 0;
            foreach ($competences['user'] as $key => $value) {
                if ($competences['user'][$key] != 0) {
                    $sum += $competences['user'][$key];
                    $cnt++;
                }
            }
        }
        return $cnt ? round($sum/$cnt, 2) : 0;
    }

    // нужен для построения матрицы
    public function getResultsVsProfile($sessionUser)
    {
        $analyticsTypes = array(
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => 1,
        );

        // @todo: убрать chartData
        $competenceData = $this->getAnalyticsChartData($sessionUser->session_user_id, $analyticsTypes);
        $competences = $competenceData['graphs'];

        if ($competences['user']) {
            $resultUserProfile = array (); // Массив промежуточных данных от деления каждой компетенции
            foreach ($competences['user'] as $key => $value) {
                if($competences['user'][$key] != 0) {
                    $resultUserProfile[] = ($competences['profile'][$key]) ? $value / $competences['profile'][$key] : 0;
                }
            }
        }

        if (count($resultUserProfile)) {
            return array_sum($resultUserProfile) / count($resultUserProfile);
        }

        return 1;
    }

    public function getMatrixBlock($kpiTotal, $resultsVsProfile)
    {
        if ($kpiTotal <= .7) {
            $row = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BOTTOM_ROW;
        } elseif ($kpiTotal > 1) {
            $row = HM_At_Evaluation_Method_CompetenceModel::MATRIX_TOP_ROW;
        } else {
            $row = HM_At_Evaluation_Method_CompetenceModel::MATRIX_MIDDLE_ROW;
        }

        if ($resultsVsProfile >= 1){
            $col = HM_At_Evaluation_Method_CompetenceModel::MATRIX_RIGHT_COLUMN;
        } else {
            $col = HM_At_Evaluation_Method_CompetenceModel::MATRIX_LEFT_COLUMN;
        }

        if (($row == HM_At_Evaluation_Method_CompetenceModel::MATRIX_TOP_ROW) && ($col == HM_At_Evaluation_Method_CompetenceModel::MATRIX_RIGHT_COLUMN)) {
            $matrixBlock = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BLOCK_LEADERS;
        } elseif (($row == HM_At_Evaluation_Method_CompetenceModel::MATRIX_MIDDLE_ROW) && ($col == HM_At_Evaluation_Method_CompetenceModel::MATRIX_RIGHT_COLUMN)) {
            $matrixBlock = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BLOCK_PERSPECTIVE;
        } elseif (($row == HM_At_Evaluation_Method_CompetenceModel::MATRIX_BOTTOM_ROW) && ($col == HM_At_Evaluation_Method_CompetenceModel::MATRIX_RIGHT_COLUMN)) {
            $matrixBlock = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BLOCK_ANALYSIS;
        } elseif (($row == HM_At_Evaluation_Method_CompetenceModel::MATRIX_TOP_ROW) && ($col == HM_At_Evaluation_Method_CompetenceModel::MATRIX_LEFT_COLUMN)) {
            $matrixBlock = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BLOCK_EXPERTS;
        } elseif (($row == HM_At_Evaluation_Method_CompetenceModel::MATRIX_MIDDLE_ROW) && ($col == HM_At_Evaluation_Method_CompetenceModel::MATRIX_LEFT_COLUMN)) {
            $matrixBlock = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BLOCK_DILIGENTS;
        } elseif (($row == HM_At_Evaluation_Method_CompetenceModel::MATRIX_BOTTOM_ROW) && ($col == HM_At_Evaluation_Method_CompetenceModel::MATRIX_LEFT_COLUMN)) {
            $matrixBlock = HM_At_Evaluation_Method_CompetenceModel::MATRIX_BLOCK_RISK;
        }

        return $matrixBlock;
    }
    
    public function generatePdfs($sessionUserIds)
    {
        if (!is_array($sessionUserIds)) $sessionUserIds = [$sessionUserIds];

        $config = Zend_Registry::get('config');
        $reportsDir = $config->path->upload->reports;

        if (count($collection = $this->fetchAll(['session_user_id IN (?)' => $sessionUserIds]))) {

            /** @var HM_At_Session_User_UserModel $sessionUser */
            foreach ($collection as $sessionUser) {

                // $domain = $config->domain; Вроде не нужен, генерация происходит при запросах из браузера
                $url = $sessionUser->getReportUrl();
                $url = $url . '/print/1/pdf/1';

                // Вся работа с созданием папок вынесена в headlesschrome.php
                $sessionDirectory = $reportsDir . $sessionUser->session_id;
                $outputFile = $sessionUser->session_user_id . '.pdf';

                HM_Export_PdfManager::sendToHeadlessChrome($url, $outputFile, $sessionDirectory);
            }
        }
    }

    public function delete($sessionUser)
    {
        if (!is_a($sessionUser, 'HM_At_Session_User_UserModel')) {
            $sessionUser = $this->getOne($this->find($sessionUser));
        }
        if ($sessionUser) {   
            $this->getService('State')->deleteBy(array('item_id = ?' => $sessionUser->session_user_id, 'process_type = ?' => HM_Process_ProcessModel::PROCESS_PROGRAMM_ASSESSMENT));
            
            if (count($collection = $this->getService('AtSessionEvent')->fetchAll(array('session_user_id = ?' => $sessionUser->session_user_id)))) {
                $sessionEventIds = $collection->getList('session_event_id');
                $this->getService('AtEvaluationMemoResult')->deleteBy(array('session_event_id IN (?)' => $sessionEventIds));
                $this->getService('AtSessionPair')->deleteBy(array('session_event_id IN (?)' => $sessionEventIds));
                $this->getService('AtSessionPairResult')->deleteBy(array('session_event_id IN (?)' => $sessionEventIds));
            }

            $this->getService('AtSessionEvent')->deleteBy(array('session_user_id = ?' => $sessionUser->session_user_id));
            $this->getService('AtEvaluationResults')->deleteBy(array('session_user_id = ?' => $sessionUser->session_user_id));
            $this->getService('AtEvaluationIndicator')->deleteBy(array('session_user_id = ?' => $sessionUser->session_user_id));
            $this->getService('AtSessionPairRating')->deleteBy(array('session_user_id = ?' => $sessionUser->session_user_id));
            $this->getService('AtSessionUserCriterionValue')->deleteBy(array('session_user_id = ?' => $sessionUser->session_user_id));
            
            return parent::delete($sessionUser->session_user_id);
        }
    }

    public function addComment($sessionUserId, $comment)
    {
        $this->update([
            'session_user_id' => $sessionUserId,
            'comment' => $comment
        ]);
    }


    /*
        public function _competenceData($sessionUser, $profile, $relationTypes)
        {
            $dataOut = array();

        $this->_sessionUser = $sessionUser;
        $this->_profile = $profile;
//        $this->_evaluation = $evaluation;
        $this->_session = $dataOut['session'] = $sessionUser->session->current();

        $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $this->_session->getOptionsModifier());

        $criteria = $this->getService('AtCriterion')->fetchAllDependence('CriterionIndicator', null, array('cluster_id', 'name'));
        $this->_indicatorsCache = array();
        $this->_criteriaCache = $criteria->getList('criterion_id', 'name');
        foreach ($criteria as $criterion) {
            if (count($criterion->indicators)) {
                $attr = $options['competenceUseIndicatorsDescriptions'] ? 'description_positive' : 'name';
                $this->_indicatorsCache[$criterion->criterion_id] = $criterion->indicators->getList('indicator_id', $attr);
            }
        }
        $results = $this->getService('AtEvaluation')->profileResultsByRelationType($this->_sessionUser, $options);
//pr($results);
//die();
        $data = $graphs = array();
        $colors = HM_At_Evaluation_Method_CompetenceModel::getRelationTypeColors();
        $titles = HM_At_Evaluation_Method_CompetenceModel::getRelationTypesShort();
        foreach ($relationTypes as $relationType) {
            $graphs[$relationType] = array(
                'legend' => ucfirst($titles[$relationType]),
                'color' => $colors[$relationType],
            );
        }
        if (count($results)) {
            foreach ($results as $criterionId => $result) {

                $criterionData = array(
                    'title' => $this->_criteriaCache[$criterionId],
                );
                foreach ($relationTypes as $relationType) {
                    $criterionData[$relationType] = $result['criterion'][$relationType];
                }
                $data[] = $criterionData;
            }

            if (in_array(HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN, $relationTypes) && $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN] === null) {
                $graphs[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN]['legend'] .= '<sup>*</sup>';
                $dataOut['footnotes'] = array('competences'=> _('Здесь и далее: результаты подчиненных включены в результаты коллег'));
            }
        }

        $dataOut['charts'] = array();
        $dataOut['charts']['competences'] = array(
            'graphs' => $graphs,
            'data' => $data,
        );
        $dataOut['competenceCriteria'] = $this->_criteriaCache;
        $dataOut['tables'] = array();
        foreach ($this->_criteriaCache as $criterionId => $criterionName) {

            $data = $table = array();
            if (!isset($results[$criterionId])) continue;

            $chartId = 'competence_criterion_' . $criterionId;

            $table = array(
                'head' => array(
                    'title' => _('Индикатор'),
            ));
            foreach ($relationTypes as $relationType) {
                $table['head'][$relationType] = ucfirst($titles[$relationType]);
            }

            if ($options['competenceUseIndicators']) {
                foreach ($results[$criterionId]['indicators'] as $indicatorId => $result) {
                    $row = array(
                        'title' => $this->_indicatorsCache[$criterionId][$indicatorId],
                    );
                    foreach ($relationTypes as $relationType) {
                        $row[$relationType] = $result[$relationType];
                    }
                    $table[] = $row;
                }
            }
            $row = array(
                'title' => array('class' => 'total', 'value' => $this->_criteriaCache[$criterionId]),
            );
            foreach ($relationTypes as $relationType) {
                $row[$relationType] = array('class' => 'total', 'value' => $results[$criterionId]['criterion'][$relationType]);
                $data[] = array(
                    'value' => $results[$criterionId]['criterion'][$relationType],
                    'title' => $titles[$relationType],
                    'color' => $colors[$relationType]
                );
            }
            $table[] = $row;

            $dataOut['tables'][$chartId]= $table;
            $dataOut['charts'][$chartId] = $data;
        }

        $top = $bottom = $topHidden = $bottomHidden = array();
        if (count($this->_profile->criteriaValues)) {
            $this->_profileCriterionValues = $this->_profile->criteriaValues->getList('criterion_id', 'value_id');
        }

        // здесь именно шкала оценки компетенций
        //$scaleId = $options['competenceScaleId'];
        $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId');
        foreach ($results as $criterionId => $result) {

            $plan = HM_Scale_Converter::getInstance()->id2value($this->_profileCriterionValues[$criterionId], $scaleId);
            if (($plan != HM_Scale_Value_ValueModel::VALUE_NA) && $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] > $plan) {
                $top[$this->_criteriaCache[$criterionId]] = sprintf('%s/%s', $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS], $plan);
            }

            $plan = $plan = HM_Scale_Converter::getInstance()->id2value($this->_profileCriterionValues[$criterionId], $scaleId);
            if (($plan != HM_Scale_Value_ValueModel::VALUE_NA) && $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] < $plan) {
                $bottom[$this->_criteriaCache[$criterionId]] = sprintf('%s/%s', $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS], $plan);
            }
            if (($delta = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS] - $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF]) >= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_HIDDEN_DELTA) {
                $topHidden[$this->_criteriaCache[$criterionId]] = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL];
            }
            if (($delta = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF] - $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS]) >= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_HIDDEN_DELTA) {
                $bottomHidden[$this->_criteriaCache[$criterionId]] = $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_ALL];
            }
        }

        if (!count($top)) $top[_('нет')] = '';
        if (!count($bottom)) $bottom[_('нет')] = '';
        if (!count($topHidden)) $topHidden[_('нет')] = '';
        if (!count($bottomHidden)) $bottomHidden[_('нет')] = '';

        $dataOut['lists'] = array();

        $dataOut['lists']['competence_top'] = $top;
        $dataOut['lists']['competence_bottom'] = $bottom;
        if ($this->_session->getType() == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
            $dataOut['lists']['competence_top_hidden'] = $topHidden;
            $dataOut['lists']['competence_bottom_hidden'] = $bottomHidden;
        }

        // сравнение с профилем успешности
        $dataOut['analyticsChartData'] = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getAnalyticsChartDataJs($this->_sessionUser->session_user_id, array(
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => 1,
        ));

        return $dataOut;
    }
*/

}