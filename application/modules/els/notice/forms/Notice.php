<?php
class HM_Form_Notice extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('notice');
        $noticeId = (int) $this->getParam('id', 0);
        $notice = Zend_Registry::get('serviceContainer')->getService('Notice')->getOne(Zend_Registry::get('serviceContainer')->getService('Notice')->find($noticeId));
        //$this->setAction($this->getView()->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'index'),NULL,TRUE)
        ));

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'event', array(
            'Label' => _('Событие'),
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'disabled' => 'disabled'
        ));
                
        $this->addElement($this->getDefaultTextElementName(), 'receiver', array(
            'Label' => _('Адресат'),
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'disabled' => 'disabled'
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Тема сообщения'),
            //'Description' => $notice ? (HM_Notice_NoticeModel::getDescription($notice->type) ? strip_tags(HM_Notice_NoticeModel::getDescription($notice->type)) : null) : null,
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Текст сообщения'),
            'Description' => $notice ? (HM_Notice_NoticeModel::getDescription($notice->type) ? : null) : null,
            'Required' => true,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'enabled', array(
            'Label' => _('Активно'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,1)
            )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'priority', array(
            'Label' => _('Немедленная отправка'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,1)
            )
        ));

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'id',
                'cancelUrl',
                'event',
            	'receiver',
                'title',
                'message',
                'enabled',
                'priority',
                'submit'
            ),
            'noticeGroup',
            array('legend' => _('Шаблон системного сообщения'))
        );

        parent::init(); // required!
	}
}