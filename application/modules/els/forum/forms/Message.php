<?php

class HM_Form_Message extends HM_Form
{

    public function init()
    {
        // Текст сообщения
        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'label' => '',
            'required' => true,
            'validators' => array(array('StringLength', 65535, 1)),
            'filters' => array('HtmlSanitizeRich'),
            'height' => 130
        ));

        $this->addElement(
            $this->getDefaultCheckboxElementName(),
            'is_hidden',
            array(
                'Label' => _('Режим скрытого ответа'),
            )
        );

        // Submit
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'label' => _('Сохранить'),
            'class' => 'ui-widget ui-button topic-reply',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'class' => 'topic-replyeditor-buttons')),
            )
        ));
    }
}