<?php

class HM_Form_Theme extends HM_Form
{
    public function init()
    {
        // Название темы
        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'label' => _('Название темы') . ":",
            'required' => true,
            'autocomplete' => 'off',
            'validators' => array(array('StringLength', 65535, 1)),
            'Filters' => array('HtmlSanitizeRich'),
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'class' => 'topic-input')),
                'Label',
            )
        ));

        // Текст темы
        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'label' => _('Текст') . ':',
            'required' => false,
            'validators' => array(array('StringLength', 65535, 0)),
            'filters' => array('HtmlSanitizeRich'),
            'toolbar' => 'hmToolbarTiny',

        ));

        $this->addDisplayGroup(
            array(
                'title',
                'text',
            ),
            'themeGroup',
            array('legend' => _('Тема'))
        );

        // Submit
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'label' => _('Сохранить'),
            'description' => _('Отмена'),
            'class' => 'ui-widget ui-button topic-create',
            'decorators' => array(
                array('Description', array('tag' => 'span', 'class' => '')),
                array(array('cancel' => 'HtmlTag'), array('tag' => 'a', 'class' => 'ui-widget ui-button topic-create-cancel', 'href' => '#')),
                'ViewHelper',
                array(array('wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'topic-createeditor-buttons')),
            )
        ));
    }

}