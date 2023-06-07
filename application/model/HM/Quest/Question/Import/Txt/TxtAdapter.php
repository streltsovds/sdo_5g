<?php
class HM_Quest_Question_Import_Txt_TxtAdapter implements HM_Adapter_Interface
{
    private $_filename = null;
    private $_questions = null;

    public function __construct($filename = null)
    {
        $this->_filename = $filename;
    }

    public function setFileName($filename)
    {
        $this->_filename = $filename;
    }

    public function needToUploadFile()
    {
        return true;
    }

    public function isTest($unlinkTempFile = true)
    {
        $questions = $this->fetchAllCore(null, null, null, null, $unlinkTempFile);

        $answers = array_column($questions, 'answers');
        $answersCount = array_map('count', $answers);

        // Тест - это когда есть вопросы && на каждый из них есть более чем по одному ответу
        $isTest = count($questions) > 0 && count($questions) == count(array_filter($answersCount, function ($value) {
                return $value > 1;
            }));

        return $isTest;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->fetchAllCore($where, $order, $count, $offset, true);
    }

    public function fetchAllCore($where = null, $order = null, $count = null, $offset = null, $unlinkTempFile = true)
    {
        $questions = array();
        if ((null !== $this->_filename) && file_exists($this->_filename) && is_readable($this->_filename)) {
            HM_Files_FilesService::detectFileEncoding($this->_filename);
            if ($lines = file($this->_filename)) {
                for($i=0; $i<=count($lines); $i++) {
                    $line = $lines[$i];
                    if (preg_match('/^[0-9]+\.(.+)/u', $line, $matches)) {

                        if (isset($matches[1])) {
                            $question = trim($matches[1]);
                            $answers = array();
                            $type = HM_Quest_Question_QuestionModel::TYPE_SINGLE;
                            $true = 0;

                            // Собираем ответы
                            for($j=$i+1; $j<=count($lines);$j++) {
                                $line = $lines[$j];
                                if (preg_match('/^[0-9]+\.(.+)/u', $line, $matches) || $j == (count($lines))) {

                                    if (count($answers)) {
                                        if ($true > 1) $type = HM_Quest_Question_QuestionModel::TYPE_MULTIPLE;
                                        $questions[] = array('question' => $question,  'type' => $type, 'answers' => $answers);
                                    }
                                    break;
                                }

                                if (preg_match('/^\(([\?\!])\)(.+)/u', $line, $matches)) {
                                    if (isset($matches[1]) && isset($matches[2])) {
                                        if ($matches[1] == '!') $true++;
                                        $answers[] = array('is_correct' => ($matches[1] == '!') , 'variant' => trim($matches[2]));
                                    }
                                }
                            }
                        }
                    }
                }
                $this->_questions = $questions;
            }
            if ($unlinkTempFile) @unlink($this->_filename);
        }

        return $questions ?: $this->_questions;
    }
}