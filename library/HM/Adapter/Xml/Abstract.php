<?php

abstract class HM_Adapter_Xml_Abstract implements HM_Adapter_Interface
{
    private $_filename = null;

    protected $_rootElementName = 'Objs';
    protected $_delimiter = ';';
    protected $_enclosure = '"';
    protected $_skipLines = 0;

    // имя аттрибута элемента, в котором хранится название параметра
    protected static $_fieldTitleAttribute = '';
    // в случае если элементы имеют одинаковое имя, иначе бежит через чилдрены
    protected static $_paramXmlElementName = '';

    /* пример на основе хмл от алматв
    protected static $_fieldTitleAttribute = 'N';
    protected static $_paramXmlElementName = 'S';
    */
    public function __construct($filename = null, $options = null)
    {
        $this->_filename = $filename;
        $this->setOptions($options);
    }

    public function setOptions($options = null)
    {
        if (null !== $options) {
            if (isset($options['rootElementName'])) {
                $this->_rootElementName = $options['rootElementName'];
            }
            if (isset($options['delimiter'])) {
                $this->_delimiter = $options['delimiter'];
            }
            if (isset($options['enclosure'])) {
                $this->_enclosure = $options['enclosure'];
            }
            if (isset($options['skipLines'])) {
                $this->_skipLines = $options['skipLines'];
            }
        }
    }

    public function setFileName($filename)
    {
        $this->_filename = $filename;
    }

    public function needToUploadFile()
    {
        return true;
    }

    private function _checkFile()
    {
        if (null === $this->_filename) {
            throw new HM_Exception(_('Не указан файл с данными'));
        }
        if (!file_exists($this->_filename)) {
            throw new HM_Exception(sprintf(_("Файл с данными '%s' не найден"), basename($this->_filename)));
        }
        if (!is_readable($this->_filename)) {
            throw new HM_Exception(sprintf(_("Файл с данными '%s' недоступен для чтения"), basename($this->_filename)));
        }

        return true;
    }

    public function getMappingArray()
    {
        return array(
            'mid_external' => 'mid_external',
            'LastName' => 'LastName',
            'FirstName' => 'FirstName',
            'Patronymic' => 'Patronymic',
            'Login' => 'Login',
            'EMail' => 'EMail',
            'Password' => 'Password',
            'isTeacher' => 'isTeacher',
            'group' => 'group',
            'tags' => 'tags'
        );
    }

    public function getFormatData($name, $resUserFunc='')
    {
        $map = array();
        switch($name['format']){
            case 'array':
                $map = $resUserFunc;
                break;
            case 'integer':
                $resUserFunc = (int)$resUserFunc;
                break;
            default:
                break;
        }

        if(is_array($resUserFunc) && count($resUserFunc)) {
            return $map;
        }else{
            if(isset($name['field']))
                $map[$name['field']] = $resUserFunc;
            else
                $map[$name[0]] = $resUserFunc;
        }
        return $map;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $result = array();
        if ($this->_checkFile()) {
            $counter = 0;
            Zend_Registry::get('serviceContainer')->getService('Unmanaged')->detectFileEncoding($this->_filename);
            $xmlReader = new XMLReader();
            if ($xmlReader->open($this->_filename)) {
                $mapping = $this->getMappingArray();
                //pr($xmlDoc->saveHTML());die;
                if (is_array($mapping) && count($mapping)) {
                    $xmlReader->read();
                    $rootElement = new SimpleXMLElement($xmlReader->readOuterXML());
                    foreach($rootElement->children() as $user) {
                        $map = array();
                        foreach($this->getParamsArray($user) as $userValue) {
                            $valueName = $this->getValueName($userValue);
                            if($mapping[$valueName]) {
                                $mappingValue = $mapping[$valueName];
                                if (is_array($mappingValue)&&count($mappingValue)) {
                                    if (isset($mappingValue['callback'])&&!empty($mappingValue['callback'])) {
                                        $resUserFunc = call_user_func(array(
                                            $this, $mappingValue['callback']), trim($userValue)
                                        );
                                    } else {
                                        $resUserFunc = trim($userValue);
                                    }
                                    $resUserFunc = $this->getFormatData($mappingValue, $resUserFunc);
                                    $map = $resUserFunc ? array_merge((array)$map,(array)$resUserFunc) : $map;
                                } else {
                                    $map[$mappingValue] = trim($userValue);
                                }

                            }
                        }
                        if(count($map)) {
                            $result[] = $map;
                        }
                    }
				}

                $xmlReader->close();
            }
        }
        return $result;
    }

    /**  экспорт хмл из разных источников отличается, так что вынесено в отдельный метод дабы переопределить
     *  можно было.
     *  byDefault :
     *  1. берется название элемента
     *  2. если переопределить self::$_fieldTitleAttribute, тогда будет браться название из аттрибута
     *      с именем self::$_fieldTitleAttribute
     * @param SimpleXmlElement $userValue
     * @return String
     */
    public function getValueName($userValue)
    {
        if (strlen(self::$_fieldTitleAttribute)) {
            $attributes = $userValue->attributes();
            return (string)$attributes[self::$_fieldTitleAttribute];
        }
        return $userValue->getName();
    }


    public function getParamsArray($user)
    {
        if (strlen(self::$_paramXmlElementName)) {
            return $user->{self::$_paramXmlElementName};
        }
        return $user->children();
    }

}