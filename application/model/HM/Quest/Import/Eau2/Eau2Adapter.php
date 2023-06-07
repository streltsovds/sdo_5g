<?php
class HM_Quest_Import_Eau2_Eau2Adapter implements HM_Adapter_Interface
{
    private $_filename = null;

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

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $tests = array();
        if ((null !== $this->_filename) && file_exists($this->_filename) && is_readable($this->_filename)) {
            
            $courseXml = $this->_getXml();
            $xml = new SimpleXMLElement($courseXml);
            if (!$xml) {
                throw new HM_Exception(sprintf(_('Неверный формат файлы xml: %s'), 'course.xml'));
            }
            
            if (count($xmlTests = $xml->xpath('//test'))) {
                foreach ($xmlTests as $xmlTest) {
                    
                    $self = (string)$xmlTest->attributes()->title;
                    $items = $xmlTest->xpath("ancestor::item");
                    $studiedproblems = $xmlTest->xpath("ancestor::studiedproblem");
                    
                    $name = $self;
                    if (count($studiedproblems)) {
                        $name = sprintf('%s / %s', $studiedproblems[0]->attributes()->title, $name);
                    }
                    if (count($items)) {
                        $name = sprintf('%s / %s', $items[0]->attributes()->title, $name);
                    }
                    
                    $test = array(
                        'name' => $name,
                        'questions' => array(),
                    );
                    
                    if (count($xmlQuestions = $xmlTest->question)) {
                        foreach ($xmlQuestions as $xmlQuestion) {
                            $question = array(
                                'type' => $type = $this->_mapType((string)$xmlQuestion->answers->attributes()->type),
                                'shorttext' => (string)$xmlQuestion->attributes()->title,
                                'question' => (string)$xmlQuestion->text,
                                'variants' => array(),
                            );

                            // @todo: refactor if more different types
                            if ($type == HM_Quest_Question_QuestionModel::TYPE_TEXT) {
                                $xmlVariants = array(
                                    $xmlQuestion->answers->answer->attributes()->right
                                );
                            } else {
                                $xmlVariants = $xmlQuestion->answers->answer;
                            }
                            if (count($xmlVariants)) {
                                foreach ($xmlVariants as $xmlVariant) {
                                    $variant = array(
                                        'variant' => html_entity_decode(strip_tags($xmlVariant)),
                                        'is_correct' => (is_object($xmlVariant)) ? ((string)$xmlVariant->attributes()->type == 'true') : true,
                                    );
                                    $question['variants'][] = $variant;                            
                                }
                            }
                            $test['questions'][] = $question;
                        }
                    }
                    $tests[] = $test; 
                }
            }
            @unlink($this->_filename);
        }
        return $tests;
    }
    
    private function _mapType($eauType) 
    {
        switch ($eauType) {
            case 'single':
                return HM_Quest_Question_QuestionModel::TYPE_SINGLE;
            case 'multiple':
                return HM_Quest_Question_QuestionModel::TYPE_MULTIPLE;
            case 'fill':
                return HM_Quest_Question_QuestionModel::TYPE_TEXT;
        }
        return HM_Quest_Question_QuestionModel::TYPE_SINGLE;
    }
    
    static public function __toArray($xml)
    {
        $arr = array();
        foreach ($xml->attributes() as $key => $value) {
            $arr[$key] = $value;
        }
        return $arr;
    }
    
    private function _getXml()
    {
        $zip = new ZipArchive();
        if (!$zip->open($this->_filename)) {
            throw new HM_Exception(sprintf(_('Невозможно открыть архив: %s'), $this->_filename));
        }
    
        $fp = $zip->getStream('course.xml');
        if (!$fp) {
            throw new HM_Exception(_('Невозможно прочитать файл course.xml'));
        }
    
        $xml = '';
        while(!feof($fp)) {
            $xml .= fread($fp, 1024);
        }
        fclose($fp);
    
        $zip->close();
    
        return $xml;
    }    
}