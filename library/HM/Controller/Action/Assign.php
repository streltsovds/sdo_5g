<?php
/**
 *
 */

abstract class HM_Controller_Action_Assign extends HM_Controller_Action
{
    // Коды возврата
    const RETCODE_DOACTION_OK            = 0; // Ошибок не было. В данный момент никак не используется.
    const RETCODE_DOACTION_END_ITERATION = 1; // Ошибка затрагивает только текущую интераци, продолжение интерации не возможно.
    const RETCODE_DOACTION_END_LOOP      = 2; // Ошибка делает невозможным продолжение всего цикла.

    abstract public function indexAction();
    abstract public function assignAction();
    abstract public function unassignAction();

    abstract protected function _preAssign($personId, $courseId);
    abstract protected function _postAssign($personId, $courseId);
    abstract protected function _assign($personId, $courseId);
    abstract protected function _unassign($personId, $courseId);
    abstract protected function _preUnassign($personId, $courseId);
    abstract protected function _postUnassign($personId, $courseId);
    abstract protected function _finishAssign();
    abstract protected function _finishUnassign();

    /**
     * Иногда параметр приходит как Array, иногда как строка, разбитая по запятой, в зависимости от грида, например, /assign/teacher/assign с subject_id = 0 или > 0
     *
     * @param $postIds
     * @return array
     */
    protected function checkPostIds($postIds): array
    {
        if (is_string($postIds) && strpos($postIds, ',') !== false)
            $postIds = explode(',', $postIds);

        return is_array($postIds) ? $postIds : [$postIds];
    }
   
}