<?php

class HM_Grid_Column_DateTime extends HM_Grid_SimpleColumn
{
    const TYPE = 'date-time';

    protected $_format = null;

    protected static function _getDefaultOptions()
    {
        return array(
            'title'      => _('Дата'),
            'format'     => '',   // формат даты-времени. Если не указан, то формат берётся из локали
            'showTime'   => true, // если не установлен формат даты-времени, то помечает, показывать или нет время
            'emptyValue' => '',   // значение, если пришёл из базы null, etc
        );
    }

    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_initFormat();

    }

    protected function _initFormat()
    {
        $format = $this->getOption('format');

        if (!$format) {

            $locale = Zend_Locale::findLocale();

            $format = Zend_Locale_Format::getDateFormat($locale);

            if ($this->getOption('showTime')) {
                $format .= ' ' . Zend_Locale_Format::getTimeFormat($locale);
            }
        }

        $this->_format = $format;
    }

    public function getCallBack()
    {
        return array(
            'function' => array($this, 'updateColumn'),
            'params' => array(
                '{{'.$this->getFieldName().'}}',
            ),
        );
    }

    public function updateColumn($value)
    {
        if (!$value) {
            return $this->getOption('emptyValue');
        }

        $date = new HM_Date($value);

        return $date->toString($this->_format);

    }

    public function getFilter()
    {
        return array(
            'render' => 'DateSmart',
        );
    }

}