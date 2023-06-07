<?php

class HM_Recruit_Reservist_Import_Template_TemplateAdapter implements HM_Adapter_Interface
{
    private $_filename = null;
    private $_tempDir  = null;

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

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $output = array();

        $xlKeys = array(
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
            "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
            "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI"
        );

        if ((null !== $this->_filename) && file_exists($this->_filename) && is_readable($this->_filename)) {

            if (false !== strpos($this->_filename, '.zip')) {
                $files = glob($this->_tempDir . '*');
                foreach($files as $file){
                    if (!is_dir($file)) unlink($file);
                }
                $excelFiles = $this->_unzipAllExcelFiles();
            } else {
                $pathParts = explode(DIRECTORY_SEPARATOR, $this->_filename);
                $excelFiles = array('..'.DIRECTORY_SEPARATOR.end($pathParts));
            }

            foreach ($excelFiles as $excelFile) {

                if (false !== strpos($excelFile, '.xlsx')) {

                    $this->_unzipFile($this->_tempDir . $excelFile, $this->_tempDir . 'xml');

                    $worksheets    = $this->_tempDir . 'xml' . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'worksheets';
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
                                $itemAttr = $item->attributes();
                                foreach ($xlKeys as $key => $xlKey) {
                                    $flag = false;
                                    foreach ($item as $child) {
                                        $attr = $child->attributes();
                                        $a = $attr['r'];
                                        $b = $xlKey.$itemAttr['r'];
                                        if ($attr['r'] == $xlKey.$itemAttr['r']) {
                                            $value = isset($child->v)? (string)$child->v:'';
                                            $out[$file][$row][$key] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                                            $flag = true;
                                        }
                                    }
                                    if (!$flag) $out[$file][$row][$key] = '';
                                }
                                $row++;
                            }
                        }
                    }

                    $rawExcelRows =
                    $rows         = array();

                    // Цитата из условия задачи #26811:
                    // Шаблон может содержать только 1 лист.
                    // Если он содержит несколько листов, то берется только первый лист,
                    // прочие листы должны игнорироваться.
                    foreach ($out['sheet1.xml'] as $excelRow) {
                        $rawExcelRows[] = $excelRow;
                    }

                    foreach ($rawExcelRows as $key => $row) {
                        if (count($row) && $key > 2) {
                            $newRow = array();
                            foreach ($row as $k => $r) {
                                if (in_array($k, range(1, 34))) {
                                    $newRow[] = $r;
                                }
                            }
                            $output[] = $newRow;
                        }
                    }
                }
            }
            @unlink($this->_filename);
        }
        return $output;
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
}