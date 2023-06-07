<?php

/**
 * Сервис для поддержки процессов
 * Здесь не должно быть никакой процессной логики, только работа с базой
 *
 */
class HM_Process_ProcessService extends HM_Service_Abstract
{
    protected $_processes = null;

    protected function _getProcessesConfig()
    {
        if($this->_processes == null){
            $this->_processes = new HM_Config_Xml(APPLICATION_PATH . '/settings/processes.xml');
        }
        return $this->_processes;
    }

    public function getStaticProcess($type)
    {
        $conf = $this->_getProcessesConfig();
        foreach ($conf as $val) {
            if ($val->type == $type) {
                return HM_Process_ProcessModel::factory($val->toArray());
            }
        }
        return false;
    }

    // ???
    public function getProccessTypes()
    {
        $conf = $this->getProcessesConfig();
        $res = array();

        foreach($conf as $val){
            $res[$val->type] = _($val->name);
        }
        return $res;
    }

    /**
     * Инициализирует процесс данными из базы (о его текущем состоянии)
     * Например, перед показом в workflowBulbs
     * 
     * @param unknown_type $model
     * @return boolean
     */
    public function initProcess($model)
    {
        if ($model) {
            $process = $model->getProcess();
            if ($processAbstract = $process->getProcessAbstract()) {

                $currentStateModel = $this->getService('State')->getCurrentState(
                    $process->getType(),
                    $model->getPrimaryKey(),
                    $processAbstract->isStatic() ? '' : $processAbstract->process_id
                );

                if ($currentStateModel) {
                    if ($processAbstract->isStrict()) {
                        $process->initState($currentStateModel);
                    } else {
                        $process->initPassedStates($currentStateModel);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Выполняет переход на 1-й шаг процесса (сохраняет в базе инормацию о 1-м шаге);
     * перед start'ом процесс не обязательно инициализировать (initProcess);
     * для динамических процессов важно, чтобы абстрактный процесс уже существовал в базе (в таблице processes);
     * в initParams можно передать данные моделей внутрь State; других способов пока не выявлено
     * 
     * @todo: в этом случае не обрабатывается isImpossible
     */  
    public function startProcess($model, $params = array(), $automatic = false)
    {
        $process = $model->getProcess();
        /** @var HM_Process_ProcessModel $processAbstract */
        $processAbstract = $process->getProcessAbstract();
        
        $process->onProcessStart();

        /** @var HM_State_Data_StateDataService $stateDataService */
        $stateDataService = $this->getService('StateData');

        if ($chain = $processAbstract->getChain()) {

            $firstClass = key($chain);
            $stateModel = $this->getService('State')->insert(
                array(
                    'item_id' => $model->getPrimaryKey(),
                    'process_id' => $processAbstract->process_id, // для статичных оно равно 0
                    'process_type' => $process->getType(),
                    'current_state' => $currentClass = key($chain),
                    'status' => HM_Process_Abstract::PROCESS_STATUS_INIT,
                    'params' => serialize($params)
                )
            );

            $statesData = array();
            $beginDate = $process->getBeginDate();

            if ($processAbstract->isStatic()) {

                $stepsDefinitions = array();
                if ($processAbstract->states['state']) {
                    foreach ($processAbstract->states['state'] as $definition) {
                        $stepsDefinitions[$definition['class']] = $process->getDatesFromDefinition($definition);
                    }
                }

                if (count($stepsDefinitions)) {
                    foreach ($stepsDefinitions as $class => $definition) {

                        list($beginEventDate, $endEventDate) = $this->getStepRelativeDates($model, $definition);

                        // DEPRECATED! Не используйте эту таблицу
                        // $this->getService('ProcessStep')->insert($data);

                        // наполняем state_of_process_data сразу всеми этапами
                        $statesData[] = array(
                            'state_of_process_id' => $stateModel->state_of_process_id,
                            'state'               => $class,
                            'begin_date'                  => $beginEventDate->get('Y-MM-dd'),
                            'begin_date_planned'          => $beginEventDate->get('Y-MM-dd'),
                            'end_date'                    => $endEventDate->get('Y-MM-dd'),
                            'end_date_planned'            => $endEventDate->get('Y-MM-dd'),
                            'status'              => HM_Process_Abstract::PROCESS_STATUS_INIT
                        );
                    }
                }
            } else {

                /** @var HM_Process_Type_ProgrammModel $processAbstract */
                $programmEvents = $processAbstract->getProgrammEvents();
                foreach ($programmEvents as $programmEvent) {

                    $beginEventDate = clone $beginDate;
                    $endEventDate   = clone $beginDate;

                    $beginEventDate = HM_Date::getRelativeDate($beginEventDate, $programmEvent->day_begin);
                    $endEventDate   = HM_Date::getRelativeDate($endEventDate,   $programmEvent->day_end);

                    // наполняем state_of_process_data сразу всеми этапами
                    $stateData = array(
                        'state_of_process_id' => $stateModel->state_of_process_id,
                        'state'               => $processAbstract->getStatePrefix() . $programmEvent->programm_event_id,
                        'begin_date'          => $beginEventDate->get('Y-MM-dd'),
                        'begin_date_planned'  => $beginEventDate->get('Y-MM-dd'),
                        'end_date'            => $endEventDate->get('Y-MM-dd'),
                        'end_date_planned'    => $endEventDate->get('Y-MM-dd'),
                        'status'              => HM_Process_Abstract::PROCESS_STATUS_INIT,
                    );

                    // @todo: не факт, что model имеет user_id
                    if (count($programmEvent->programmEventUser) && $model->user_id) {
                        $user2programmEventUser = $programmEvent->programmEventUser->getList('user_id', 'programm_event_user_id');
                        $stateData['programm_event_user_id'] = $user2programmEventUser[$model->user_id];
                    }

                    $statesData[] = $stateData;
                }
            }

            foreach ($statesData as $stateData) {
                $stateDataService->insert($stateData);
            }

            if (!$processAbstract->isStrict()) {
                // отмечаем в data переход на 1-й этап
                $stateDataService->check(
                    $stateModel->state_of_process_id,
                    $firstClass,
                    HM_Process_Abstract::PROCESS_STATUS_CONTINUING,
                    $automatic
                );
            }

            if (!$processAbstract->isStatic()) {
                $this->_resetProgrammEvents($model, $stateModel);
                $this->_updateProgrammEvent($model, $stateModel);
            }
            
            if ($state = $process->initState($stateModel)) {
                if ($currentClass != $processAbstract->getStateClass($state)) {
                    $this->_updateState($model); // если случился автопереход; правда, сейчас все автопереходы отключены
                }
            }
            
            return true;
        }
        return false;
    }

    /*
     * Метод для корректной установки дат этапов процесса, указанных в processes.xml в виде относительных величин,
     * в том числе и отрицательных, то есть отсчитываемых от даты окончания процесса.
     *
     * @param object $model Экземпляр процесса, для этапов которого определяются относительные даты
     * @param array $step Массив, содержащий данные об этапе, взятые из processes.xml
     * @return array Массив, состояций из дат начала и окончания этапа
     *
     */
    protected function getStepRelativeDates($model, $step)
    {
        $process   = $model->getProcess();
        $beginDate = $model->begin_date ? new HM_Date($model->begin_date) : $process->getBeginDate();
        $endDate   = $model->end_date   ? new HM_Date($model->end_date)   : $process->getBeginDate();

        $beginEventDate =
        $endEventDate   = new HM_Date();

        if ($step['day_begin'] < 0 && $step['day_end'] <= 0) {
            $dates = 'negative';
        } elseif ($step['day_begin'] >= 0 && $step['day_end'] > 0) {
            $dates = 'positive';
        } else {
            $dates = 'both';
        }

        switch ($dates) {
            case 'positive':
                $beginEventDate = clone $beginDate;
                $endEventDate   = clone $beginDate;
                $beginEventDate = $step['day_begin'] ?
                                  HM_Date::getRelativeDate($beginEventDate, $step['day_begin']) : $beginEventDate;
                $endEventDate   = HM_Date::getRelativeDate($endEventDate  , $step['day_end'  ]);
                break;
            case 'both':
                $beginEventDate = clone $beginDate;
                $endEventDate   = clone $endDate;
                $beginEventDate = HM_Date::getRelativeDate($beginEventDate, $step['day_begin']);
                $endEventDate   = HM_Date::getRelativeDate($endEventDate  , $step['day_end'  ]);
                break;
            case 'negative':
                $beginEventDate = clone $endDate;
                $endEventDate   = clone $endDate;
                $endEventDate   = $step['day_end'] ?
                                  HM_Date::getRelativeDate($endEventDate  , $step['day_end'  ]) : $endEventDate;
                $beginEventDate = HM_Date::getRelativeDate($beginEventDate, $step['day_begin']);
                break;
        }

        return array($beginEventDate, $endEventDate);
    }
    
    public function getCurrentState($model)
    {
        $this->initProcess($model);
        $process = $model->getProcess();
        return $process->getCurrentState();
    }

    public function getLastPassedState($model)
    {
        $this->initProcess($model);
        $process = $model->getProcess();
        return $process->getLastPassedState();
    }

    /**
     * Выполняет переход на n+1-й шаг процесса (сохраняет в базе инормацию о n-м шаге);
     * 
     */
    public function goToNextState($model, $automatic = false)
    {
        $this->initProcess($model);
        $process = $model->getProcess();
        $currentState = $process->getCurrentState();
        if (!$currentState) return false;

        $nextClass = $currentState->getNextState();
        $prevStateId = $process->getStateId();
        if ($nextState = $process->goToNextState()) {
            $this->_updateState($model);
            return true;
        }
        return false;
    }

    /**
     * Выполняет переход на n-1-й шаг процесса (сохраняет в базе инормацию о n-м шаге);
     *
     */
    public function goToPrevState($model, $automatic = false)
    {
        $this->initProcess($model);
        $process = $model->getProcess();

        if ($prevState = $process->goToPrevState()) {
            $this->_updateState($model, $automatic, $rollback = true);
            return true;
        }
        return false;
    }

    /**
     * Альтернатива goToNextState и _updateState для нестрогих процессов
     * 
     */       
    public function setStateStatus($model, $stateClass, $status)
    {
        $process = $model->getProcess();
        if (!$process || ($process->getStatus() === null)) {
            $this->initProcess($model); // возможно, процесс уже существует и содержит новые данные; нельзя его заново инициализировать из базы
        }
        $processAbstract = $process->getProcessAbstract();
        
        if ($stateModel = $this->getService('State')->getOne($this->getService('State')->find($process->getStateId()))) {
            if ($status == HM_State_Abstract::STATE_STATUS_PASSED) {
                
                $passedStates = explode(',', $stateModel->passed_states);
                if (!in_array($stateClass, $passedStates)) $passedStates[] = $stateClass;
                $passedStates = array_filter($passedStates); // filter empty
                
                $stateModel->passed_states = implode(',', $passedStates);
                $stateModel->current_state = '';
                $stateModel->last_passed_state = $stateClass;
                $stateModel->status = $process->getStatus();

                // фиксируем в state_of_process_data
                $this->getService('StateData')->check(
                    $stateModel->state_of_process_id,
                    $stateClass,
                    HM_Process_Abstract::PROCESS_STATUS_COMPLETE,
                    false //?
                );

                // если это был последний невыполненный шаг процесса - заканчиваем весь процесс
                if (count($passedStates) == count($process->getStates())) {
                    $stateModel->status = HM_Process_Abstract::PROCESS_STATUS_COMPLETE;
                    $process->goToComplete();
                }
                
            } elseif ($status == HM_State_Abstract::STATE_STATUS_FAILED) {
                
                $process->goToFail();
                $stateModel->current_state = $stateClass;
                $stateModel->status = HM_Process_Abstract::PROCESS_STATUS_FAILED;

                // фиксируем в state_of_process_data
                $this->getService('StateData')->check(
                    $stateModel->state_of_process_id,
                    $stateClass,
                    HM_Process_Abstract::PROCESS_STATUS_FAILED,
                    false //?
                );
            }
            
            $stateModel = $this->getService('State')->update($stateModel->getValues());

            /** @var HM_State_Data_StateDataService $stateDataService */
            $stateDataService = $this->getService('StateData');

            $stateDataService->check(
                $stateModel->state_of_process_id,
                $stateModel->current_state,
                $stateModel->status
            );

            if (!$processAbstract->isStatic()) {
                $this->_updateProgrammEvent($model, $stateModel);
            }               
        } 
    }
    
    /**
     * Если процесс скоропостижно завершен на одном из промежуточных этапов.
     * При этом изменяется и статус текущего этапа (становится красный) и статус процесса целиком 
     * Если процесс нестрогий, то текущего вообще шага нет, надо обязательно передать $currentStateClass
     */
    public function goToFail($model, $currentStateClass = false)
    {
        $this->initProcess($model);
        $process = $model->getProcess();
        $processAbstract = $process->getProcessAbstract();
                
        if ($processAbstract && $processAbstract->isStrict()) {
            if ($nextState = $process->goToFail()) {
                $this->_updateState($model);
                return true;
            }
        } else {
            if (!$currentStateClass) {
                if ($lastPassedState = $process->getLastPassedState()) {
                    $lastPassedStateClass = $processAbstract->getStateClass($lastPassedState);
                    $this->setStateStatus($model, $lastPassedStateClass, HM_State_Abstract::STATE_STATUS_FAILED);
                }
            }
        }
    }
    
    /**
     * Применимо чаще всего к нестрогим процессам
     * Строгие процессы приходят к успешному завершению только через последовательное выполнение goToNextState
     * Можно вызвать goToSuccess для строгого процесса ,если есть уверенность, что это последний шаг процесса
     * (например, из HM_At_Session_Event_Method_Form_Finalize)
     */
    public function goToSuccess($model, $currentStateClass = false, $forceFinish = false)
    {
        $this->initProcess($model);
        $process = $model->getProcess();
        $processAbstract = $process->getProcessAbstract();
        
        if ($processAbstract->isStrict()) {

            if ($forceFinish) {
                do {
                    if ($nextState = $process->goToNextState()) {
                        $this->_updateState($model);
                    }
                } while ($nextState || (++$i > 10));
            } else {
                if ($nextState = $process->goToNextState()) {
                    $this->_updateState($model);
                }
            }
        } elseif ($currentStateClass) {
            
            // это нештатный случай завершения процесса, поэтому принудительно делаем goToComplete()
            // штатный - это когда выполняется последний оставшийся шаг и внутри setStateStatus вызывается goToComplete() 
            // должно выполняться до $this->setStateStatus, т.к. setStateStatus сохраняет статус всего процесса тоже            
            
            $process->goToComplete();  
            $this->setStateStatus($model, $currentStateClass, HM_State_Abstract::STATE_STATUS_PASSED);
        }
    }
    
    /**
     * Это нужно, когда процесс завершается "не по вине" участника - например:
     * кто-то один прошёл отбор, для всех остальных надо завершить процесс,
     * но это не значит что они провалили каждый свой текущий этап.
     */
    public function goToComplete($model)
    {
        $this->initProcess($model);
        $process = $model->getProcess();
        $process->goToComplete();
        
        // нужно еще сохранить статус процесса; 
        if ($stateModel = $this->getService('State')->getOne($this->getService('State')->find($process->getStateId()))) {
            $stateModel->status = $process->getStatus();
            $this->getService('State')->update($stateModel->getValues());
        }
    }
    
    /**
     * Сохраняет текущее состояние процесса в базу
     *
     * ВНИМАНИЕ! rollback - не работает
     */    
    protected function _updateState($model, $automatic = false, $rollback = false)
    {
        $process = $model->getProcess();
        $processAbstract = $process->getProcessAbstract();

        // @todo: для программ с mode_strict=0 текущего шага вообще нет
        $currentState = $process->getCurrentState();
            
        foreach($process->getStates() as $state) {
            $params[$processAbstract->getStateClass($state)] = $state->getParams();
        }

        $currentStateClass = $currentState ? $processAbstract->getStateClass($currentState) : '';
        $prevStateClass = ($prevState = $process->getPrevState()) ? $processAbstract->getStateClass($prevState) : '';

        $stateModel = $this->getService('State')->update(
            array(
                'state_of_process_id' => $process->getStateId(),
                'current_state'       => $currentStateClass,
                'last_passed_state'   => $prevStateClass,
                'status'              => $process->getStatus(),
                'params'              => serialize($params)
            )
        );


        /** @var HM_State_Data_StateDataService $stateDataService */
        $stateDataService = $this->getService('StateData');

        if ($process->getStatus() != HM_Process_Abstract::PROCESS_STATUS_FAILED) {

            // предыдущий шаг - отметить как прошедший
            if ($prevStateClass) {
                $stateDataService->check(
                    $process->getStateId(),
                    $prevStateClass,
                    HM_State_Abstract::STATE_STATUS_PASSED,
                    $automatic
                );
            }

            // новый шаг, ставший текущим - отметить как continuing
            if ($currentStateClass) {
                $stateDataService->check(
                    $process->getStateId(),
                    $currentStateClass,
                    HM_State_Abstract::STATE_STATUS_CONTINUING,
                    $automatic
                );
            }

        } else {

            // когда процесс прерывается на каком-то шаге,
            // перехода не случается и _updateState вызывается для тог же шага, на котором всё закончилось
            $stateDataService->check(
                $process->getStateId(),
                $currentStateClass,
                HM_State_Abstract::STATE_STATUS_FAILED,
                $automatic
            );
        }

        if (!$processAbstract->isStatic()) {
            $this->_updateProgrammEvent($model, $stateModel);
        }                  
    }

    // каждый раз когда процесс переходит на след.шаг нужно обновлять programm_user_event
    // в state_of_process хранится только текущий шаг. а в programm_user_event - все шаги
    // если в model нет никакого user_id - не беда, значит вам это не надо;
    protected function _updateProgrammEvent($model, $stateModel)
    {
        $programmEventCondition = array();
        $process = $model->getProcess();
        if ($processAbstract = $process->getProcessAbstract()) {
            $statePrefix = $processAbstract->getStatePrefix();
            if ($processAbstract->isStrict()) {
                // если процесс строгий - надо поставить continuing для $state->current_state
                $data = array('status' => HM_Programm_Event_User_UserModel::STATUS_CONTINUING); 
                $programmEventCondition['programm_event_id = ?'] = (int)str_replace($statePrefix, '', $stateModel->current_state); // @todo: иногда пытается вставить "Complete"
            } else {
                // если процесс нестрогий - $state->current_state отсутствует
                // надо проставить passed для всех ранее пройденных
                $programmEventIds = array();
                $data = array('status' => HM_Programm_Event_User_UserModel::STATUS_PASSED);
                if ($passedStates = explode(',', $stateModel->passed_states)) {
                    foreach ($passedStates as $stateClass) {
                        if ($programmEventId = (int)str_replace($statePrefix, '', $stateClass)) {
                            $programmEventIds[] = $programmEventId; 
                        }
                    }
                }
                if (count($programmEventIds)) {
                    $programmEventCondition['programm_event_id IN (?)'] = $programmEventIds;
                }
            }
        }
        
        if (!empty($programmEventCondition) && method_exists($model, 'getUserId')) {
            $this->getService('ProgrammEventUser')->updateWhere($data, array(
                'user_id = ?' => $model->getUserId(),
            ) + $programmEventCondition);        
        }
        
    }

    protected function _resetProgrammEvents($model)
    {
        if ($model->process && $model->getUserId()) {
            $this->getService('ProgrammEventUser')->updateWhere(array(
                    'status' => HM_Programm_Event_User_UserModel::STATUS_NOT_STARTED
                ), array(
                    'user_id = ?' => $model->getUserId(),
                    'programm_id = ?' => $model->process->programm_id,
                ));
        }
    }
}
