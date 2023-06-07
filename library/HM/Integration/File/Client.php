<?php

class HM_Integration_File_Client implements HM_Integration_Interface_Client
{
    const DIR_PROCESSED = 'processed';

    protected $_source;
    protected $_dataIds = array();
    protected $requireInputParam = false;

    protected $_dir;
    protected $_files = array();
    protected $_filesProcessed = array();

    protected $_enableProcessed = true;

    protected $_method2FileMapping = array(
        'GetOrganizationalUnits' => 'Departments',
        'GetChangesOfOrganizationalUnits' => 'Departments',
        'GetTitleList' => 'Positions',
        'GetChangesOfTitleList' => 'Positions',
        'GetPhysicalPersons' => 'Persons',
        'GetChangesOfPhysicalPersons' => 'Persons',
        'GetPhysicalPersonsNames' => 'PersonsNames',
        'GetChangesOfPhysicalPersonsNames' => 'PersonsNames',
        'GetStaffUnitsPeriods' => 'StaffUnits',
        'GetChangesOfStaffUnitsPeriods' => 'StaffUnits',
        'GetEmployees' => 'Employees',
        'GetChangesOfEmployees' => 'Employees',
        'GetAbsence' => 'VECM_VacationSchedules',
        'GetChangesOfAbsence' => 'VECM_VacationSchedules',
        'GetAbsenceWatch' => 'VECM_WatchSchedules',
        'GetChangesOfAbsenceWatch' => 'VECM_WatchSchedules',
        'GetAbsenceWatchEmployees' => 'Employees',
        'GetChangesOfAbsenceWatchEmployees' => 'Employees',
    );

    public function __construct($source, $options = array())
    {
        $this->_source = $source;
        $config = Zend_Registry::get('config');

        $this->_dir = implode('/', array($config->integration->file->root, 'prod'/*$source['key']*/));
        if (!is_readable($this->_dir)) {
            throw new HM_Integration_Exception(sprintf('Директория недоступна: %s', $this->_dir));
        }
    }

    public function addSubDir($subdir)
    {
        $this->_dir = implode('/', array($this->_dir, $subdir));
        if (!is_readable($this->_dir)) {
            throw new HM_Integration_Exception(sprintf('Директория недоступна: %s', $this->_dir));
        }

        // если загружаем что-то левое, не надо отражать это в истории
        $this->_enableProcessed = false;
    }

    // для совместимости с
    public function setRequireInputParam($requireInputParam)
    {
        $this->requireInputParam = $requireInputParam;
        return $this;
    }

    public function call($method, $primaryKey = 'ID', $keySalt = false, $doNotSaveProcessed = false)
    {
        if (isset($this->_method2FileMapping[$method])) {
            $suffix = '_' . $this->_source['inn'];
            $filename = $this->_method2FileMapping[$method] . $suffix . '.xml';
            $path = implode('/', array($this->_dir, $filename));
            $pathProcessed = implode('/', array($this->_dir, self::DIR_PROCESSED, $filename));

            if (!$doNotSaveProcessed) {
                $this->_files[] = $path;
                $this->_filesProcessed[] = $pathProcessed;
            }

            if (is_readable($path)) {

                $data = $this->_getData($path, $method, $primaryKey, $keySalt);
                if (strpos($method, 'ChangesOf') !== false) {

                    if (is_readable($pathProcessed)) {
                        $dataPrev = $this->_getData($pathProcessed, $method, $primaryKey, $keySalt);
                        return is_array($keySalt) ? $this->_diff($data, $dataPrev, $keySalt) : $this->_diff($data, $dataPrev);
                    }
                } else {
                    return $data;
                }
            }
        }

        throw new HM_Integration_Exception(sprintf('Файл недоступен или неверного формата: %s', $method));
    }

    public function callExport($item)
    {
    }

    protected function _getData($path, $method, $primaryKey, $keySalt)
    {
        $xmlStr = file_get_contents($path);
        if ($xml = new SimpleXMLElement($xmlStr)) {

            // костыль для Absence
            $rootElement = str_replace('VECM_', '', $this->_method2FileMapping[$method]);

            if (count($items = $xml->xpath(sprintf('//%s', $rootElement)))) {
                $data = array();
                foreach ($items[0] as $item) {

                    $attributes = array();
                    foreach ($item->attributes() as $name => $value) {

                        $prefix = self::looksLikeId($name) ? sprintf('%s-', $this->_source['key']) : '';
                        $attributes[$name] = $prefix . trim((string)$value);
                    }

                    $salt = $attributes[$keySalt];

                    if (is_array($keySalt)) {
                        $keySaltArray = array();
                        foreach ($keySalt as $key) {
                            if (in_array($key, array('StartDate', 'EndDate'))) {
                                $keySaltArray[] = implode('-', array_reverse(explode('.', $attributes[$key])));
                            }
                        }
                        $salt = implode('-', $keySaltArray);
                    }

                    $key = $keySalt ? implode('-', array($attributes[$primaryKey], $salt)) : $attributes[$primaryKey];
                    $data[$key] = $attributes;
                }
                return $data;
            }
        }
    }

    protected function _diff($data, $reference, $keySalt = false)
    {
        $return = array();
        foreach ($data as $key => $item) {
            if ($keySalt && is_array($keySalt)) {
                $keys = array($item['ID']);
                foreach ($keySalt as $salt) {
                    $keys[] = implode('-', array_reverse(explode('.', $item[$salt])));
                }
                $key = implode('-', $keys);
            }
            if (!isset($reference[$key])) {
                // create
                $return[$key] = $item;
            } elseif (count(array_diff($item, $reference[$key]))) {
                // update
                $return[$key] = $item;
                unset($reference[$key]);
            } else {
                unset($reference[$key]);
            }
        }

        foreach ($reference as $key => $item) {
            // delete
            $return[$key] = $item;
            $return[$key]['isDeleted'] = 1;
        }

        return $return;
    }

    // надо обеспечить уникальность id среди всех источников
    protected function _inputFilter(&$value, $key, $prefix)
    {
        return strpos(strtolower($key), 'id') === 0 ? $value = $prefix . $value : $value;
    }

    public function answer($status)
    {
        if ($status == HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS) {

            if (count($this->_files) && $this->_enableProcessed) {
                foreach ($this->_files as $key => $path) {
                    $pathProcessed = $this->_filesProcessed[$key];
                    if (is_readable($path) && is_writable(implode('/', array($this->_dir, self::DIR_PROCESSED)))) {
                        copy($path, $pathProcessed);
                    } else {
                        throw new Exception('Задача выполнена успешно, но не удалось сохранить резервную копию файла интеграции.');
                    }
                }
            }
        }
    }

    static public function looksLikeId($name)
    {
        return (
            (
                strpos(strtolower($name), 'id') === 0) ||
                strpos($name, 'Id') ||
                strpos($name, 'ID')
            ) && (
                strpos(strtolower($name), 'idcategory') !== 0
            );
    }
}