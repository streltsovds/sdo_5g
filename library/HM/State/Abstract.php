<?php
abstract class HM_State_Abstract
{
    const STATE_STATUS_PASSED     = 0;
    const STATE_STATUS_CONTINUING = 1;
    const STATE_STATUS_WAITING    = 2;
    const STATE_STATUS_FAILED     = 3;

    // статус чисто для вызова перехода на предыдущий этап
    const STATE_STATUS_ROLLBACK   = 9;

    const ACTION_TYPE_LINK   = 0;
    const ACTION_TYPE_SELECT = 1;

    const PASSED_FAIL = 1;
    const PASSED_OK   = 2;

    protected $_params = array();
    protected $_status = null;
    protected $_nextState = null;
    protected $_process = null;
    protected $_processAbstract = null;
    protected $_isLastPassed = null;

    abstract public function isNextStateAvailable();
    abstract public function onNextState();
    abstract public function getActions();
    abstract public function getDescription();
    abstract public function initMessage();
    abstract public function onNextMessage();
    abstract public function onErrorMessage();    
    
    static public function factory($class)
    {
        $parts = explode('_', $class);
        if (count($parts) > 1) {
            $itemId = array_pop($parts);
            if (is_numeric($itemId)) {
                array_push($parts, 'Abstract');
                $class = implode('_', $parts);
                return new $class($itemId);
            } else {
                array_push($parts, $itemId);
                $class = implode('_', $parts);
                return new $class();
            }
        }
        return false;        
    }
    
    public function init($processAbstract, $process = null)
    {
        $this->_processAbstract = $processAbstract;
        $this->_process = $process;
    }
    
    public function getTitle()
    {
        if ($this->isImpossible()) {
            return _('Внимание! Мероприятие не может быть выполнено, проверьте настройки.');
        } 
        
        return $this->_processAbstract->getStateTitle($this);
    }

    /**
     * Здесь информация о плановых сроках этапов
     * (там где используется календарь)
     * Пример реализации:
     *
     * @return array
     */
    public function getProcessStepsData()
    {
        return array();
    }

    /**
     * Здесь информация о фактическом прохождении этапов программы
     *
     * @return HM_State_Data_StateDataModel
     */
    public function getStateData()
    {
        static $stateDataCache = array();

        $stateId = $this->_process->getStateId();

        if (!isset($stateDataCache[$stateId])) {

            /** @var HM_State_Data_StateDataService $stateDataService */
            $stateDataService = $this->getService('StateData');

            $statesData = $stateDataService->fetchAll(array(
                'state_of_process_id = ?' => $stateId
            ));

            $stateDataCache[$stateId] = array();

            foreach ($statesData as $stateData) {
                $stateDataCache[$stateId][$stateData->state] = $stateData;
            }

        }

        $state = $this->_processAbstract->getStateClass($this);

        return isset($stateDataCache[$stateId][$state]) ? $stateDataCache[$stateId][$state] : false;

    }

    /**
     * Информация о всех этапах, включая непройденные
     *
     * @return array
     */
    public function getAllStatesData()
    {
        static $allStatesDataCache = array();

        $stateId = $this->_process->getStateId();

        if (!isset($allStatesDataCache[$stateId])) {

            /** @var HM_State_Data_StateDataService $stateDataService */
            $stateDataService = $this->getService('StateData');
            $statesData = $stateDataService->fetchAll(array(
                'state_of_process_id = ?' => $stateId
            ));

            $allStatesDataCache[$stateId] = array();

            foreach ($statesData as $stateData) {
                $allStatesDataCache[$stateId][$stateData->state] = $stateData;
            }
        }

        return $allStatesDataCache[$stateId];

    }

    public function getExtendedDescription()
    {
        $status = $this->_status;

        if (($status == self::STATE_STATUS_WAITING) || $this->isCurrent()) {
            //return false;
        }

        $data  = $this->getStateData();

        if (!$data) {
            return array(
                'comment' => '',
                'files'   => array()
            );
        }

        return array(
            'comment' => $data->comment,
            'files'   => $data->getFiles()
        );

    }

    public function getForms()
    {
        return '';
    }

    public function getDescriptionForm()
    {
        if ($this->_processAbstract->isStrict() && !$this->isCurrent()) {
            return '';
        }

        $stateData = $this->getStateData();

        //if (!$stateData) {
        //    return '';
        //}

        $state   = $this->_processAbstract->getStateClass($this);
        $stateId = $this->_process->getStateId();

        $form = new HM_State_Form_CommentForm(array(
            'state'   => $state,
            'stateId' => $stateId
        ));

        $form->setName($state);

        $form->setDefault('comment', $stateData->comment);

        return $form;

    }

    public function getFilesForm()
    {
        if ($this->_processAbstract->isStrict() && !$this->isCurrent()) {
            return '';
        }

        $stateData = $this->getStateData();

        if (!$stateData) {
            return '';
        }

        $state   = $this->_processAbstract->getStateClass($this);
        $stateId = $this->_process->getStateId();

        $form = new HM_State_Form_FilesForm(array(
            'state'   => $state,
            'stateId' => $stateId
        ));

        $form->setDefault('files', $stateData->getFiles());

        return $form;

    }

    public function setParams($params)
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function setStatus($status)
    {
        $this->_status = $status;

        switch($this->_status){
            case self::STATE_STATUS_FAILED:
                $msg = $this->getFailMessage();
                break;
            case self::STATE_STATUS_CONTINUING:
                $msg = $this->getCurrentStateMessage();
                break;
            case self::STATE_STATUS_PASSED:
                $msg = $this->getSuccessMessage();
                break;
            case self::STATE_STATUS_WAITING:
            default:
                $msg = '';
                break;
        }
        
        $params = $this->getParams();
        $params['system']['textStatus'] = $msg;
        $this->setParams($params);

    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function getStatusMessage()
    {
        $params = $this->getParams();
        return $params['system']['textStatus'];
    }

    // По-умолчанию возвращает текущий статус. Если false, то не показываем
    public function getResultMessage()
    {
        if(in_array($this->getStatus(), array(self::STATE_STATUS_PASSED, self::STATE_STATUS_FAILED))){
            return $this->getStatusMessage();
        }

        return false;
    }

    public function getClass()
    {
        $classes = $this->getClasses();
        $return = $classes[$this->getStatus()];
        if ($this->isImpossible()) {
            $return .= ' impossible';
        } 
        return $return;
    }

    public function getClasses()
    {
        return array(
            HM_State_Abstract::STATE_STATUS_PASSED     => 'status-completed status-completed-success',
            HM_State_Abstract::STATE_STATUS_CONTINUING => 'status-continuing',
            HM_State_Abstract::STATE_STATUS_WAITING    => 'status-waiting',
            HM_State_Abstract::STATE_STATUS_FAILED     => 'status-completed status-completed-failed'
        );
    }

    public function setNextState($nextState)
    {
        $this->_nextState = $nextState;
    }

    public function getNextState()
    {
        return $this->_nextState;
    }


    public function getFailMessage()
    {
        return _('');
    }

    public function getSuccessMessage()
    {
        return _('');
    }

    /**
     *    Called on ajax call and other places
     */
    public function checkAutomateTransition($newParams)
    {
    }

    public function getCompleteMessage()
    {
        $params = $this->getParams();
        if($this->getStatusMessage() != '' && ($this->getStatus() == self::STATE_STATUS_PASSED || $this->getStatus() == self::STATE_STATUS_FAILED) ){
            return  $this->getStatusMessage();
        }

        return false;
    }

/*
 * @see self::getSuccessMessage
    public function getAchievedStateMessage() 
    {
        return _('');
    }*/

    // ???
    public function getCurrentStateMessage() 
    {
        return '';
    }

    public function getProcessAbstract()
    {
        return $this->_processAbstract;
    }

    public function getProgrammEventId()
    {
        return $this->_programmEventId;
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    // весьма опасный метод, может стоит от него отказаться
    // проталкивает процесс дальше, если текушщий шаг в принципе не может быть выполнен
    public function isImpossible()
    {
        return false;
    }
    
    public function isCurrent()
    {
        return ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING);
    }

    public function isVisible()
    {
        if (isset($this->_programmEventId)) {
           return ($this->_processAbstract->getIsHidden($this) == HM_Programm_Event_EventModel::EVENT_HIDDEN_YES)?false:true;
        }
        return true;
    }

    // работает для статического процесса
    public function getDates()
    {
        $return = array();

        // DEPRECATED! даты хранятся только в state_of_process_data
        // $data = $this->getProcessStepsData();
        $data = $this->getAllStatesData();
        $class = get_class($this);
        if ($data[$class]) {
            $step = $data[$class];
            if ($step->begin_date_planned) {
                $date = new HM_Date($step->begin_date_planned);
                $return[] = $date->toString('dd.MM.Y');
            }
            if ($step->end_date_planned) {
                $date = new HM_Date($step->end_date_planned);
                $return[] = $date->toString('dd.MM.Y');
            }
        }
        return $return;
    }

    // дата фактического завершения этапа
    // берётся из state_of_process_data
    public function getFactDate()
    {
        $data = $this->getStateData();
        if ($data->end_date) {
            $date = new HM_Date($data->end_date);
            return $date->toString('dd.MM.Y');
        }
        return false;
    }

    /**
     * @return null
     */
    public function isLastPassed()
    {
        return $this->_isLastPassed;
    }

    /**
     * @param null $isLastPassed
     */
    public function setLastPassed()
    {
        $this->_isLastPassed = true;
    }



    public function addFile($fileData)
    {
        return false;
    }

    public function onStateStart()
    {
        return true;
    }

    public function isPrevStateAvailable()
    {
        return false;
    }

    public function onPrevState()
    {
        return true;
    }

    public function onStateRollback()
    {
        return true;
    }

}
