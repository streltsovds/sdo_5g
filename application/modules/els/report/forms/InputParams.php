<?php

class HM_Form_InputParams extends HM_Form
{
    protected $_fields = [];
    protected $_reportId = 0;

    public function __construct($fields, $reportId)
    {
        $this->_reportId = $reportId;

        if (is_array($fields) && count($fields)) {
            $this->_fields = $fields;
        }

        parent::__construct();
    }

    public function init()
    {
        $this->setAction($this->getView()->url(array('action' => 'index', 'controller' => 'index', 'module' => 'report', 'report_id' => $this->_reportId)));

        $values = array();
        if (isset(Zend_Registry::get('session_namespace_default')->report['values'][$this->_reportId])) {
            $values = Zend_Registry::get('session_namespace_default')->report['values'][$this->_reportId];
        }

        $fieldNames = array();
        foreach ($this->_fields as $field) {
            if ($field['type'] == 'select') {
                $this->addElement(
                    $this->getDefaultSelectElementName(),
                    $field['name'],
                    array(
                        'label' => $field['title'],
                        'required' => true,
                        'multiOptions' => $field['values'],
                        'Filters' => array(
                            'StripTags'
                        )
                    )
                );
            } elseif (in_array($field['type'], array('date', 'datetime', 'datetimestamp'))) {

                $this->addElement(
                    $this->getDefaultDatePickerElementName(),
                    $field['name'] . '_from',
                    array(
                        'label' => $field['title'] . _(' (C)'),
                        'required' => true,
                        'Filters' => array(
                            'StripTags'
                        ),
                        'JQueryParams' => array(
                            'showOn' => 'button',
                            'buttonImage' => "/images/icons/calendar.png",
                            'buttonImageOnly' => 'true'
                        )
                    )
                );

                $this->addElement(
                    $this->getDefaultDatePickerElementName(),
                    $field['name'] . '_to',
                    array(
                        'label' => $field['title'] . _(' (По)'),
                        'required' => true,
                        'Filters' => array(
                            'StripTags'
                        ),
                        'JQueryParams' => array(
                            'showOn' => 'button',
                            'buttonImage' => "/images/icons/calendar.png",
                            'buttonImageOnly' => 'true'
                        )
                    )
                );
            } else {
                $this->addElement(
                    $this->getDefaultTextElementName(),
                    $field['name'],
                    array(
                        'label' => $field['title'],
                        'required' => true,
                        'Filters' => array(
                            'StripTags'
                        )
                    )
                );
            }

            if (isset($values[$field['name']])) {
                $this->getElement($field['name'])->setValue($values[$field['name']]);
            } elseif (in_array($field['type'], array('date', 'datetime', 'datetimestamp'))) {

                $dateFieldPostfixes = array('from', 'to');

                foreach ($dateFieldPostfixes as $dateFieldPostfix) {
                    if (isset($values[$field['name'] . "_$dateFieldPostfix"])) {
                        $this->getElement($field['name'] . "_$dateFieldPostfix")->setValue($values[$field['name'] . "_$dateFieldPostfix"]);
                    } elseif (isset($field['filter'][$dateFieldPostfix])) {
                        $this->getElement($field['name'] . "_$dateFieldPostfix")->setValue($field['filter'][$dateFieldPostfix]);
                    }
                }
            }

            if (in_array($field['type'], array('date', 'datetime', 'datetimestamp'))) {
                $fieldNames[] = $field['name'] . '_from';
                $fieldNames[] = $field['name'] . '_to';
            } else {
                $fieldNames[] = $field['name'];
            }
        }

        $this->addDisplayGroup(
            $fieldNames,
            'inputGroup',
            array('legend' => _('Входные параметры'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Далее')));

        parent::init(); // required!
    }
}
