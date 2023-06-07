<?php
class HM_Quest_Question_Import_Excel_ExcelAdapter implements HM_Adapter_Interface
{
    private $_filename = null;
    private $_tempDir  = null;
    private $_questions = null;

    public function __construct($filename = null)
    {
        $this->_filename = $filename;
        $this->_tempDir  = Zend_Registry::get('config')->path->upload->tmp.'excel'.DIRECTORY_SEPARATOR;
    }

    public function setFileName($filename)
    {
        $this->_filename = $filename;
    }

    public function needToUploadFile()
    {
        return true;
    }

    public function isTest()
    {
        $isTest = false;

        if ((null !== $this->_filename) && file_exists($this->_filename) && is_readable($this->_filename)) {

            foreach ($this->getExcelFiles() as $excelFile) {

                if (false !== strpos($excelFile, '.xlsx')) {

                    $excelRows = $this->readFile($excelFile);

                    // Непустые значения первого столбца
                    $arr = array_filter(array_column($excelRows, 0));
                    // Все ли они числовые
                    $arr = array_unique(array_map('is_numeric', $arr));

                    if (count($arr) == 1 && $arr[0] === true) {
                        $isTest = true;
                    }
                }
            }
        }

        return $isTest;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $output    = array();

        if ((null !== $this->_filename) && file_exists($this->_filename) && is_readable($this->_filename)) {

            foreach ($this->getExcelFiles() as $excelFile) {

                if (false !== strpos($excelFile, '.xlsx')) {

                    $excelRows = $this->readFile($excelFile);

                    $questions      =
                    $answers        =
                    $justifications = array();

                    $number = 0;
                    $type = HM_Quest_Question_QuestionModel::TYPE_SINGLE;
                    // Проходим по каждому ряду
                    foreach ($excelRows as $row) {
                        // Обрабатываем только ряды, содержащие данные
                        if (count($row)) {
                            if ($row[0]) {
                                $number = $row[0];
                                $questions[$number] = array(
                                    'question' => $row[1],
                                    'type' => $type,
                                    'answers' => array()
                                );
                            }

                            if ($row[4]) $justifications[$number] = $row[4];

                            $answers[$number][] = array(
                                'is_correct' => ($row[3]) ? true : false,
                                'variant' => trim($row[2])
                            );
                        }
                    }
                    foreach ($questions as $key => $question) {
                        if (count($answers[$key]) == 1) {
                            $question['type'] = HM_Quest_Question_QuestionModel::TYPE_TEXT;
                        } else {
                            $correctCount = 0;
                            foreach ($answers[$key] as $answer) {
                                if ($answer['is_correct']) {
                                    $correctCount ++;
                                }
                            }
                            if ($correctCount > 1) {
                                $question['type'] = HM_Quest_Question_QuestionModel::TYPE_MULTIPLE;
                            }
                        }

                        $question['answers']       = $answers[$key];
                        $question['justification'] = $justifications[$key];
                        $question['shuffle_variants'] = 1;
                        $output[] = $question;
                    }
                }
            }
            $this->_questions = $output;

            @unlink($this->_filename);
        }

        return $output ?: $this->_questions;
    }

    private function _unzipAllExcelFiles()
    {
        return $this->_unzipFile($this->_filename, $this->_tempDir);
    }

    private function _unzipFile($zipFile, $destinationDir)
    {
        $excelFiles = array();

        $zip = new ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($destinationDir);
            $zip->close();
        } else {
            throw new HM_Exception(_('Невозможно прочитать файл ' . $zipFile));
        }

        if ($handle = opendir($destinationDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $excelFiles[] = $entry;
                }
            }
            closedir($handle);
        }

        return $excelFiles;
    }

    /**
     * @return array
     */
    protected function getExcelFiles()
    {
        if (false !== strpos($this->_filename, '.zip')) {
            $files = glob($this->_tempDir . '*');
            foreach ($files as $file) {
                if (!is_dir($file)) unlink($file);
            }
            $excelFiles = $this->_unzipAllExcelFiles();
        } else {
            $excelFiles = array($this->_filename);
        }

        return $excelFiles;
    }

    /**
     * @param $excelFile
     * @return array
     * @throws HM_Exception
     */
    protected function readFile($excelFile)
    {
        $this->_unzipFile($excelFile, $this->_tempDir . 'xml');

        $worksheets = $this->_tempDir . 'xml' . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'worksheets';
        $sharedStrings = $this->_tempDir . 'xml' . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'sharedStrings.xml';

        $xml = simplexml_load_file($sharedStrings);
        $sharedStringsArr = array();
        foreach ($xml->children() as $item) {
            $sharedStringsArr[] = (string)$item->t;
        }

        $handle = @opendir($worksheets);
        $out = array();
        while ($file = @readdir($handle)) {
            //проходим по всем файлам из директории /xl/worksheets/
            if ($file != "." && $file != ".." && $file != '_rels') {
                $xml = simplexml_load_file($worksheets . DIRECTORY_SEPARATOR . $file);
                //по каждой строке
                $row = 0;
                foreach ($xml->sheetData->row as $item) {
                    $out[$file][$row] = array();
                    //по каждой ячейке строки
                    $cell = 0;
                    foreach ($item as $child) {
                        $attr = $child->attributes();
                        $value = isset($child->v) ? (string)$child->v : false;
                        $out[$file][$row][$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                        $cell++;
                    }
                    $row++;
                }
            }
        }

        // Если шаблон содержит несколько листов, то берется только первый лист.
        foreach ($out['sheet1.xml'] as $excelRow) {
            $excelRows[] = $excelRow;
        }

        // Удаляем ряд с заголовками
        array_shift($excelRows);

        return $excelRows;
    }
}