<?php
//..!!! А еще на перспективу надо сделать анализ всех маркеров, чтобы авторы шаблонов не писали маркеры, которые могут быть один частью другого - будет некорректная замена
class HM_PrintForm
{
    const FORM_1_1 = 1;//Тестовая форма на базе ф.1.1
    const FORM_ADAPTATION_PLAN = 2;//Форма для плана адаптации
    const FORM_STUDY_PLAN = 3;//План обучения, повышения квалификации и обязательной аттестации пользователей
    const FORM_STUDY_PLAN_MANAGER = 4;//См. выше, но для руководителей
    const FORM_STUDY_JOURNAL = 5;//Форма для ???
    const FORM_STUDY_PROTOCOL = 8;
    const FORM_INDIVIDUAL_REPORT = 6;//Форма для индивидуального отчета
    const FORM_QUARTER_PLAN_REPORT = 7;//Форма для плана-отчета в сессии квартального планирования
    const FORM_QUEST_PROTOCOL = 11;
    const FORM_WELCOME_TRAINING = 12;

    const FORM_ROTATION_PLAN = 18;//Форма для плана ротации
    const FORM_ROTATION_REPORT = 19;//Форма для отчёта о ротации

    const FORM_RESERVE_PLAN = 20;//Форма для плана развития
    const FORM_RESERVE_REPORT = 21;//Форма для отчёта о прохождении ИПР

    const TYPE_WORD = 1;
    const TYPE_EXCEL = 2;

    private $_templates = array(
        self::FORM_1_1 => "form_1_1",
        self::FORM_ADAPTATION_PLAN => "form_adaptation_plan",
        self::FORM_ROTATION_PLAN => "form_rotation_plan",
        self::FORM_ROTATION_REPORT => "form_rotation_report",
        self::FORM_RESERVE_PLAN => "form_reserve_plan",
        self::FORM_RESERVE_REPORT => "form_reserve_report",
        self::FORM_STUDY_PLAN => "form_study_plan",
        self::FORM_STUDY_PLAN_MANAGER => "form_study_plan_manager",
        self::FORM_STUDY_JOURNAL => "form_study_journal",
        self::FORM_STUDY_PROTOCOL => "form_study_protocol",
        self::FORM_INDIVIDUAL_REPORT => "form_individual_report",
        self::FORM_QUARTER_PLAN_REPORT => "form_quarter_plan_report",
        self::FORM_QUEST_PROTOCOL => "form_quest_protocol",
        self::FORM_WELCOME_TRAINING => "form_welcome_training",
    );

    private $fileNames = array();
    

    //Главная функция генерации
    public function makePrintForm($type, $templateId, $data, $outFileName, $options=false, $bDownload=true, $files=false, $lastDoc = false)
    {
        $extension = $type==self::TYPE_WORD ? 'docx' : 'xlsx';

        $templatePath = Zend_Registry::get('config')->path->templates->print_forms.$this->_templates[$templateId].".{$extension}";


        if(!file_exists($templatePath)) throw new Exception('шаблон документа не найден');

        $template = file_get_contents($templatePath);
        // В любом случае кладём в temp-папку, а потом удалим, если что
        $tempZipPath = /*$bDownload ? $outFileName : */tempnam(sys_get_temp_dir(), 'DOC_');
        file_put_contents($tempZipPath, $template);

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath)) {
            $contentPath = $type==self::TYPE_WORD ? 'word/document.xml' : 'xl/worksheets/sheet1.xml';
            if (($index = $zip->locateName($contentPath)) !== false) {
                $content = $zip->getFromIndex($index);

                if($type==self::TYPE_EXCEL) {
                    $content = $this->makeCompleteContent($zip, $content);
                    if(!$content)   return false;                    
                }

                $this->_translateInputData($data, $type);

                $out_content = $this->_translateData($type, $content, $data, $options);
                if($out_content===false) {
                    return false;                    
                }

                if($type==self::TYPE_EXCEL) {
                    $out_content = $this->makeLinkedContent($zip, $out_content);
                    if(!$out_content)   return false;                   
                }

                $zip->deleteIndex($index);
                $zip->addFromString($contentPath, $out_content);    
            }
            if($type==self::TYPE_WORD) {
                $this->updateWordFiles($zip, $content, $files);
            }
        }
        $zip->close();

        if(!$bDownload) return true;

        header($type==self::TYPE_WORD ? 'Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document' :
            'Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.document');
        header('Content-Disposition: attachment; filename="'.basename($outFileName).".{$extension}".'"');
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Pragma: public");
        header("Content-Transfer-Encoding: binary");
        echo file_get_contents($tempZipPath);
        unlink($tempZipPath);
        die();
    }

    //Главная процедура замены
    private function _translateData($type, $content, $data, $options)
    {
        $content = $this->_removeTagsInMarkers($content);
        $rowsPatterns = $this->_findAllRows($type, $content);

        if($rowsPatterns===false) {
            return false;
        }
        foreach($data as $marker=>$value) {
///////////////
            if(!is_array($value)) {
                if($options && isset($options[$marker])) {
                    foreach($options[$marker] as $k=>$v) {
                        switch($k){
                            case 'fill':
                                if($type==self::TYPE_WORD) {
                                    $i = 0;
                                    $fillStr = "\r\n<w:shd w:val=\"clear\" w:color=\"auto\" w:fill=\"{$v}\"/>";
                                    $findStr = '<w:tcPr>';
                                    while($posMarker = strpos($content, "[%{$marker}%]", $i)){
                                        $pos = strrpos($content, $findStr, -(strlen($content)-$posMarker));//ищем св-ва родительcкой ячейки
                                        if($pos===false) break;
                                        $content = substr($content, 0, $pos+strlen($findStr)) . $fillStr . substr($content, $pos+strlen($findStr));
                                        $i = $posMarker+strlen($fillStr)+1;
                                }   }
                            break;
                }   }   }
                $content = str_replace("[%{$marker}%]", $value, $content);
                continue;
            }
///////////////

            if(!isset($rowsPatterns[$marker])) continue;//Лишние данные

            $rowsText = array();
            $i = 0;
            foreach($value as $row) {//формирование блока строк
                $row['NPP'] = ++$i;
                $rowsText[] = $this->_translateRow($rowsPatterns[$marker], $row, $marker);
            }
            $content = str_replace("[%{$marker}%]", implode("\n", $rowsText), $content);
        }

        return preg_replace('/\[%.*?%\]/', '', $content);//зачищаем незаполненные плэйсхолдеры
    }

    //формирование строки
    private function _translateRow($content, $DATA, $rowName=false)
    {
        foreach($DATA as $marker=>$value) {
            $content = str_replace("[%row:{$marker}%]", $value, $content);
            $content = str_replace("[%row:{$rowName}:{$marker}%]", $value, $content);
        }
        return $content; 
    }

    //Поиск всех образцов строк в шаблоне и замена их на маркеры для дальнейшей замены на блоки строк
    private function _findAllRows($type, &$text) 
    {
        $rows = array();
        
        $xmlRowStartPatterns = $type==self::TYPE_WORD ? array('<w:tr ', '<w:tr>') : array('<row');
        $xmlRowEndPattern = $type==self::TYPE_WORD ? '</w:tr>' : '</row>';

        $curPosition = 0;
        while(true)
        {
            $firstColumnPosition = strpos($text, "[%row:", $curPosition);
            if(!$firstColumnPosition) return $rows;
            $lastColumnPosition = strpos($text, "%]", $firstColumnPosition);
            if(!$lastColumnPosition) return false;

            $columnInfo = substr($text, $firstColumnPosition, ($lastColumnPosition - $firstColumnPosition)+strlen("%]"));
            $columnInfo = explode(':', substr($columnInfo, 2, -2));

            if(count($columnInfo)<2) return false;//3, NPP не обязательно!

            $firstRowPosition = false;
            foreach ($xmlRowStartPatterns as $xmlRowStartPattern) {
                if(false === $firstRowPosition) {
                    $firstRowPosition = strrpos(substr($text, 0, $firstColumnPosition), $xmlRowStartPattern);
                }
            }

            if(!$firstRowPosition) return false;

            $lastRowPosition  = strpos($text, $xmlRowEndPattern, $firstRowPosition);
            if(!$lastRowPosition) return false;

            $a = substr($text, $firstRowPosition, ($lastRowPosition - $firstRowPosition)+strlen($xmlRowEndPattern));

            $rows[$columnInfo[1]] = $a;

            $curLen = strlen($text);
            $p1 = substr($text, 0, $firstRowPosition);
            $p2 = substr($text, $lastRowPosition+strlen($xmlRowEndPattern), $curLen-($lastRowPosition+strlen($xmlRowEndPattern)));

            $text = $p1."[%{$columnInfo[1]}%]".$p2;

            $curPosition = $lastRowPosition-$curLen+strlen($text);
        }

        return $rows;
    }

    //Удаление тэгового фарша из маркеров
    private function _removeTagsInMarkers($text)
    {
        $curPosition = 0;
        while(true)
        {
            $firstCharPosition = strpos($text, "[%", $curPosition);

            if(!$firstCharPosition) return $text;
            $lastCharPosition  = strpos($text, "%]", $firstCharPosition);

            $a = substr($text, $firstCharPosition, ($lastCharPosition - $firstCharPosition)+2);

            $curLen = strlen($text);
            $p1 = substr($text, 0, $firstCharPosition);
            $p2 = substr($text, $lastCharPosition+2, $curLen-($lastCharPosition+2));

            $text = $p1.str_replace(' ', '', strip_tags($a)).$p2;

            $curPosition = $lastCharPosition-$curLen+strlen($text);
        }
        return $text;
    }

    //Обрабатываем входные данные, в частности для ворда, транслируем переводы строки
    private function _translateInputData(&$data, $type)
    {
        foreach($data as $key=>&$value) {
            if(is_array($value)) {  
                foreach($value as $key=>&$v) {
                    $v = $this->__translateValue($v, $type);
                }
            } else {
                $value = $this->__translateValue($value, $type);
            }
        }
    }
    private function __translateValue($value, $type)
    {
        if($type==self::TYPE_WORD){
            if(strpos($value, '<w:')===false) {
                $value = str_replace("\n", '<w:br/>', $value);
            }
        }
        return $value;
    }

//EXCEL part /////////////////////////////////////////
    private function makeCompleteContent(&$zip, $content)//Восполнение текста из строковых ресурсов 
    {
        if (($index = $zip->locateName('xl/sharedStrings.xml')) === false) return false;
        $resourcesXML = $zip->getFromIndex($index);

        $xml = simplexml_load_string($resourcesXML);
        $resources = array();
        $i = 0;
        foreach ($xml->children() as $item) {
            $text = (string)$item->t;
            if(!$text) {//бывает вместо текста - фаршированный xml
                $piginText = array();
                foreach($item->r as $it) {
                    $piginText[] = $it->asXML();
                }
                $piginText = strip_tags(implode('', $piginText));
            }   
            $resources[$i] = $text ? $text : $piginText;
            $i++;
        }
        //Вставляем тект из ресурсов в контент
        $content = preg_replace_callback('|<c(.{1,20})t="s"(.*?)<v>(\d+)</v>(.*?)</c>|is', 
            function ($matches) use(&$resources) {
                return "<c{$matches[1]}t=\"s\"{$matches[2]}<v>{$resources[$matches[3]]}</v>{$matches[4]}</c>";
            },
            $content
        );

        return $content;
    }

    private function makeLinkedContent(&$zip, $content) //Вынос строковых ресурсов
    {
        $resources = array();
        //Выносим строки
        $content = preg_replace_callback('|(<c(.{1,20})t="s"(.*?))<v>(.*?)</v>|is', 
            function ($matches) use (&$resources){
                $prefix = $matches[1];
                $value = $matches[4];
                if($value=='') {
                    return "{$prefix}<v></v>";
                }
                $resources[] = $value;
                return "{$prefix}<v>".(count($resources)-1)."</v>";
            },
            $content
        );
        //Корректируем файл строковых ресурсов
        if (($index = $zip->locateName('xl/sharedStrings.xml')) === false) return false;
        $zip->deleteIndex($index);
        $count = count($resources);
        $restext = implode("</t></si><si><t>", $resources);
        $restext = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="{$count}" uniqueCount="{$count}"><si><t>{$restext}</t></si></sst>
EOD
;
        $zip->addFromString('xl/sharedStrings.xml', $restext);

        //Перенумерация строк и ячеек (по-порядку)
        $rowcount = 0;
        $fromTo = array();

        $content = preg_replace_callback('|(<row )r="(\d+)"(.*?)(</row>)|is', 
            function ($matches) use(&$rowcount, &$fromTo) {
                $rowcount++;
                $matches[3] = preg_replace('|r=\"([A-Z]+)'.$matches[2].'\"|is', 'r="${1}'.$rowcount.'"', $matches[3]);
                $fromTo[$matches[2]] = $rowcount;
                return "{$matches[1]}r=\"{$rowcount}\"{$matches[3]}{$matches[4]}";
            },
            $content
        );

        //Перенумерация объединений ячеек
        $content = preg_replace_callback('|(<mergeCell\s.*?)ref=\"([A-Z]+)(\d+):([A-Z]+)(\d+)\"(.*?/>)|is', 
            function ($matches) use($fromTo) {
                return "{$matches[1]}ref=\"{$matches[2]}{$fromTo[$matches[3]]}:{$matches[4]}{$fromTo[$matches[5]]}\"{$matches[6]}";
            },
            $content
        );

        return $content;
    }
//EXCEL part] /////////////////////////////////////////

//WORD part /////////////////////////////////////////
    //Добавление мультимедии
    private function _addFiles($content, $files)
    {       
        $strings = array();
        foreach($files as $resourceId=>$fileData) {
              $strings[] = <<<EOD
<Relationship Id="{$resourceId}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/{$fileData['filename']}"/>
EOD
;
        }
        $strings = implode("", $strings);

        return str_replace("</Relationships>", "{$strings}</Relationships>", $content);
    }

    private function updateWordFiles(&$zip, $content, $files) 
    {   if(!$files) return;

        $resourcePath = 'word/_rels/document.xml.rels';
        if (($index = $zip->locateName($resourcePath)) !== false) {
            $content = $zip->getFromIndex($index);
            $out_content = $this->_addFiles($content, $files);
            if($out_content===false) return false;                    

            $zip->deleteIndex($index);
            $zip->addFromString($resourcePath, $out_content);    
            foreach($files as $resourceId=>$fileData) {
                $zip->addFromString("word/media/{$fileData['filename']}", $fileData['data']);    
            }
        }
        $typesPath = '[Content_Types].xml';
        if (($index = $zip->locateName($typesPath)) !== false) {
            $out_content = $zip->getFromIndex($index);

            $contTypes = array('image/png'=>'png', 'image/jpeg'=>'jpg');

            foreach($contTypes as $type=>$ext) {
                if(strpos($out_content, $type)!==false) continue;
                $out_content = str_replace('</Types>', '<Default Extension="'.$ext.'" ContentType="'.$type.'"/></Types>', $out_content);
            }
            if($out_content===false) return false;                    
            $zip->deleteIndex($index);
            $zip->addFromString($typesPath, $out_content);    
        }
    }
}