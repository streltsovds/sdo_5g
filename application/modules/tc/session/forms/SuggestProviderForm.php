<?php

class HM_Form_SuggestProviderForm extends HM_Form_SimpleForm
{
    protected function _initElements()
    {
        $this->_initProviderGroup();
        $this->_initSubjectGroup();
    }

    protected function _initSubjectGroup()
    {
        $this
            ->simpleTextElement('subject_name', array(
                'Label'    => _('Название курса'),
                'Required' => true,
            ))

            ->simpleAutoCompleteElement('city', array(
                'Label'    => _('Город (курса)'),
                'DataUrl'  => array(
                    'baseUrl'    => 'tc',
                    'module'     => 'provider',
                    'controller' => 'ajax',
                    'action'     => 'city',
                ),
                'MaxItems' => 1,
                'Required' => true,
            ))

            ->simpleTextElement('subject_cost', array(
                'Label'       => _('Стоимость (без НДС)'),
                'Required'    => true,
            ))

            ->simpleGroup(_('Учебный курс'));

    }

    protected function _initProviderGroup()
    {
        $this
            ->simpleAutoCompleteElement('provider', array(
                'Label'    => _('Название провайдера'),
                'DataUrl'  => array(
                    'baseUrl'    => 'tc',
                    'module'     => 'provider',
                    'controller' => 'ajax',
                    'action'     => 'provider',
                ),
                'MaxItems' => 1,
                'Required' => true,
                'AllowNewItems' => true
            ))

            ->simpleTextAreaElement('provider_contacts', array(
                'Label' => _('Контактная информация'),
                'Description' => _('Только для новых провайдеров')
            ))

            ->simpleGroup(_('Провайдер'));

    }
}