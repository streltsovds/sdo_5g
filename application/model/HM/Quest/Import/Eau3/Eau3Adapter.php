<?php
class HM_Quest_Import_Eau3_Eau3Adapter implements HM_Adapter_Interface
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

                    $questAttributes = $xmlTest->attributes();
                    $items = $xmlTest->xpath("ancestor::item");
                    $studiedproblems = $xmlTest->xpath("ancestor::studiedproblem");
                    
                    $name = (string)$questAttributes['title'];
                    if (count($studiedproblems)) {
                        $name = sprintf('%s / %s', $studiedproblems[0]->attributes()->title, $name);
                    }
                    if (count($items)) {
                        $name = sprintf('%s / %s', $items[0]->attributes()->title, $name);
                    }
                    
                    $test = array(
                        'name' => (string)$questAttributes['title'],
                        'show_result' => ($questAttributes['show-test-stats'] == "true"),
                        'show_log' => ($questAttributes['show-test-stats'] == "true"), // ?
                        'limit_time' => $questAttributes['time-limit']/60000,
                        'questions' => array(),
                    );

                    if ($questAttributes['number-of-questions']) {
                        $test['mode_selection'] = HM_Quest_QuestModel::MODE_SELECTION_LIMIT;
                        $test['mode_selection_questions'] = (string)$questAttributes['number-of-questions'];
                    } elseif ($questAttributes['shuffle-questions'] == "true") {
                        $test['mode_selection'] = HM_Quest_QuestModel::MODE_SELECTION_ALL;
                        $test['mode_selection_all_shuffle'] = 1;
                    }

                    if (count($xmlQuestions = $xmlTest->question)) {
                        foreach ($xmlQuestions as $xmlQuestion) {

                            $questionAttributes = $xmlQuestion->attributes();
                            $type = $this->_mapType((string)$questionAttributes['type']);

                            $method = '_' . $type;
                            $question = $this->{$method}($xmlQuestion);
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

    private function _alltypes($xmlQuestion)
    {
        $questionAttributes = $xmlQuestion->attributes();

        $scoreMax = 1;
        if (isset($questionAttributes['score-max'])) {
            $scoreMax = (int)$questionAttributes['score-max'];
        }

        $question = array(
            'type' => $this->_mapType((string)$questionAttributes['type']),
            'shorttext' => (string)$questionAttributes['title'],
            'question' => (string)$xmlQuestion->text,
            'score_max' => $scoreMax,
            'variants' => array(),
        );
        return $question;
    }

    private function _single($xmlQuestion)
    {
        $question = $this->_alltypes($xmlQuestion);
        $xmlVariants = $xmlQuestion->answers->answer;

        if (count($xmlVariants)) {
            foreach ($xmlVariants as $xmlVariant) {
                $variantAttributes = $xmlVariant->attributes();
                $variant = array(
                    'variant' => (string)$xmlVariant,
                    'is_correct' => (is_object($xmlVariant)) ? ((string)$variantAttributes['type'] == 'true') : true,
                );
                $question['variants'][] = $variant;
            }
        }
        return $question;
    }
    
    private function _multiple($xmlQuestion)
    {
        // такой же
        return $this->_single($xmlQuestion);
    }

    private function _text($xmlQuestion)
    {
        $question = $this->_alltypes($xmlQuestion);
        $xmlVariants = $xmlQuestion->answers->answer;

        if (count($xmlVariants)) {
            foreach ($xmlVariants as $xmlVariant) {
                $variantAttributes = $xmlVariant->attributes();
                $variant = array(
                    'variant' => str_replace(array('"', '[', ']'), '', html_entity_decode($variantAttributes['right'])),
                );
                $question['variants'][] = $variant;
            }
        }
        return $question;
    }

    private function _placeholder($xmlQuestion)
    {
        $question = $this->_alltypes($xmlQuestion);
        $questionAttributes = $xmlQuestion->attributes();
        $xmlVariants = $xmlQuestion->answers->answer;

        $stubs = str_replace(array('[', ']'), '', html_entity_decode($questionAttributes['stubs']));
        $stubs = explode(',',$stubs);

        $stubs = array_map(function($val) {
            return HM_Quest_Question_Type_PlaceholderModel::VARIANT_WRONG_MARKER.str_replace('"','', $val);
        }, $stubs);

        if (count($xmlVariants)) {
            $placeholders = array();
            $tvariants = array();
            foreach ($xmlVariants as $xmlVariant) {
                $variantAttributes = $xmlVariant->attributes();
                $variant = str_replace(array('[', ']'), '', html_entity_decode($variantAttributes['right']));
                $variant = explode(',',$variant);

                $variant = array_map(function($val) {
                    return str_replace('"','', $val);
                }, $variant);


                $displayMode = HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_INPUT;
                if ($variantAttributes['dd'] == 't') {
                    $variant = array_merge($variant,$stubs);
                    if ($variantAttributes['multiple'] == 'f') {
                        $displayMode = HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_SELECT;
                    } elseif ($variantAttributes['multiple'] == 't') {
                        $displayMode = HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_MULTYSELECT;
                    }
                }

                shuffle($variant);


                $variant = array(
                    'variant' => implode(';',$variant),
                    'is_correct' => true,
                    'data' => serialize(array('mode_display' => $displayMode)),
                );
                $placeholder = '/<A (.*) InnerLink="EUL:'.$variantAttributes['DB_ID'].'">(.*?)<\/A>/i';
                $placeholders[] = $placeholder;
                $tvariants[strpos($question['question'], 'EUL:'.$variantAttributes['DB_ID'])] = $variant;
            }

            ksort($tvariants);
            foreach ($tvariants as  $variant) {
                $question['variants'][] = $variant;
            }
            foreach ($placeholders as  $placeholder) {
                $question['question'] = preg_replace($placeholder, '[]', $question['question']);
            }
        }

        return $question;
    }
    private function _mapping($xmlQuestion)
    {
        $question = $this->_alltypes($xmlQuestion);
        $xmlVariants = $xmlQuestion->answers->answer;

        if (count($xmlVariants)) {
            foreach ($xmlVariants as $xmlVariant) {
                $variantAttributes = $xmlVariant->attributes();
                $variant = array(
                    'variant' => str_replace(array('"', '[', ']'), '', html_entity_decode($variantAttributes['right'])),
                    'data' => (string)$xmlVariant,
                );
                $question['variants'][] = $variant;
            }
        }
        return $question;
    }

    private function _sorting($xmlQuestion)
    {
        $question = $this->_alltypes($xmlQuestion);
        $xmlVariants = $xmlQuestion->answers->answer;

        if (count($xmlVariants)) {
            $i = 1;
            foreach ($xmlVariants as $xmlVariant) {
                $variantAttributes = $xmlVariant->attributes();
                $variant = array(
                    'variant' => (string)$xmlVariant,
                    'data' => $i++,
                );
                $question['variants'][] = $variant;
            }
        }
        return $question;
    }

    private function _classification($xmlQuestion)
    {
        $question = $this->_alltypes($xmlQuestion);
        $xmlVariants = $xmlQuestion->answers->answer;

        if (count($xmlVariants)) {
            $i = 1;
            foreach ($xmlVariants as $xmlVariant) {
                $variantAttributes = $xmlVariant->attributes();
                $variant = array(
                    'variant' => (string)$variantAttributes['class-group'],
                    'data' => (string)$xmlVariant,
                );
                $question['variants'][] = $variant;
            }
        }
        return $question;
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
            case 'fillingaps':
                return HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER;
            case 'compare':
                return HM_Quest_Question_QuestionModel::TYPE_MAPPING;
            case 'sort':
                return HM_Quest_Question_QuestionModel::TYPE_SORTING;
            case 'classify':
                return HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION;
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