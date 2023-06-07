<?php
class HM_Subject_Task_TaskService extends HM_Service_Abstract
{
    
    

    /**
     * Возвращает массив с моделями, в которых subject_id -> парент
     * @param unknown_type $courseId
     * @return multitype:
     */
    public function getCourseParent($courseId){
        $ret = array();

        $ret = $this->fetchAll(array('course_id = ?' => $courseId));
             
        return $ret;
    }
    
    public function link($taskId, $subjectId)
    {
        $result = $this->fetchRow([
            'task_id = ?'  => $taskId,
            'subject_id= ?' => $subjectId,
        ]);

        if(!$result) {
            $result = $this->insert([
                'task_id' => $taskId,
                'subject_id' => $subjectId,
            ]);
        }

        return $result;
    }
}