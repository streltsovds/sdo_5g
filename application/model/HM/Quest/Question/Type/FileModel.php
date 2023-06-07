<?php
class HM_Quest_Question_Type_FileModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    public function getResult($questAttemptId)
    {
        $return = array();
        $elementId = "results_{$this->question_id}";
        $element = new HM_Form_Element_Html5File($elementId);

        if($element->isUploaded()){
            $element->receive();
            if ($element->isReceived()) {
                $filename = $element->getFileName();
                $return['free_variant'] = basename($filename);
            }          
            
            $questionPath = Zend_Registry::get('config')->path->upload->quests . '/' . $this->question_id;
            if (!is_dir($questionPath)) {
                mkdir($questionPath, 0755);
            }            
            
            $filter = new Zend_Filter_File_Rename(
                array(
                    'source' => $filename,
                    'target' => $questionPath . '/' . $questAttemptId,
                    'overwrite' => true
                )
            );
            $filter->filter($filename);
        }        
        return $return;
    }
    
    public function isCorrect($value)
    {
    }

    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        $url = Zend_Registry::get('view')->url(array('module' => 'file', 'controller' => 'get', 'action' => 'quest', 'attempt_id' => $questionResult->attempt_id,  'question_id' => $this->question_id, 'baseUrl' => ''));
        return "<a href='{$url}'>{$questionResult->free_variant}</a>";
    }     
}