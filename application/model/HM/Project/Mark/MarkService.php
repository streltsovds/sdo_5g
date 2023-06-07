<?php
//require_once($_SERVER['DOCUMENT_ROOT']."/formula_calc.php");

class HM_Project_Mark_MarkService extends HM_Service_Abstract
{

    protected $userId = null;

    public function setUserId($id) {
        $this->userId = $id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function  update($data)
    {
        $data['mark'] = HM_Project_Mark_MarkModel::filterMark($data['mark']);
        $mark = parent::update($data);
        return $mark;
    }

    public function insert($data)
    {
        $data['mark'] = HM_Project_Mark_MarkModel::filterMark($data['mark']);
        $mark = parent::insert($data);
        return $mark;
    }

    public function updateWhere($data, $where) {
        $updateResult = parent::updateWhere($data, $where);
        return $updateResult;
    }

    public function getRelatedUserList($id) {
        return array(intval($this->userId));
    }

    // кэширующие функции для работы calcTotalValue (иногда может вызываться много раз
    protected $_meetingAssignCache = array();

    protected function _getMeetingAssign($userId, $projectId)
    {
        $cache = &$this->_meetingAssignCache;

        if (!isset($cache[$projectId])) {
            $cache[$projectId] = array();
        }

        if (!isset($cache[$projectId][$userId])) {

            $meetingAssignService = $this->getService('MeetingAssign');

            $cache[$projectId][$userId] = $meetingAssignService->fetchAllDependenceJoinInner('Meeting', $meetingAssignService->quoteInto(array(
                'self.MID = ? AND ',
                'Meeting.CID = ? AND ',
                'Meeting.isfree = ? AND ',
                'Meeting.vedomost = 1'
            ), array(
                $userId,
                $projectId,
                HM_Meeting_MeetingModel::MODE_PLAN
            )));
        }

        return $cache[$projectId][$userId];

    }

    protected function _loadMeetingAssignCache($projectId)
    {
        $cache = array();

        $meetingAssigns = $this->getService('MeetingAssign')->fetchAllDependenceJoinInner('Meeting', $this->getService('MeetingAssign')->quoteInto(array(
            'Meeting.CID = ? AND ',
            'Meeting.isfree = ? AND ',
            'Meeting.vedomost = 1'
        ), array(
            $projectId,
            HM_Meeting_MeetingModel::MODE_PLAN
        )));

        foreach ($meetingAssigns as $meetingAssign) {
            $mid = $meetingAssign->MID;

            if (!isset($cache[$mid])) {
                $cache[$mid] = array();
            }

            $cache[$mid][] = $meetingAssign;
        }

        $this->_meetingAssignCache[$projectId] = $cache;

        return $cache;
    }

    protected function _getAllEvents()
    {
        static $cache = null;

        if ($cache === null) {
            $cache = $this->getService('Event')->fetchAll();
        }

        return $cache;
    }

    public function getCourseProgress($projectId, $userId)
    {
        $maxValue = $this->calcMaxTotalValue($projectId);
        $userValue = $this->calcTotalValue($projectId, $userId);
        $maxValueOfStudents = $this->calcMaxTotalValueOfStudents($projectId);

        $projectService = $this->getService('Project');
        $project = $projectService->getOne($projectService->find($projectId));

        return array(
            'value' => $userValue,
            'maxValue' => $maxValue,
            'maxValueOfStudents' => $maxValueOfStudents,
            'threshold' => $project->threshold
        );
    }

    /**
     * Подсчитывает лучший результат среди текущих слушаталей курса
     *
     * @param $projectId
     * @return int|number
     */
    public function calcMaxTotalValueOfStudents($projectId)
    {
        $cache = $this->_loadMeetingAssignCache($projectId);
        $max = 0;

        foreach ($cache as $mid => $meetingAssigns) {
            $userTotal = $this->calcTotalValue($projectId, $mid);

            if ($userTotal > $max) {
                $max = $userTotal;
            }
        }

        return $max;
    }



    /**
     * Вероятно, функцию можно значительно упростить... Но нет времени
     * Написано на основе calcTotalValue
     *
     * @param $projectId
     * @return number
     */
    public function calcMaxTotalValue($projectId)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        $formulaService = $this->getService('Formula');
        $meetingService = $this->getService('Meeting');

        $meetings = $meetingService->fetchAll($meetingService->quoteInto(array(
            'CID = ? AND ',
            'isfree = ? AND ',
            'vedomost = ?'
        ), array(
            $projectId,
            HM_Meeting_MeetingModel::MODE_PLAN,
            1
        )));

        $events = $this->_getAllEvents();
        $eventWeights = $events->getList('event_id', 'weight');
        $eventScales = $events->getList('event_id', 'scale_id');

        $meetingsByType = $avgByType = $weightsByType = array();

        foreach ($meetings as $meeting) {

            if (!isset($meetingsByType[$meeting->typeID])) {

                $scaleId = isset($eventScales[-$meeting->typeID]) ? $eventScales[-$meeting->typeID] : $meeting->getScale();

                list($min, $max) = HM_Scale_ScaleModel::getRange($scaleId);

                $meetingsByType[$meeting->typeID] = array(
                    'sum' => 0,
                    'count' => 0,
                    'min' => $min,
                    'max' => $max,
                );

                $weightsByType[$meeting->typeID] = isset($eventWeights[-$meeting->typeID]) ? $eventWeights[-$meeting->typeID] : HM_Event_EventModel::WEIGHT_DEFAULT;
            }

            $meetingParams = $meeting->getParams();
            $meetingMark   = $max;
            // нормализация оценки по формуле под шкалу
            /**
             * @todo: пока сделано для тестов для остальных типов занятий при создании в параметр formula_id при автоматичестом выставлении всегда записывается 1 как ИД формулы, что все портит
             */
            if ( isset($meetingParams['formula_id']) && $meeting->getType() == HM_Event_EventModel::TYPE_TEST) {
                $formula = $formulaService->getById($meetingParams['formula_id']);
                if ( $formula ) {
                    $formulaMarks = $formulaService->getFormulaMarksByScale($formula->formula, $min, $max);
                    if ( $formulaMarks && isset($formulaMarks[$max]) ) {
                        $meetingMark = $formulaMarks[$max];
                    }
                }
            }

            $meetingsByType[$meeting->typeID]['sum'] += $meetingMark;
            $meetingsByType[$meeting->typeID]['count']++;

        }


        HM_Event_EventService::normalizeWeights($weightsByType);

        foreach ($meetingsByType as $typeId => $values) {
            $avgByType[$typeId] = (100 * $weightsByType[$typeId] * $values['sum']) / ($values['count'] * ($values['max'] - $values['min']));
        }

        return array_sum($avgByType);

    }

    public function calcTotalValue($projectId, $userId, $throwExceptionIfMeetingStatusIsNA = false)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        $formulaService = $this->getService('Formula');

        $meetingAssigns = $this->_getMeetingAssign($userId, $projectId);

        $events = $this->_getAllEvents();
        $eventWeights = $events->getList('event_id', 'weight');
        $eventScales = $events->getList('event_id', 'scale_id');

        $meetingsByType = $avgByType = $weightsByType = array();

        foreach ($meetingAssigns as $meetingAssign) {

            if ($meetingAssign->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {

                if ($throwExceptionIfMeetingStatusIsNA) {
                    throw new HM_Exception(_('Курс пройден не полностью'));
                }

                continue;
            }

            $meeting = $meetingAssign->meetings->current();

            $scaleId = isset($eventScales[-$meeting->typeID]) ? $eventScales[-$meeting->typeID] : $meeting->getScale();

            list($min, $max) = HM_Scale_ScaleModel::getRange($scaleId);

            if (!isset($meetingsByType[$meeting->typeID])) {

                $meetingsByType[$meeting->typeID] = array(
                    'sum' => 0,
                    'count' => 0,
                    'min' => $min,
                    'max' => $max,
                );

                $weightsByType[$meeting->typeID] = isset($eventWeights[-$meeting->typeID]) ? $eventWeights[-$meeting->typeID] : HM_Event_EventModel::WEIGHT_DEFAULT;
            }

            $meetingParams = $meeting->getParams();
            $meetingMark   = $meetingAssign->V_STATUS;
            // нормализация оценки по формуле под шкалу
            /**
             * @todo: пока сделано для тестов для остальных типов занятий при создании в параметр formula_id при автоматичестом выставлении всегда записывается 1 как ИД формулы, что все портит
             */
            if ( isset($meetingParams['formula_id']) && $meeting->getType() == HM_Event_EventModel::TYPE_TEST) {
                $formula = $formulaService->getById($meetingParams['formula_id']);
                if ( $formula ) {
                    $formulaMarks = $formulaService->getFormulaMarksByScale($formula->formula, $min, $max);
                    if ( $formulaMarks && isset($formulaMarks[$meetingMark]) ) {
                        $meetingMark = $formulaMarks[$meetingMark];
                    }
                }
            }

            $meetingsByType[$meeting->typeID]['sum'] += $meetingMark;
            $meetingsByType[$meeting->typeID]['count']++;

        }


        HM_Event_EventService::normalizeWeights($weightsByType);

        foreach ($meetingsByType as $typeId => $values) {
            $avgByType[$typeId] = (100 * $weightsByType[$typeId] * $values['sum']) / ($values['count'] * ($values['max'] - $values['min']));
        }

        return array_sum($avgByType);

    }

    public function onMeetingScoreChanged($projectId, $userId)
    {
        $projectService = $this->getService('Project');
        $project = $projectService->getOne($projectService->find($projectId));

        try {
            $total = $this->calcTotalValue($projectId, $userId, true);
        } catch (HM_Exception $e) {
            return;
        }

        if ($project->auto_mark) {

            switch ($project->getScale()) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    if ($total >= $project->threshold) {
                        $mark = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                    if (!empty($project->threshold)) {
                        if ($total >= $project->threshold) {
                            $mark = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                        } else {
                            $mark = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                        }
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                    if ($project->formula_id && ($formula = $this->getService('Formula')->getById($project->formula_id))) {
                        $mark = $formula->getResultValue((int)$total);
                    } else {
                        $mark = (int)$total;
                    }
                    break;
            }

            $data = array(
                'cid' => $projectId,
                'mid' => $userId,
                'mark' => $mark,
                'confirmed' => HM_Project_Mark_MarkModel::MARK_NOT_CONFIRMED,
            );

            $collection = $this->fetchAll(array(
                'cid = ?' => $projectId,
                'mid = ?' => $userId
            ));

            if (count($collection)) {
                $this->updateWhere($data, array(
                    'cid = ?' => $projectId,
                    'mid = ?' => $userId
                ));
            } else {
                $this->insert($data);
            }
        }

        if ($project->auto_graduate) {
            $projectService->assignGraduated($projectId, $userId);
        }

        return $mark;
    }
    
    public function isConfirmationNeeded($projectId, $userId)
    {
        $collection = $this->fetchAll(array(
            'cid = ?' => $projectId,        
            'mid = ?' => $userId,        
            'confirmed = ?' => HM_Project_Mark_MarkModel::MARK_NOT_CONFIRMED,        
        ));  
        return count($collection);
    }    
    
    public function setConfirmed($projectId, $userId)
    {
        $collection = $this->fetchAll(array(
            'cid = ?' => $projectId,        
            'mid = ?' => $userId,        
            'confirmed = ?' => HM_Project_Mark_MarkModel::MARK_NOT_CONFIRMED,     
        ));
        
        if (count($collection)) {
            $this->updateWhere(array(
                'confirmed' => HM_Project_Mark_MarkModel::MARK_CONFIRMED,
            ), array(
                'cid = ?' => $projectId,        
                'mid = ?' => $userId,        
                'confirmed = ?' => HM_Project_Mark_MarkModel::MARK_NOT_CONFIRMED,     
            ));
            return $collection->current();
        }
        return false;
    }
}
