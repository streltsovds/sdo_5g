<?php

class HM_Form_Domain extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('domain');

        $this->addElement('hidden', 'cancelUrl', [
            'Required' => false,
            'Value' => $this->getView()->url(['module' => 'report', 'controller' => 'list', 'action' => 'index'], null, true)
        ]);

        $config = new HM_Report_Config();

        $this->addElement($this->getDefaultSelectElementName(), 'domain', [
                'label' => _('Область отчёта'),
                'required' => true,
                'multiOptions' => $config->getDomains()
            ]
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', [
                'label' => _('Название шаблона отчёта'),
                'required' => true,
                'Filters' => ['HtmlSanitizeRich']
            ]
        );

        $roles = HM_Report_ReportModel::getReportRoles();
        $this->addElement($this->getDefaultMultiCheckboxElementName(), 'roles', [
            'Label' => _('Доступен для ролей'),
            'Required' => false,
            'Validators' => [
            ],
            'Filters' => [
            ],
            'MultiOptions' => $roles
        ]);

        $this->addDisplayGroup(
            [
                'cancelUrl',
                'domain',
                'name',
                //'status',
                'roles',
                'submit'
            ],
            'resourceGroup',
            ['legend' => _('Отчётная форма')]
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', ['Label' => _('Сохранить')]);

        parent::init(); // required!
    }

}