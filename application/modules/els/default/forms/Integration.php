<?php
class HM_Form_Integration extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('integration');

        $types = array(
            'import' => _('Начальная загрузка из 1С ЗУП'),
            'update' => _('Загрузка изменений из 1С ЗУП'),
            'sync' => _('Принудительная загрузка изменений из 1С ЗУП'),
        );

        $this->addElement('radio', 'type', array(
            'Label' => _('Тип интеграции'),
            'Validators' => array(
            ),
            'Value' => 'update',
            'MultiOptions' => $types,
            'separator' => '<br>'
        ));

        $this->addElement('select', 'source', array(
            'Label' => _('Выбор ПСК'),
            'multiOptions' => HM_Integration_Abstract_Model::getLdapNamesForSelect(),
        ));

        $tasks = HM_Integration_Manager::getTasks();
        $checkboxNames = array();
        foreach ($tasks as $task => $label) {
            $checkboxName = $task . '_task';
            $checkboxNames[] = $checkboxName;
            $this->addElement('checkbox', $checkboxName, array(
                'Label' => $label,
                'checked' => 'checked',
            ));
        }

        $this->addElement('checkbox', 'ad', array(
            'Label' => _('Интеграция с AD'),
            'Description' => _('Если опция выбрана, то после интеграции с 1С на том же выбранном ПСК запускается интеграция с AD.')
        ));

        $this->addDisplayGroup(
            array(
                'type',
                'source',
                'ad',
            ),
            'actionSelector',
            array('legend' => _('Запуск различных вариантов интеграции'))
        );

        $this->addDisplayGroup(
            $checkboxNames,
            'tasksGroup',
            array('legend' => _('Выбор потоков интеграции'))
        );

        $this->addElement('Submit', 'submit', array(
            'Label' => _('Пуск')
        ));

        parent::init();
    }
}