<?php
trait HM_Controller_Action_Trait_Session_Report
{
    protected $_sessionUser;
    protected $_criteriaCache = array();
    protected $_criteriaTestCache = array();
    protected $_criteriaPersonalCache = array();
    protected $_indicatorsCache = array();
    
    protected $_profile;
    protected $_position;
    protected $_cycle;
    
    public function init()
    {
        // эти контекстные меню нужны только рук-лю и только при регуляоной оценке 
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            if (!($this->_currentPosition = $this->getService('User')->isManager(false, true))) {
                $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page7'));
            }
            if (count($this->_session->vacancy) || count($this->_session->newcomer)) {
                $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page2'));
                $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page7'));
            }
        }           
        return parent::init();
    }
    
    protected function _userReport()
    {
        $this->view->setHeader(_('Индивидуальный отчет'));

        $sessionUser = $this->_sessionUser;

        $this->_position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence(array('Parent'), $sessionUser->position_id));
        $this->_profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_position->profile_id));
        $this->_cycle = $this->getService('Cycle')->getOne($this->getService('Cycle')->find($this->_session->cycle_id));

        if ($this->_position && count($this->_position->parent) && !is_null($this->_position->parent) ) {
            $positionName = $this->_position->parent->current()->name;
        }
        $this->view->lists['general'] = array(
            _('ФИО') => $sessionUser->user->current()->getName(),
            _('Подразделение') => $positionName,
            _('Должность') => $this->_position ? $this->_position->name . ($this->_position->is_manager ? ' (' . _('руководитель') . ')' : '') : $this->view->reportNoValue(),
            _('Профиль должности') => $this->_profile ? $this->_profile->name : $this->view->reportNoValue(),
        );

        $sessionBeginDate = new HM_Date($this->_session->begin_date);
        $sessionEndDate = new HM_Date($this->_session->end_date);
        
        $this->view->lists['session'] = [
            _('Оценочная сессия') => $this->_session->name,
            _('Оценочный период') => $this->_cycle ? $this->_cycle->name : '',
            _('Даты проведения оценки') => sprintf(_('c %s по %s'), $sessionBeginDate->toString('dd.MM.yyyy'), $sessionEndDate->toString('dd.MM.yyyy')),
            _('Дата подготовки отчета') => date('d.m.Y'),
        ];

        $this->view->scaleMaxValue = $this->getService('Scale')->getMaxValue(
            $this->getService('Option')->getOption('competenceScaleId')        
        );
        
        $methods = [];
        $params = ['sessionUser' => $this->_sessionUser, 'profile' => $this->_profile];
        if ($this->_profile && $programm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAllDependence('Event', [
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT,          
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,          
            'item_id = ?' => $this->_profile->profile_id,
            ]))) {
            if (count($programm->events)) {
                $evaluationIds = $programm->events->getList('ordr', 'item_id');
                ksort($evaluationIds);
                $evaluations = $this->getService('AtEvaluation')->fetchAll(['evaluation_type_id IN (?)' => $evaluationIds])->asArrayOfObjects();
                foreach ($evaluationIds as $evaluationId) {
                    $evaluation = $evaluations[$evaluationId];
                    if (!isset($methods[$evaluation->method])) {
                        if ($evaluation->method == HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE) {
                            // какие срезы выводить на круговых диаграммах
                            $params['relationTypes'] = [
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS,
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF,
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT,
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL,
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CLIENTS,
                                HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
                            ];
                        }
                        $params['evaluation'] = $evaluation;
                        $methods[$evaluation->method] = $params; // это совершенно не годится для произвольных form, (когда множество разных секций в отчёте имеют одинаковый method), если они здесь когда-нибудь появятся
                    }
                }

                // todo: Какие-то условия?
                $this->view->showCommentForm = true;
                if ($this->view->showCommentForm) {
                    $form = new HM_Form_ReportComment();
                    $form->setDefaults(['comment' => $this->_sessionUser->comment]);
                    $this->view->commentForm = $form;
                }
            }
        }        

        $this->view->texts['general'] = $this->_session->report_comment;
        $this->view->texts['competence_general'] = $this->getService('Option')->getOption('competenceReportComment');
        $this->view->texts['kpi_general'] = $this->getService('Option')->getOption('kpiReportComment');

        $this->view->sessionUser = $sessionUser;
        $this->view->methods = $methods;
    }
    
    protected function _userReportWord()
    {
        function AVG($array) {       
            $count = $summ = 0;
            foreach($array as $value) {
                if(isEmptyValue($value) || intval($value)<0) continue;  
                $summ += $value;
                $count++;
            }
            return $summ/$count;
        }
        function isEmptyValue($value) {       
            if(is_array($value)) {
                foreach($value as $v) {
                    if(!isEmptyValue($v)) return false;
                }                
                return true;
            }
            return !$value && !($value==='0' || $value===0);
        }
        function getOptions($avg) {       
            if(isEmptyValue($avg)) return null;//пустое значение - не красим ячейку
            return array('fill'=> $avg>=3.5 ?  '538135' : ($avg>=3 ? '92D050' : ($avg>=2 ? 'FFFF00' : 'FF0000')));
        }        

        $sessionUser = $this->_sessionUser;
        $sessionUserId = $sessionUser->session_user_id;
        $this->_position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence(array('Parent'), $sessionUser->position_id));
        $this->_profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_position->profile_id));
        $this->_cycle = $this->getService('Cycle')->getOne($this->getService('Cycle')->find($this->_session->cycle_id));

        if ($this->_position && count($this->_position->parent) && !is_null($this->_position->parent) ) {
            $positionName = $this->_position->parent->current()->name;
        }
        $sessionBeginDate = new HM_Date($this->_session->begin_date);
        $sessionEndDate = new HM_Date($this->_session->end_date);

        // ВАЖНО!!! т.к. мы не прошиваем кластеры и компетенции в базу, мы просто хардкодим связку названий и паттерна в DOCX-шаблоне
        // Если в дальнейшем мы внесем эти данные в дамп, можно будет убрать сопоставление и использовать связку ИД с паттерном шаблона, изменив шаблон, 
        // если ИД в базе отличаются от паттернов.
        // Хардкодим названия компетенций для связи их с шаблоном, совпадения ИД  - "случайны"
        $standardCompetencies = array(93=>'Готовность к изменениям', 94=>'Ориентация на достижение результата', 95=>'Ориентация на развитие', 96=>'Лидерство', 97=>'Перспективное мышление',98=>'Управленческая ответственность',99=>'Эффективное администрирование',100=>'Эффективное взаимодействие, влияние');
        // Хардкодим названия кластеров, совпадения ИД  - "случайны"
        $standardClusters = array(6=>'Управленческий профиль', 5=>'Мотивационный профиль');
        $realClusters2standard = array();
        $c = array_keys($standardClusters);
        $cluster1Id = array_shift($c);
        $cluster2Id = array_shift($c);
        //

        $stdExpertTypes = array(
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
        );
        $extExpertTypes = array(
        );

        //Получение данных по компетенциям и результатам оценки
        $optionsAt = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, HM_Option_OptionModel::MODIFIER_AT);        
        $competenceData = $this->getService('AtEvaluation')->profileResultsByRelationType($this->_sessionUser, $optionsAt+array('position'=>$sessionUser->position->current()));

        if(!$competenceData['results']) {
            $this->_flashMessenger->addMessage(array(
                 'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                 'message' => _('Нет данных для отчета!')
            ));
            $this->_redirector->gotoUrl($this->getRequest()->getHeader('referer'));
        }

        $competencesDB = $this->getService('AtCriterion')->fetchAllDependence('CriterionCluster', array('criterion_id IN (?)'=>array_keys($competenceData['results'])));

        //Формирование единого массива с компетенциями и результатами для дальнейшего разбора и вычислений
        $competences = array();
        foreach($competencesDB as $competence) {       
            $cluster_id = array_search($competence->cluster->current()->name, $standardClusters);
            $realClusters2standard[$competence->cluster->current()->cluster_id] = $cluster_id;
            $pattern_id = array_search($competence->name, $standardCompetencies);
            if(!$pattern_id) continue;
            $stdEvals = $parentEvals = array();
            foreach($competenceData['results'][$competence->criterion_id]['criterion'] as $role=>$score) {
                if(array_search($role, $stdExpertTypes)!==false) {
                    $stdEvals[$role] = $score;
                }
            }
            $competences[$pattern_id] = array('raw'=>$competence, 'evals'=>$stdEvals, 'parentEvals'=>$parentEvals, 'cluster_pattern'=>$cluster_id);
        }

        //ШАПКА
        $options = $data = array();
        $data['FIO'] = $sessionUser->user->current()->getName();
        $data['AGE'] = $sessionUser->user->current()->getMetadataValue('year_of_birth') ? '???': strip_tags($this->view->reportNoValue());
        $data['JOBPOSITION'] = $this->_position->name;
        $data['DEPARTMENT'] = $positionName;
        $data['EDUCATION'] = $sessionUser->user->current()->Information;
        if(strpos($data['EDUCATION'], 'tel~')!==false) {
            $data['EDUCATION'] = _('нет данных');
        }
        $data['STAGE'] = $this->_position->position_date ? HM_Date::getPeriodSinceDate($this->_position->position_date) : strip_tags($this->view->reportNoValue());
        $data['DATE'] = sprintf(_('c %s по %s'), $sessionBeginDate->toString('dd.MM.yyyy'), $sessionEndDate->toString('dd.MM.yyyy'));
        $data['GOAL'] = $this->_session->goal; // _('Регулярная оценка персонала');

        //ТАБЛИЦА 1
        $avgs = array();//для среднего всех средних
        foreach($competences as $competence_pattern=>$competence) {
            $evals = $competence['evals'];        
            $avg = AVG($evals);//array_sum($evals)/count($evals);
            $avgs[] = $avg;

            $data["A-{$competence_pattern}"] = round($avg, 1);

            //Сильные стороны (S)
            if($avg>=3) {
                $data["S-{$competence_pattern}"] = round($avg, 1);
                $options["S-{$competence_pattern}"] = getOptions($avg);
            } else {
                $data["S-{$competence_pattern}"] = '';
            }
            //Зоны развития (Z)
            if($avg<3) {
            $data["Z-{$competence_pattern}"] = round($avg, 1);
                $options["Z-{$competence_pattern}"] = getOptions($avg);
            } else {
                $data["Z-{$competence_pattern}"] = '';
            }
            //Разногласия (R)
            $eParent = $evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT];
            $eChildren = $evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN];
            $diffR = abs($eParent-$eChildren);
            if(isEmptyValue($eParent) || isEmptyValue($eChildren) || $diffR<1.5) {
                $data["R2-{$competence_pattern}"] = $data["R1-{$competence_pattern}"] = '';
            } else {
                $data["R1-{$competence_pattern}"] = $eChildren;
                $data["R2-{$competence_pattern}"] = $eParent;
                $options["R1-{$competence_pattern}"] = getOptions($eChildren);
                $options["R2-{$competence_pattern}"] = getOptions($eParent);
            }
            //Скрытые силы (H)
            $evalsOthers = $evals;
            unset($evalsOthers[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF]);
            $avgOthers = AVG($evalsOthers);//array_sum($evalsOthers)/count($evalsOthers);
            $diffH = $evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF] - $avgOthers;
            if($diffH<=-1.5) {
                $data["H1-{$competence_pattern}"] = $evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF];
                $data["H2-{$competence_pattern}"] = round($avgOthers, 1);
                $options["H1-{$competence_pattern}"] = getOptions($evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF]);
                $options["H2-{$competence_pattern}"] = getOptions($avgOthers);
            } else {
                $data["H2-{$competence_pattern}"] = $data["H1-{$competence_pattern}"] = '';
            }
        }
        $data["AVG"] = round(AVG($avgs), 1);//array_sum($avgs)/count($avgs)

        //ТАБЛИЦА СЛАБЫЕ-СИЛЬНЫЕ
        $dataByClusters = array();
        //ищем лучшие-худшие
        foreach($competences as $competence_pattern=>$competence) {
            if(!$competence['cluster_pattern']) continue;
            if(!isset($dataByClusters[$competence['cluster_pattern']])) {
                $dataByClusters[$competence['cluster_pattern']] = array('min_ball'=>9999, 'max_ball'=>-1, 'min_count'=>0, 'max_count'=>0, 'max_object'=>array(), 'min_object'=>array());
            }
            $evals = $competence['evals'];        
            $avg = AVG($evals);//array_sum($evals)/count($evals);//ср оц по комп
            $d = &$dataByClusters[$competence['cluster_pattern']];
            if($avg < $d['min_ball']) {
                $d['min_ball'] = $avg;
                $d['min_object'] = array($competence['raw']);
            }
            if($avg > $d['max_ball']) {
                $d['max_ball'] = $avg;
                $d['max_object'] = array($competence['raw']);
            }
        }

        //если оценки по компетенциям равны (нет единственной лучшей или единственной худшей - выводим все лучшие и все худшие)
        //пока лучших и худших по-одному, а здесь будем искать равные по оценке и добавлять
        $minCount = $maxCount = array();//0;
        foreach($competences as $competence_pattern=>$competence) {
            if(!$competence['cluster_pattern']) continue;
            $evals = $competence['evals'];        
            $avg = AVG($evals);//array_sum($evals)/count($evals);//ср оц по комп

            $d = &$dataByClusters[$competence['cluster_pattern']];
            if($avg==$d['max_ball']) {
                if(!(count($d['max_object'])==1 && $d['max_object'][0]->criterion_id==$competence['raw']->criterion_id)) {//чтобы не добавить компетенцию повторно
                    $d['max_object'][] = $competence['raw'];
                }
                $d['max_count']++;
            }
            if($avg==$d['min_ball']) { 
                if(!(count($d['min_object'])==1 && $d['min_object'][0]->criterion_id==$competence['raw']->criterion_id)) {//чтобы не добавить компетенцию повторно
                    $d['min_object'][] = $competence['raw'];
                }
                $d['min_count']++;
            }
        }
        //Собираем инфу для вывода Компетенций и наиболее частых ответов по индикаторам
        foreach($dataByClusters as $cluster_pattern=>$clusterData) {

            $data["table_{$cluster_pattern}p"] = array();
            foreach($clusterData['max_object'] as $competence) {
                $data["table_{$cluster_pattern}p"][] = array('text'=>$competence->name);
                $indicatorsText = array();
                foreach($competenceData['results'][$competence->criterion_id]['indicators'] as $indicatorId=>$evals) {
                    $indicatorAvg = AVG($evals);
                    $valueId = HM_Scale_Converter::getInstance()->value2idExt($indicatorAvg, $optionsAt['competenceScaleId']);
                    $indicatorsText[] = '• '.$this->getService('AtCriterionIndicatorScaleValue')->fetchAll(array('value_id = ?'=>$valueId, 'indicator_id = ?'=>$indicatorId))->current()->description;
                }
                $data["table_{$cluster_pattern}p"][] = array('text'=>implode("\n", $indicatorsText));
            }
            $data["table_{$cluster_pattern}m"] = array();
            foreach($clusterData['min_object'] as $competence) {
                $data["table_{$cluster_pattern}m"][] = array('text'=>$competence->name);
                $indicatorsText = array();
                foreach($competenceData['results'][$competence->criterion_id]['indicators'] as $indicatorId=>$evals) {
                    $indicatorAvg = AVG($evals);
                    $valueId = HM_Scale_Converter::getInstance()->value2idExt($indicatorAvg, $optionsAt['competenceScaleId']);
                    $indicatorsText[] = '• '.$this->getService('AtCriterionIndicatorScaleValue')->fetchAll(array('value_id = ?'=>$valueId, 'indicator_id = ?'=>$indicatorId))->current()->description;
                }
                $data["table_{$cluster_pattern}m"][] = array('text'=>implode("\n", $indicatorsText));
            }

/*
            $data["{$cluster_pattern}+C"] = $clusterData['max_count']==1 ? ($clusterData['max_object']->name." (".round($clusterData['max_ball'], 1).")") : $blank;
            $data["{$cluster_pattern}+D"] = $clusterData['max_count']==1 ? $clusterData['max_object']->description : '';
            $data["{$cluster_pattern}-C"] = $clusterData['min_count']==1 ? ($clusterData['min_object']->name." (".round($clusterData['min_ball'], 1).")") : $blank;
            $data["{$cluster_pattern}-D"] = $clusterData['min_count']==1 ? $clusterData['min_object']->description : '';
*/
        }
//pr($data);
//die();
/*
$data['table_6p'] = array(array('text'=>"6a\n123"), array('text'=>'123'), array('text'=>'123'));
$data['table_6m'] = array(array('text'=>'6b123'), array('text'=>'123'), array('text'=>'123'));
$data['table_5p'] = array(array('text'=>'5a123'), array('text'=>'123'), array('text'=>'123'));
$data['table_5m'] = array(array('text'=>'5b123'), array('text'=>'123'), array('text'=>'123'));
*/
/*
$data['table6+'] = array('6+_a','6+_b','6+_c');
$data['table6-'] = array('6-_a','6-_b','6-_c');
$data['table5+'] = array('5+_a','5+_b','5+_c');
$data['table5-'] = array('5-_a','5-_b','5-_c');
*/
//pr($data);

//die();
        //ТАБЛИЦА 2
        $avgs = array();
        foreach($competences as $competence_pattern=>$competence) {
            $evals = $competence['evals'];        
            foreach($stdExpertTypes as $type) {
                $data["{$type}-{$competence_pattern}"] = isset($evals[$type]) ? $evals[$type] : '-';
                if(!isset($avgs[$type])) {
                    $avgs[$type] = array($evals[$type]);
                } else {
                    $avgs[$type][] = $evals[$type];
                }
            }
            $data["{$competence_pattern}"] = round(AVG($evals), 1);//array_sum($evals)/count($evals)
        }                                            
        $totalAvgs = array();
        foreach($avgs as $type=>$avg) {
            if(isEmptyValue($avg)) continue;
            $columnAvg = AVG($avg);//array_sum($avg)/count($avg);
            $totalAvgs[] = $columnAvg;
            $data["{$type}"] = round($columnAvg, 1);
            $options["{$type}"] = getOptions($columnAvg);
        }
        $data["AVG2"] = round(AVG($totalAvgs), 1);//array_sum($totalAvgs)/count($totalAvgs)
        $options["AVG2"] = getOptions(AVG($totalAvgs));

        //ТАБЛИЦЫ 3&4
        $avgsCol1 = $avgsCol2 = $avgsRow1 = $avgsRow2 = array();
        foreach($competences as $competence_pattern=>$competence) {
            $evals = $competence['evals'];             
//            unset($evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT]);
            $evalsCommon = $evals+$competence['parentEvals'];        
            foreach(array_merge($stdExpertTypes, $extExpertTypes)  as $type) {

                if(!$evalsCommon[$type]) continue;
                // Если простые данные мы заполняем кучей, сразу для обоих таблиц, то такой номер не пройдет для расчета средних - там надо делить на 2 части
                $data["{$competence_pattern}-{$type}"] = isset($evalsCommon[$type]) ? $evalsCommon[$type] : '-';

                if($competence['cluster_pattern']==$cluster1Id) {
                    if(!isset($avgsCol1[$type])) {
                        $avgsCol1[$type] = array($evalsCommon[$type]);
                    } else {
                        $avgsCol1[$type][] = $evalsCommon[$type];
                    }
                    if(!isset($avgsRow1[$competence_pattern])) {
                        $avgsRow1[$competence_pattern] = array($evalsCommon[$type]);
                    } else {
                        $avgsRow1[$competence_pattern][] = $evalsCommon[$type];
                    }
                }
                if($competence['cluster_pattern']==$cluster2Id) {
                    if(!isset($avgsCol2[$type])) {
                        $avgsCol2[$type] = array($evalsCommon[$type]);
                    } else {
                        $avgsCol2[$type][] = $evalsCommon[$type];
                    }
                    if(!isset($avgsRow2[$competence_pattern])) {
                        $avgsRow2[$competence_pattern] = array($evalsCommon[$type]);
                    } else {
                        $avgsRow2[$competence_pattern][] = $evalsCommon[$type];
                    }
                }
            }
        }
        //таб3 правая колонка
        foreach($avgsRow1 as $competence_pattern=>$avg) {
            $columnAvg = AVG($avg);//array_sum($avg)/count($avg);
            $data["T3-{$competence_pattern}"] = round($columnAvg, 1);
        }
        //таб4 правая колонка
        foreach($avgsRow2 as $competence_pattern=>$avg) {
            $columnAvg = AVG($avg);//array_sum($avg)/count($avg);
            $data["T4-{$competence_pattern}"] = round($columnAvg, 1);
        }
        //таб3 посл строка
        $totalAvgs = array();
        foreach($avgsCol1 as $type=>$avg) {
            $columnAvg = AVG($avg);//array_sum($avg)/count($avg);
            $totalAvgs[] = $columnAvg;
            $data["T3-{$type}"] = round($columnAvg, 1);
        }
        $data["AVG3"] = round(AVG($totalAvgs), 1);//array_sum($totalAvgs)/count($totalAvgs)
        //таб4 посл строка
        $totalAvgs = array();
        foreach($avgsCol2 as $type=>$avg) {
            $columnAvg = AVG($avg);//array_sum($avg)/count($avg);
            $totalAvgs[] = $columnAvg;
            $data["T4-{$type}"] = round($columnAvg, 1);
        }
        $data["AVG4"] = round(AVG($totalAvgs), 1);//array_sum($totalAvgs)/count($totalAvgs)

        //ТАБЛИЦА 5
        $memos = $competenceData['memos'];
        $diffLen = count($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG]) - count($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS]);
        $maxLen = max(count($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG]), count($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS]));
        if($diffLen>0) {
            $memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS] = array_pad($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS], $maxLen, '');
        }
        if($diffLen<0) {
            $memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG] = array_pad($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG], $maxLen, '');
        }
        $data["table_5"] = array();
        foreach($memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG] as $i=>$value) {
            if(!trim($value)) continue;
            $data["table_5"][] = array(HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_STRONG=>$value, HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS=>$memos[HM_At_Evaluation_Memo_MemoModel::MEMO_TYPE_NEEDPROGRESS][$i]);
        }


        $files = $this->_makeDiagramms($this->view->url(array('module' => 'session', 'controller' => 'report', 'action' => 'get-static-diagramms', 'session_id' => $this->_session->session_id, 'session_user_id' => $sessionUserId, 'print'=>1)));
        $filesMapping = array();//мапим кластера
        foreach($files as $clusterId=>$file) {
            $clusterId = explode('_', $clusterId);
            $clusterId[1] = $realClusters2standard[$clusterId[1]];
            $clusterId = implode('_', $clusterId);
            $filesMapping[$clusterId] = $file;
            $data["IMG_{$clusterId}"] = $this->_wrapImage($clusterId, $file['size']);
        }


        $data['IMG_PHOTO'] = _("Место для\nцветной фотографии");
        $user = $this->getService('User')->find($sessionUser->user_id)->current();
        $photo = $user->getRealPhoto();
        if($photo) {
            $size = getimagesize($photo);
            $data['IMG_PHOTO'] = $this->_wrapImage('PHOTO', array('W'=>192, 'H'=>192*($size[1]/$size[0])));
            $filesMapping['PHOTO'] = array('filename'=>basename($photo), 'data'=>file_get_contents($photo));
        }        
//

        //строим отчет!
        $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, HM_PrintForm::FORM_INDIVIDUAL_REPORT, $data, "individual_report_{$sessionUserId}", $options, true, $filesMapping);
    }


    protected function _wrapImage($id, $size)
    {
        $W = intval($size['W']*0.7*10000);//эмпирически определили к-т
        $H = intval($size['H']*0.7*10000);

        return <<< EOD

<w:drawing>
    <wp:inline distT="0" distB="0" distL="0" distR="0">
        <wp:extent cx="{$W}" cy="{$H}"/>
        <wp:effectExtent l="0" t="0" r="1905" b="5080"/>
        <wp:docPr id="77" name="Рисунок 77"/>
        <wp:cNvGraphicFramePr>
            <a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/>
        </wp:cNvGraphicFramePr>
        <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
            <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
                <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
                    <pic:nvPicPr>
                        <pic:cNvPr id="0" name="Picture 3"/>
                        <pic:cNvPicPr>
                            <a:picLocks noChangeAspect="1" noChangeArrowheads="1"/>
                        </pic:cNvPicPr>
                    </pic:nvPicPr>
                    <pic:blipFill>
                        <a:blip r:embed="{$id}" cstate="print">
                            <a:extLst>
                                <a:ext uri="{28A0092B-C50C-407E-A947-70E740481C1C}">
                                    <a14:useLocalDpi xmlns:a14="http://schemas.microsoft.com/office/drawing/2010/main" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:w15="http://schemas.microsoft.com/office/word/2012/wordml" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns="" val="0"/>
                                </a:ext>
                            </a:extLst>
                        </a:blip>
                        <a:srcRect/>
                        <a:stretch>
                            <a:fillRect/>
                        </a:stretch>
                    </pic:blipFill>
                    <pic:spPr bwMode="auto">
                        <a:xfrm>
                            <a:off x="0" y="0"/>
                            <a:ext cx="{$W}" cy="{$H}"/>
                        </a:xfrm>
                        <a:prstGeom prst="rect">
                            <a:avLst/>
                        </a:prstGeom>
                        <a:noFill/>
                    </pic:spPr>
                </pic:pic>
            </a:graphicData>
        </a:graphic>
    </wp:inline>
</w:drawing>

EOD
;
    }

    protected function _makeDiagramms($source)
    {
        $return = array();

        $phantomOutFile = tempnam(sys_get_temp_dir(), 'pha'); // good 
        $phantomjs = Zend_Registry::get('config')->phantomjs->app;
        $phantomjsScript = Zend_Registry::get('config')->phantomjs->scriptword;
        $linux = Zend_Registry::get('config')->phantomjs->linux;
        // phantomJS иногда зависает собака
        $timeout = 15; // 5 секунд - мало, файл не успевает построиться !!!
        $timeoutAdd = 5; // добавление к таймауту на каждой неудачной итерации
        $attempts = 5;
        $url = (Zend_Registry::get('config')->phantomjs->protocol_domain?Zend_Registry::get('config')->phantomjs->protocol_domain:Zend_Registry::get('view')->serverUrl()) . $source;
        $i = 0;
        do {
            $cmd = $phantomjs.' '.$phantomjsScript.' ' . $url . ' '. ($linux?" 2>/dev/null 1>{$phantomOutFile} & WPID=\$! && sleep {$timeout} && kill \$WPID":"1>{$phantomOutFile}");
            $ret = system($cmd, $result);

            $timeout += $timeoutAdd; // Увеличиваем таймаут
        } while ($result && (++$i < $attempts));
        if($result || !file_exists($phantomOutFile) || !filesize($phantomOutFile)) { 
            die('phantom error!');
        }

        $data = file_get_contents($phantomOutFile);

        unlink($phantomOutFile);

        if(strpos($data, "%][%")===false) { 
            return array();
        }

        $diagramms = explode("%][%", $data);

        $clusters = array();
        foreach($diagramms as $diagramm) {
            $r = preg_match("/(cluster_(\d+))\]/i", $diagramm, $m);
            $clusterId = $m[1];
            $r = preg_match_all("/<svg(.*?)<\/svg>/i", $diagramm, $m);

            if(!isset($clusters[$clusterId])) {
                $clusters[$clusterId] = array();
            }
            $clusters[$clusterId] = array_merge($clusters[$clusterId], $m[0]);
        }

        foreach($clusters as $clusterId=>$cluster) {
          foreach($cluster as $i=>$svg) {

            //ширина и высота(в особенности) - это костылики, SVG дает слишком большие и неправильные, непропорциональные, потому пишем свои, 
            //но это совсем не универсально - заточка конкретно под наш диаграммы нашего отчет 
            $width = '900px';                       //Соответствует параметрам в /library/phantomjs/html.js
            $height = $i%2==0 ? '600px':'110px';    //а тут - покороче обрезаем по высоте, такие танцы...
            //

            $pos = strpos($svg, 'style="');
            if($pos) {
                $pos += 7;
                $style = substr($svg, $pos, strpos($svg, '"', $pos)-$pos);
                $style = explode(';', str_replace(' ', '', $style));
                foreach($style as $param) {
                    $param = explode(':', $param);
                    switch($param[0])  {
//                        case 'width': $width = $param[1]; break;
//                        case 'height': $height = $param[1]; break;//чтоб легенда не тянулась на страницу
                    }
                }            
            } 

            $svg = str_ireplace("<svg", "<svg width=\"{$width}\" height=\"{$height}\"", $svg);

//IMAGICK section
            try{
                $image = new IMagick();
                $image->readImageBlob('<?xml version="1.0" encoding="UTF-8" standalone="no"?>'.$svg);
                $image->setImageFormat("png32");
                $return["{$clusterId}_{$i}"] = array('filename'=>"{$clusterId}_{$i}.png", 'data'=>$image->getImageBlob(), 'size'=>array('W'=>intval($width), 'H'=>intval($height)));
            } catch(Exception $e) {
                die($e->getMessage());
            }
          }
        }

        return $return;
    }

    protected function _userAnalyticsReport($submit = false)
    {
        $form = new HM_Form_Analytics();

        $this->view->setHeader(_('Анализ результатов'));

        $sessionUser = $this->_sessionUser;

        $this->view->sessionUser = $this->_sessionUser;
        $this->view->status = $sessionUser->status;
        $this->view->setSubHeader($sessionUser->user->current()->getName());

        // сравнение с профилем успешности
        if ($submit) {
            $response = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getAnalyticsChartDataJs(
                $this->_sessionUser->session_user_id,
                array(
                    HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => $this->getRequest()->getParam('user'),
                    HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => $this->getRequest()->getParam('profile'),
                    HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS => explode(',', $this->getRequest()->getParam('sessions')),
                    HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION => $this->getRequest()->getParam('position'),
                )
            );

            if (key_exists('sessions', $response['graphs'])) {
                foreach ($response['data'][0] as $key => $value) {
                    if ($key != 'title' && !key_exists($key, $response['graphs'])) {
                        $response['graphs'][$key] = array(
                            'legend' => 'Профиль сотрудника по итогам прошлой оценочной сессии',
                            'color'  => sprintf('#%06X', mt_rand(0, 0xFFFFFF)) // random color
                        );
                    }
                }

                unset($response['graphs']['sessions']);
            }

            $this->view->analyticsChartData = $response;
            $sessionsIds = explode(',', $this->getRequest()->getParam('sessions'));
            $sessions = Zend_Registry::get('serviceContainer')->getService('AtSession')->fetchAll(array(
                'session_id IN (?)' => count($sessionsIds) ? $sessionsIds : array(0)
            ))->getList('session_id', 'name');

            $form->setDefaults(array(
                'session_user_id' => $sessionUser->session_user_id,
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => $this->getRequest()->getParam('user'),
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => $this->getRequest()->getParam('profile'),
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS => $this->getRequest()->getParam('sessions'),
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION => $this->getRequest()->getParam('position'),

            ));
            $this->view->form = $form;
        } else {
            $analyticsChartData = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getAnalyticsChartDataJs(
                $this->_sessionUser->session_user_id,
                array(
                    HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
                    HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => 1,
                )
            );

            $this->view->analyticsChartData = $analyticsChartData;
        }
    }

    protected function _userDevelopment()
    {
        $form = new HM_Form_Development();

        $this->view->setSubSubHeader(_('Развитие работника'));

        $sessionUser = $this->_sessionUser;

        $this->view->sessionUser = $this->_sessionUser;
        $this->view->status = $sessionUser->status;
        $this->view->setHeader($sessionUser->user->current()->getName());
        $this->view->form = $form;

        $analyticsChartData = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getAnalyticsChartDataJs(
            $this->_sessionUser->session_user_id,
            array(
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
                HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => 1,
            )
        );

        $subjectsByCompetences = array();

        foreach ($analyticsChartData['data'] as $datum) {
            if ((int) $datum['profile'] > (int) $datum['user']) {
                $criterionName = str_replace("\n", " ", $datum['title'] );
                $criterion = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->fetchAll(array(
                    'name = ?' => $criterionName
                ));
                if (count($criterion)) {
                    $criterion = $criterion->current();
                    $subjectsCriteria = Zend_Registry::get('serviceContainer')->getService('SubjectCriteria')->fetchAllDependence('Subject', array(
                        'criterion_id = ?' => $criterion->criterion_id,
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE
                    ));
                    if (count($subjectsCriteria)) {
                        foreach ($subjectsCriteria as $subjectsCriterion) {
                            $subject = $subjectsCriterion->subject;
                            if (count($subject)) {
                                $subject = $subject->current();
                                $subjectsByCompetences[$datum['title']]['subjects'][$subject->subid] = $subject->name;
                                $comment = '';
                                $graduated = Zend_Registry::get('serviceContainer')->getService('Graduated')->fetchAll(array(
                                    'MID = ?' => $sessionUser->user_id,
                                    'CID = ?' => $subject->subid
                                ));
                                if (count($graduated)) {
                                    $graduated = $graduated->current();
                                    $from = date('d.m.Y', strtotime($graduated->begin));
                                    $to = date('d.m.Y', strtotime($graduated->end));
                                    $comment = _('проходил(а)') . " с {$from} по {$to}";
                                }

                                $student = Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(array(
                                    'MID = ?' => $sessionUser->user_id,
                                    'CID = ?' => $subject->subid
                                ));
                                if (count($student)) {
                                    $student = $student->current();
                                    $from = date('d.m.Y', strtotime($student->begin_personal));
                                    $to = date('d.m.Y', strtotime($student->end_personal));
                                    $comment = _('проходит') . " с {$from}" . _('по настоящее время');
                                }

                                $claimant = Zend_Registry::get('serviceContainer')->getService('Claimant')->fetchAll(array(
                                    'MID = ?' => $sessionUser->user_id,
                                    'CID = ?' => $subject->subid,
                                ));
                                switch ($claimant->status) {
                                    case HM_Role_ClaimantModel::STATUS_NEW:
                                        $status = _('На рассмотрении');
                                        break;
                                    case HM_Role_ClaimantModel::STATUS_REJECTED:
                                        $status = _('Отклонена');
                                        break;
                                    case HM_Role_ClaimantModel::STATUS_ACCEPTED:
                                        $status = _('Одобрена');
                                        break;
                                }
                                if (count($claimant)) {
                                    $claimant = $claimant->current();
                                    $from = date('d.m.Y', strtotime($claimant->begin));
                                    $comment = _('подана заявка') . " {$from} " . _('Статус:') . ' ' . $status;
                                }

                                $subjectsByCompetences[$datum['title']]['comments'][$subject->subid] = $comment;
                            }
                        }
                    }
                }
            }
        }

        $this->view->analyticsChartData = $analyticsChartData;
        $this->view->subjectsByCompetences = $subjectsByCompetences;
    }

    protected function _userAnalyticsReportOld()
    {
        $form = new HM_Form_Analytics();

        $this->view->setHeader(_('Анализ результатов'));

        $sessionUser = $this->_sessionUser;

        $this->view->status = $sessionUser->status;
        $this->view->setSubHeader($sessionUser->user->current()->getName());
        $this->view->form = $form;
        $this->view->params = array(
            'module' => 'session',
            'controller' => 'report-chart',
            'session_user_id' => $sessionUser->session_user_id,
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER => 1,
            HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE => 1,
        );
    }
    
    protected function _eventCompetence($event)
    {
        $user = $event->user->current();
        $respondent = $event->respondent->current();
        $session = $event->session->current();
        $sessionUser = $event->sessionUser->current();
        if (count($event->sessionRespondent)) $sessionRespondent = $event->sessionRespondent->current();
        $this->_cycle = $this->_session->cycle ? $this->_session->cycle->current()->name : '';
        
        $evaluationType = $this->getService('AtEvaluation')->findDependence('EvaluationMemo', $event->evaluation_id)->current();
        $memosCache = count($evaluationType->evaluation_memo) ? $evaluationType->evaluation_memo->getList('evaluation_memo_id', 'name') : array();

        $criteria = $this->getService('AtCriterion')->fetchAllDependence('CriterionIndicator', null, array('cluster_id', 'name'));
        $this->_criteriaCache = $criteria->getList('criterion_id', 'name');        
        foreach ($criteria as $criterion) {
            if (count($criterion->indicators)) {
                $attr = $this->getService('Option')->getOption('competenceUseIndicatorsDescriptions') ? 'description_positive' : 'name';
                $this->_indicatorsCache[$criterion->criterion_id] = $criterion->indicators->getList('indicator_id', $attr);
            }
        }        
        
        if ($collection = $this->getService('Orgstructure')->findDependence(array('Parent', 'Profile'), $sessionUser->position_id)) {
            $this->_positionUser = $collection->current();
        }
        if ($collection = $this->getService('Orgstructure')->findDependence(array('Parent'), $sessionRespondent->position_id)) {
            $this->_positionRespondent = $collection->current();
        }
        if ($this->_positionUser->profile) {
            $this->_profile = $this->_positionUser->profile->current();
        }

        $department = count($this->_positionUser->parent) ? $this->_positionUser->parent->current()->name : '';
        $this->view->lists['general-user'] = array(
            _('ФИО') => $user->getName(),
            _('Подразделение') => $department,
            _('Должность') => $this->_positionUser->name . ($this->_positionUser->is_manager ? ' (' . _('рук.') . ')' : ''),
            _('Профиль должности') => $this->_profile->name,
        );

        $department = count($this->_positionRespondent->parent) ? $this->_positionRespondent->parent->current()->name : '';
        $this->view->lists['general-respondent'] = array(
            _('ФИО') => $respondent->getName(),
            _('Подразделение') => $department,
            _('Должность') => $this->_positionRespondent->name . ($this->_positionUser->is_manager ? ' (' . _('рук.') . ')' : ''),
        );

        $eventFillDate = new HM_Date($event->date_filled);
        $this->view->lists['general'] = array(
            _('Оценочная сессия') => $this->_session->name,
            _('Оценочный период') => $this->_cycle,
            _('Дата заполнения анкеты') => $eventFillDate->toString('dd.MM.yyyy'),
        );

        
        /****** radar ******/
        
        $data = $graphs = array();
        $colors = HM_At_Evaluation_Method_CompetenceModel::getRelationTypeColors();
        $titles = HM_At_Evaluation_Method_CompetenceModel::getRelationTypesShort();
        $graphs[$evaluationType->relation_type] = array(
            'legend' => ucfirst($titles[$evaluationType->relation_type]),
            'color' => $colors[$evaluationType->relation_type],
        );        

        $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $session->getOptionsModifier());
        foreach ($event->evaluationResults as $result) {

            $value = HM_Scale_Converter::getInstance()->id2value($result->value_id, $options['competenceScaleId']);
            if ($value == HM_Scale_Value_ValueModel::VALUE_NA) continue;

            $data[] = array(
                'title' => $this->_criteriaCache[$result->criterion_id],
                $evaluationType->relation_type => $value,
            );

            if ($value >= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_TOP_COMPETENCES) {
                $top[$this->_criteriaCache[$result->criterion_id]] = $value;
            }
            if ($value <= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_BOTTOM_COMPETENCES) {
                $bottom[$this->_criteriaCache[$result->criterion_id]] = $value;
            }
        }
        $this->view->charts['criteria'] = array(
            'graphs' => $graphs,
            'data' => $data,
        );

        if ($this->getService('Option')->getOption('competenceUseIndicators')) {

            $this->view->competenceCriteria = $this->_criteriaCache;
            if (count($event->evaluationIndicators)) {
                foreach ($event->evaluationIndicators as $result) {
                    $listId = 'criterion_' . $result->criterion_id;
    
                    $value = HM_Scale_Converter::getInstance()->id2value($result->value_id, $evaluationType->scale_id);
                    if ($value == HM_Scale_Value_ValueModel::VALUE_NA) continue;
    
                    $this->view->lists[$listId][$this->_indicatorsCache[$result->criterion_id][$result->indicator_id]] = $value;
                }
            }
        }

        $this->view->competenceMemos = $memosCache;
        if (count($event->evaluationMemoResults)) {
            foreach ($event->evaluationMemoResults as $memoResult) {
                $textId = 'memo_' . $memoResult->evaluation_memo_id;
                $this->view->texts[$textId] = nl2br($memoResult->value);
            }
        }

        $this->view->scaleMaxValue = $this->getService('Scale')->getMaxValue($options['competenceScaleId']);
        
        if (!count($top)) $top[_('нет')] = '';
        if (!count($bottom)) $bottom[_('нет')] = '';

        $this->view->lists['top'] = $top;
        $this->view->lists['bottom'] = $bottom;

        $this->view->status = $event->status;

        $atMemoResults = $this->getService('AtEvaluationMemoResult')->fetchAll(
            $this->getService('AtEvaluationMemoResult')->quoteInto("session_event_id = ?", $event->session_event_id),
            array('evaluation_memo_id')
        )->getList('evaluation_memo_id', 'value');

        $atMemos = $this->getService('AtEvaluationMemo')->fetchAll()->getList('evaluation_memo_id', 'name');

        $this->view->atMemoResults = array();
        foreach ($atMemoResults as $key => $value)   {
            $this->view->atMemoResults[ $atMemos[$key] ] = $value;
        }



    }
    
    protected function _eventQuest($event)
    {
        $questAttempts = $this->getService('QuestAttempt')->fetchAll(array(
            'context_event_id = ?' => $event->session_event_id,        
            'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT,        
            'is_resultative = ?' => 1,        
        ));
        if (count($questAttempts)) {
            $questAttempt = $questAttempts->current();
            $url = $this->view->url(array(
                'module' => 'quest', 
                'controller' => 'report', 
                'action' => 'attempt', 
                'session_id' => $event->session_id,        
                'attempt_id' => $questAttempt->attempt_id,        
                'baseUrl' => '',        
            ));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        }
    }
    
    private function _sortByRating($row1, $row2)
    {
        return ($row1['total'] > $row2['total']) ? -1 : 1;            
    }
    
    static public function _getPairCompareClass($value)
    {
        if ($value >= HM_At_Session_Pair_Rating_RatingModel::RATIO_THRESHOLD_HI) {
            return 'ratio-hi';
        } elseif ($value < HM_At_Session_Pair_Rating_RatingModel::RATIO_THRESHOLD_LO) {
            return 'ratio-lo';
        } 
        return 'ratio-me';
    }    
}