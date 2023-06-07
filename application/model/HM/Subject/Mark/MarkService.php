<?php
//require_once($_SERVER['DOCUMENT_ROOT']."/formula_calc.php");

class HM_Subject_Mark_MarkService extends HM_Service_Abstract 
{

    protected $userId = null;
    public $lastHmException = null;

    public function setUserId($id) {
        $this->userId = $id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function update($data, $unsetNull = true)
    {
        $mark = parent::update($this->_updateData($data));
        $this->removeCache($data);
        return $mark;
    }

    public function insert($data, $unsetNull = true)
    {
        $mark = parent::insert($this->_updateData($data));
        $this->removeCache($data);
        return $mark;
    }

    public function updateWhere($data, $where) {

        $updateResult = parent::updateWhere($this->_updateData($data), $where);
        $this->removeCache($data);
        return $updateResult;
    }

    public function getRelatedUserList($id) {
        return array(intval($this->userId));
    }

    // кэширующие функции для работы calcTotalValue (иногда может вызываться много раз
    protected $_lessonAssignCache = array();

    protected function _getLessonAssign($userId, $subjectId)
    {
        $cache = &$this->_lessonAssignCache;

        if (!isset($cache[$subjectId])) {
            $cache[$subjectId] = array();
        }

        if (!isset($cache[$subjectId][$userId])) {

            $lessonAssignService = $this->getService('LessonAssign');

            $cache[$subjectId][$userId] = $lessonAssignService->fetchAllDependenceJoinInner('Lesson', $lessonAssignService->quoteInto(array(
                'self.MID = ? AND ',
                'Lesson.CID = ? AND ',
                'Lesson.isfree = ? AND ',
                'Lesson.vedomost = ?'
            ), array(
                $userId,
                $subjectId,
                HM_Lesson_LessonModel::MODE_PLAN,
                HM_Lesson_LessonModel::MARK_ON
            )));
        }

        return $cache[$subjectId][$userId];

    }

    protected function _loadLessonAssignCache($subjectId)
    {
        $cache = array();

        $lessonAssigns = $this->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $this->getService('LessonAssign')->quoteInto(array(
            'Lesson.CID = ? AND ',
            'Lesson.isfree = ? AND ',
            'Lesson.vedomost = 1'
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_PLAN
        )));

        foreach ($lessonAssigns as $lessonAssign) {
            $mid = $lessonAssign->MID;

            if (!isset($cache[$mid])) {
                $cache[$mid] = array();
            }

            $cache[$mid][] = $lessonAssign;
        }

        $this->_lessonAssignCache[$subjectId] = $cache;

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

    public function getCourseProgress($subjectId, $userId)
    {
        $maxValue = $this->calcMaxTotalValue($subjectId);
        $userValue = $this->calcTotalValue($subjectId, $userId);
        $maxValueOfStudents = $this->calcMaxTotalValueOfStudents($subjectId);

        $subjectService = $this->getService('Subject');
        $subject = $subjectService->getOne($subjectService->find($subjectId));

        return array(
            'value' => $userValue,
            'maxValue' => $maxValue,
            'maxValueOfStudents' => $maxValueOfStudents,
            'threshold' => $subject->threshold
        );
    }

    /**
     * Подсчитывает лучший результат среди текущих слушаталей курса
     *
     * @param $subjectId
     * @return int|number
     */
    public function calcMaxTotalValueOfStudents($subjectId)
    {
        $cache = $this->_loadLessonAssignCache($subjectId);
        $max = 0;

        foreach ($cache as $mid => $lessonAssigns) {
            $userTotal = $this->calcTotalValue($subjectId, $mid);

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
     * @param $subjectId
     * @return number
     */
    public function calcMaxTotalValue($subjectId)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        $formulaService = $this->getService('Formula');
        $lessonService = $this->getService('Lesson');

        $lessons = $lessonService->fetchAll($lessonService->quoteInto(array(
            'CID = ? AND ',
            'isfree = ? AND ',
            'vedomost = ?'
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_PLAN,
            1
        )));

        $events = $this->_getAllEvents();
        $eventWeights = $events->getList('event_id', 'weight');
        $eventScales = $events->getList('event_id', 'scale_id');

        $lessonsByType = $avgByType = $weightsByType = array();

        foreach ($lessons as $lesson) {

            if (!isset($lessonsByType[$lesson->typeID])) {

                $scaleId = isset($eventScales[-$lesson->typeID]) ? $eventScales[-$lesson->typeID] : $lesson->getScale();

                list($min, $max) = HM_Scale_ScaleModel::getRange($scaleId);

                $lessonsByType[$lesson->typeID] = array(
                    'sum' => 0,
                    'count' => 0,
                    'min' => $min,
                    'max' => $max,
                );

                $weightsByType[$lesson->typeID] = isset($eventWeights[-$lesson->typeID]) ? $eventWeights[-$lesson->typeID] : HM_Event_EventModel::WEIGHT_DEFAULT;
            }

            $lessonParams = $lesson->getParams();
            $lessonMark   = $max;
            // нормализация оценки по формуле под шкалу
            /**
             * @todo: пока сделано для тестов для остальных типов занятий при создании в параметр formula_id при автоматичестом выставлении всегда записывается 1 как ИД формулы, что все портит
             */
            if ( isset($lessonParams['formula_id']) && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
                $formula = $formulaService->getById($lessonParams['formula_id']);
                if ( $formula ) {
                    $formulaMarks = $formulaService->getFormulaMarksByScale($formula->formula, $min, $max);
                    if ( $formulaMarks && isset($formulaMarks[$max]) ) {
                        $lessonMark = $formulaMarks[$max];
                    }
                }
            }

            $lessonsByType[$lesson->typeID]['sum'] += $lessonMark;
            $lessonsByType[$lesson->typeID]['count']++;

        }


        HM_Event_EventService::normalizeWeights($weightsByType);

        foreach ($lessonsByType as $typeId => $values) {
            $avgByType[$typeId] = (100 * $weightsByType[$typeId] * $values['sum']) / ($values['count'] * ($values['max'] - $values['min']));
        }

        return array_sum($avgByType);

    }

    public function calcTotalValue($subjectId, $userId, $throwExceptionIfLessonStatusIsNA = false)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        $formulaService = $this->getService('Formula');

        $lessonAssigns = $this->_getLessonAssign($userId, $subjectId);

        $events = $this->_getAllEvents();
        $eventWeights = $events->getList('event_id', 'weight');
        $eventScales = $events->getList('event_id', 'scale_id');

        $lessonsByType = $avgByType = $weightsByType = array();

        foreach ($lessonAssigns as $lessonAssign) {

            if ($lessonAssign->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {

                if ($throwExceptionIfLessonStatusIsNA) {
                    throw new HM_Exception(_('Курс пройден не полностью'));
                }

                continue;
            }

            $lesson = $lessonAssign->lessons->current();

            $scaleId = isset($eventScales[-$lesson->typeID]) ? $eventScales[-$lesson->typeID] : $lesson->getScale();

            list($min, $max) = HM_Scale_ScaleModel::getRange($scaleId);

            if (!isset($lessonsByType[$lesson->typeID])) {

                $lessonsByType[$lesson->typeID] = array(
                    'sum' => 0,
                    'count' => 0,
                    'min' => $min,
                    'max' => $max,
                );

                $weightsByType[$lesson->typeID] = isset($eventWeights[-$lesson->typeID]) ? $eventWeights[-$lesson->typeID] : HM_Event_EventModel::WEIGHT_DEFAULT;
            }

            $lessonParams = $lesson->getParams();
            $lessonMark   = $lessonAssign->V_STATUS;
            // нормализация оценки по формуле под шкалу
            /**
             * @todo: пока сделано для тестов для остальных типов занятий при создании в параметр formula_id при автоматичестом выставлении всегда записывается 1 как ИД формулы, что все портит
             */
            if ( isset($lessonParams['formula_id']) && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
                $formula = $formulaService->getById($lessonParams['formula_id']);
                if ( $formula ) {
                    $formulaMarks = $formulaService->getFormulaMarksByScale($formula->formula, $min, $max);
                    if ( $formulaMarks && isset($formulaMarks[$lessonMark]) ) {
                        $lessonMark = $formulaMarks[$lessonMark];
                    }
                }
            }

            $lessonsByType[$lesson->typeID]['sum'] += $lessonMark;
            $lessonsByType[$lesson->typeID]['count']++;

        }


        HM_Event_EventService::normalizeWeights($weightsByType);

        foreach ($lessonsByType as $typeId => $values) {
            $avgByType[$typeId] = (100 * $weightsByType[$typeId] * $values['sum']) / ($values['count'] * ($values['max'] - $values['min']));
        }

        return array_sum($avgByType);

    }

    /**
     * @param int $subjectId
     * @param int $userId
     * @param bool $forceManualUpdate
     * @param bool $rethrowHmException
     *
     * @return int - mark
     * @throws HM_Exception
     */
    public function onLessonScoreChanged($subjectId, $userId, $forceManualUpdate = false, $rethrowHmException = false)
    {
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->getOne($subjectService->find($subjectId));

        try {
            $total = $this->calcTotalValue($subjectId, $userId, true);
        } catch (HM_Exception $e) {
            if ($rethrowHmException) {
                throw $e;
            }
            return;
        }

        if ($subject->auto_mark || $forceManualUpdate) {

            switch ($subject->getScale()) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    if ($total >= $subject->threshold) {
                        $mark = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                    if (!empty($subject->threshold)) {
                        if ($total >= $subject->threshold) {
                            $mark = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                        } else {
                            $mark = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                        }
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                    if ($subject->formula_id && ($formula = $this->getService('Formula')->getById($subject->formula_id))) {
                        $mark = $formula->getResultValue((int)$total);
                    } else {
                        $mark = (int)$total;
                    }
                    break;
            }

            $data = array(
                'cid' => $subjectId,
                'mid' => $userId,
                'mark' => $mark,
                'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
            );

            $collection = $this->fetchAll(array(
                'cid = ?' => $subjectId,
                'mid = ?' => $userId
            ));

            if (count($collection)) {
                $this->updateWhere($data, array(
                    'cid = ?' => $subjectId,
                    'mid = ?' => $userId
                ));
            } else {
                $this->insert($data);
            }
        }

        if ($subject->auto_graduate) {
            $subjectService->assignGraduated($subjectId, $userId);
        }

        return $mark;
    }

    public function isConfirmationNeeded($subjectId, $userId)
    {
        $collection = $this->fetchAll(array(
            'cid = ?' => $subjectId,
            'mid = ?' => $userId,
            'confirmed = ?' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
        ));
        return count($collection);
    }

    public function setConfirmed($subjectId, $userId)
    {
        $collection = $this->fetchAll(array(
            'cid = ?' => $subjectId,
            'mid = ?' => $userId,
            'confirmed = ?' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
        ));

        if (count($collection)) {
            $this->updateWhere(array(
                'confirmed' => HM_Subject_Mark_MarkModel::MARK_CONFIRMED,
            ), array(
                'cid = ?' => $subjectId,
                'mid = ?' => $userId,
                'confirmed = ?' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
            ));
            return $collection->current();
        }
        return false;
    }

    private function _updateData($data)
    {
        if($data['mark'])
            $data['mark'] = HM_Subject_Mark_MarkModel::filterMark($data['mark']);

        $data['date'] = $this->getDateTime();

        return $data;
    }

    /**
     * @param $data
     */
    protected function removeCache($data)
    {

        // HM_Cache::removeSubjectRoughProgress($data['mid'], $data['cid']);
    }

}
