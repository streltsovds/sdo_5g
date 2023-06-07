<?php
class HM_Form_notifications extends HM_Form {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('send-notifications');

        $this->addElement($this->getDefaultTagsElementName(), 'initiators', array(
            'required' => false,
            'Label' => _('Добавить получателей'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
            'json_url' => '/user/ajax/users-list',
            'newel' => false,
//                'maxitems' => 1
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('initiators', array(
//                'required' => false,
//                'Label' => _('Добавить получателей'),
//                'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
//                'json_url' => '/user/ajax/users-list',
//                'newel' => false,
////                'maxitems' => 1
//            )
//        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Тема сообщения'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Текст сообщения'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'report_link', array(
            'Label' => _('Прикрепить ссылку на отчёт')
        ));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Отправить')));
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'initiators',
                'title',
                'message',
                'report_link',
                'submit'
            ),
            'noticeGroup',
            array('legend' => _('Уведомление о назначении сессии'))
        );

        parent::init(); // required!
    }

    public function initWithData($postMassIds)
    {
        $this->addElement('hidden',
            'postMassIds_grid',
            array(
                'value' => $postMassIds
            )
        );

        $this->addElement('hidden',
            'sendnotifications',
            array(
                'value' => 1
            )
        );

        parent::init(); // еще разочек

    }
}