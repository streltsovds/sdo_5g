<?php
/**
 * Абстрактный класс процесса
 * От него наследуют все процессы, реализованные в системе (те, что в моделях с именем '...Process.php')
 * Здесь нет никакой работы с базой; все входные данные из базы получаются через ProcessService
 */
abstract class HM_Process_Abstract
{
    // Status и State - это две большие разницы
    // Status - общее состоняие процесса
    // одному PROCESS_STATUS_CONTINUING может соответствовать множество State   
     
    // state и stateModel - это две большие разницы
    // state - класс состояния, содержащий всю логику этого состояни (напр., HM_At_Session_State_Open.php)
    // stateModel - модель текущего состояния процесса из таблицы state_of_process
     
    const PROCESS_STATUS_INIT       = 0; // хранится в state_of_process/status
    const PROCESS_STATUS_CONTINUING = 1;
    const PROCESS_STATUS_FAILED     = 2;
    const PROCESS_STATUS_COMPLETE   = 3;

    const MODE_STATE_DATES_HIDDEN = 0;
    const MODE_STATE_DATES_READONLY = 1;
    const MODE_STATE_DATES_EDITABLE = 2;

    protected $_states    = array();
    protected $_status    = null;
    protected $_stateId   = null;
    protected $_model     = null;
    protected $_chain     = array();
    
    protected $_processId = null;
    protected $_processAbstract;

    abstract public function onProcessStart();
    abstract public function onProcessComplete();
    abstract public function getType();

    static public function getStatuses() {}
    
    public function __construct($model)
    {
        $this->_model = $model;
        $this->initProcessAbstract();     
        
        if ($this->_processAbstract) {
            $this->_processId = $this->_processAbstract->process_id;
            $this->_chain = $this->_processAbstract->getChain();        
        } else {
            // что-то очень неправильно..
        }
    }
    
    /**
     * @param HM_State_StateModel $stateModel
     * @return HM_State_Abstract; 
     * может не совпадать с $stateModel если случился авто-переход на след.шаг; в этом случае надо обновить $stateModel в базе
     */
    public function initState($stateModel)
    {
        $this->_stateId = $stateModel->state_of_process_id;
        $this->setStatus($stateModel->status);
        
        $params = unserialize($stateModel->params);
        
        $status = HM_State_Abstract::STATE_STATUS_PASSED; // таким образом шаги до current_state становятся зелёненькие 

        if (empty($this->_chain)) return;

        $currentState = null;
        $this->_states = array(); // иногда инициализация процесса случается повторно, состояния не должны дублироваться
        foreach ($this->_chain as $key => $val) {
            $state = call_user_func(array('HM_State_Abstract', 'factory'), $key);
            $state->init($this->_processAbstract, $this);
            if ($key == $stateModel->current_state) {
                
                switch ($stateModel->status) {
                    case HM_Process_Abstract::PROCESS_STATUS_FAILED:
                        // если процесс провален, показываем теущий шаг красненьким
                        $currentStatus = HM_State_Abstract::STATE_STATUS_FAILED;
                    break;
                    case HM_Process_Abstract::PROCESS_STATUS_COMPLETE:
                        // если процесс закончен, не показываем теущий шаг (показываем сереньким)
                        // такое бывает например после goToComplete()
                        $currentStatus = HM_State_Abstract::STATE_STATUS_WAITING;
                    break;
                    default:
                        // нормальное состояние - текущий шаг синенький
                        $currentStatus = HM_State_Abstract::STATE_STATUS_CONTINUING;
                    break;
                }
                
                $state->setStatus($currentStatus);
                $status = HM_State_Abstract::STATE_STATUS_WAITING; // таким образом шаги после current_state становятся серенькие
                $state->setParams($params[$key]);
                $state->setNextState($val);
                $currentState = $state;
            } else {
                $state->setStatus($status);
                $state->setParams($params[$key]);
                $state->setNextState($val);
            }
            $this->_states[] = $state;
        }

//         while ($currentState && $currentState->isImpossible()) {
//             $currentState = $this->goToNextState();
//         } 
        
        return $currentState;
    }
    
    public function initPassedStates($stateModel)
    {
        $this->_stateId = $stateModel->state_of_process_id;
        $this->setStatus($stateModel->status);
        
        $params = unserialize($stateModel->params);
        $status = HM_State_Abstract::STATE_STATUS_PASSED;
        $passedStates = !empty($stateModel->passed_states) ? explode(',', $stateModel->passed_states) : array(); 
        if (!empty($stateModel->current_state) && ($stateModel->status == HM_Process_Abstract::PROCESS_STATUS_FAILED)) {
            $failState =  $stateModel->current_state; 
        }

        if (empty($this->_chain)) return;

        foreach ($this->_chain as $key => $val) {
            $state = call_user_func(array('HM_State_Abstract', 'factory'), $key);
            $state->init($this->_processAbstract, $this); // при свободном прохождениии state должен знать про статус всего процесса
            if (in_array($key, $passedStates)) {
                $state->setStatus(HM_State_Abstract::STATE_STATUS_PASSED);
                if ($stateModel->last_passed_state && ($stateModel->last_passed_state == $key)) {
                    $state->setLastPassed();
                }
            } elseif ($failState && ($key == $failState)) {
                $state->setStatus(HM_State_Abstract::STATE_STATUS_FAILED);
            } else {
                $state->setStatus(HM_State_Abstract::STATE_STATUS_WAITING);
            }
            $state->setParams($params[$key]);

            if (!$this->_processAbstract->getIsHidden($state)) {
                $this->_states[] = $state;
            }
        }
        
        return true;
    }
    
    public function getProcessAbstract()
    {
        return $this->_processAbstract;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function getStates()
    {
        return $this->_states;
    }

    // альтернатива getCurrentState для нестрогих процессов
    public function getLastPassedState($firstByDefault = true)
    {
        if ($this->getProcessAbstract() && $this->getProcessAbstract()->isStrict()) return false;

        $states = $this->getStates();
        foreach ($states as $state) {
            if ($state->isLastPassed()) return $state;
        }

        if ($firstByDefault) {
            return $states[0];
        }

        return false;
    }

    public function getCurrentState()
    {
        if (!$this->getProcessAbstract()->isStrict()) return false;
        
        switch ($status = $this->getStatus()) {
            case self::PROCESS_STATUS_COMPLETE:
                if ($chain = array_values($this->_chain)) {
                    // это особенный state
                    // он не отображется в bulbs                    
                    // его нет в $this->_states
                    return call_user_func(array('HM_State_Abstract', 'factory'), array_pop($chain));
                }
                break;
            default:
                $states = $this->getStates();
                foreach ($states as $state) {
                    if (in_array($state->getStatus(), array(HM_State_Abstract::STATE_STATUS_CONTINUING, HM_State_Abstract::STATE_STATUS_FAILED))) {
                        return $state;
                    }
                }
                break;        
        }
        return false;
    }

    public function getPrevState()
    {
        $states = $this->getStates();
        $prevClass = array_search($this->_processAbstract->getStateClass($this->getCurrentState()), $this->_chain);

        foreach($states as $state) {
            if ($prevClass == $this->_processAbstract->getStateClass($state)) {
                return $state;
            }
        }
        return false;
    }

    
    /**
     * @return HM_State_Abstract если OK | false если не OK
     * 
     */
    public function goToNextState()
    {
        $currentState = $this->getCurrentState();
        if ($currentState) {
            $nextClass = $currentState->getNextState();
            $nextState = null;

            $states = $this->getStates();
            foreach($states as $state) {
                if ($nextClass == $this->_processAbstract->getStateClass($state)) {
                    $nextState = $state;
                    break;
                }
            }

            if ($nextState == null) {
                if ($nextState = call_user_func(array('HM_State_Abstract', 'factory'), $nextClass)) {
                    $nextState->setStatus(HM_State_Abstract::STATE_STATUS_WAITING);
                    $nextState->setParams(array());
                }
            }

            if ($currentState->isNextStateAvailable()) {
                if ($currentState->onNextState()) {

                    $currentState->setStatus(HM_State_Abstract::STATE_STATUS_PASSED);
                    
                    if ($nextState) {
                        $nextState->setStatus(HM_State_Abstract::STATE_STATUS_CONTINUING);
                        if (is_subclass_of($nextState, 'HM_State_Complete_Abstract')) {
                            $this->setStatus(self::PROCESS_STATUS_COMPLETE);
                            $this->onProcessComplete();
                        } else {
                            $this->setStatus(self::PROCESS_STATUS_CONTINUING);
                            $nextState->onStateStart();
                        }

                        if ($nextState->isVisible()) {
                            return $nextState;
                        } else {
                            return $this->goToNextState();
                        }

                        // return $currentState->onNextMessage(); // непонятно что с этим message дальше делать
                    }
                }
            } else {
                return false;
                // return $currentState->onErrorMessage();
            }
        }
        return false;
    }


    /**
     * @return HM_State_Abstract если OK | false если не OK
     *
     */
    public function goToPrevState()
    {
        $currentState = $this->getCurrentState();
        if ($currentState) {
            $prevClass = get_class($this->getPrevState());
            $prevState = null;

            $states = $this->getStates();
            foreach($states as $state) {
                if ($prevClass == $this->_processAbstract->getStateClass($state)) {
                    $prevState = $state;
                    break;
                }
            }

            if ($prevState == null) {
                if ($prevState = call_user_func(array('HM_State_Abstract', 'factory'), $prevClass)) {
                    $currentState->setStatus(HM_State_Abstract::STATE_STATUS_WAITING);
                    $currentState->setParams(array());
                }
            }

            if ($currentState->isPrevStateAvailable()) {
                if ($currentState->onPrevState()) {

                    $currentState->setStatus(HM_State_Abstract::STATE_STATUS_WAITING);

                    if ($prevState) {
                        $prevState->setStatus(HM_State_Abstract::STATE_STATUS_CONTINUING);
                        if (is_subclass_of($currentState, 'HM_State_Complete_Abstract')) {
                            $this->setStatus(self::PROCESS_STATUS_CONTINUING);
                            $this->onProcessRollback();
                        } else {
                            $this->setStatus(self::PROCESS_STATUS_CONTINUING);
                            $currentState->onStateRollback();
                        }

                        return $prevState;
                        // return $currentState->onNextMessage(); // непонятно что с этим message дальше делать
                    }
                }
            } else {
                return false;
                // return $currentState->onErrorMessage();
            }
        }
        return false;
    }


    public function goToFail()
    {
        if ($currentState = $this->getCurrentState()) {
        	$currentState->setStatus(HM_State_Abstract::STATE_STATUS_FAILED);
        }
        if (!in_array($this->getStatus(), array(self::PROCESS_STATUS_COMPLETE, self::PROCESS_STATUS_FAILED))) {
            $this->setStatus(self::PROCESS_STATUS_FAILED);
            $this->onProcessComplete();
        }
        return $currentState;
    }

    public function goToComplete()
    {
        if (!in_array($this->getStatus(), array(self::PROCESS_STATUS_COMPLETE, self::PROCESS_STATUS_FAILED))) {
            $this->setStatus(self::PROCESS_STATUS_COMPLETE);
            $this->onProcessComplete();
        }
        return true;
    }

    public function getProcessId()
    {
        return $this->_processId;
    }

    public function getStateId()
    {
        return $this->_stateId;
    }

    // deprecated???
    public function checkAutomateTransition($newParams = array())
    {
        $this->getCurrentState()->checkAutomateTransition($newParams);
    }

    public function getModel()
    {
        return $this->_model;
    }

    // ???
    public function getRedirectionUrl()
    {
        $resArray = array('action' => 'list');
        return Zend_Registry::get('view')->url($resArray, null, true);

    }

    public function getDatesFromDefinition($definition)
    {
        return $definition;
    }

    public function getBeginDate()
    {
        return new HM_Date();
    }

    // режим работы с датами этапов в окне БП
    public function getStateDatesMode()
    {
        return self::MODE_STATE_DATES_EDITABLE;
    }

    public function getService($serviceName) {
        return Zend_Registry::get('serviceContainer')->getService($serviceName);
    }

    public function quoteInto($where, $args)
    {
        return $this->getService('User')->quoteInto($where, $args);
    }
}
