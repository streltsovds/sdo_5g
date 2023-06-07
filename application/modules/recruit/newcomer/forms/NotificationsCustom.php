<?php
class HM_Form_notificationsCustom extends HM_Form {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('send-notifications');

        $this->addElement($this->getDefaultTextElementName(), 'title_users', array(
            'Label' => _('Тема сообщения'),
            'Required' => (Zend_Controller_Front::getInstance()->getRequest()->getActionName() != 'send-notifications-worker'),
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'message_users', array(
            'Label' => _('Текст сообщения'),
            'Required' => (Zend_Controller_Front::getInstance()->getRequest()->getActionName() != 'send-notifications-worker'),
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title_managers', array(
            'Label' => _('Тема сообщения'),
            'Required' => (Zend_Controller_Front::getInstance()->getRequest()->getActionName() != 'send-notifications-worker'),
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'message_managers', array(
            'Label' => _('Текст сообщения'),
            'Required' => (Zend_Controller_Front::getInstance()->getRequest()->getActionName() != 'send-notifications-worker'),
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addDisplayGroup(
            array(
                'title_users',
                'message_users',
            ),
            'noticeGroupusers',
            array('legend' => _('Сообщение для пользователей'))
        );

        $this->addDisplayGroup(
            array(
                'title_managers',
                'message_managers'
            ),
            'noticeGroupManagers',
            array('legend' => _('Сообщение для руководителей'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Отправить')));
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
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