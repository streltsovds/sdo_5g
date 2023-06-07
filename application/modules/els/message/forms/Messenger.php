<?php
class HM_Form_Messenger extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('messengerform');
        $this->setAction(
            $this->getView()->url(array(
                'module' => 'message',
                'controller' => 'send',
                'action' => 'instant-send',
            ))
        );

        $this->addElement($this->getDefaultTagsElementName(), 'users', array(
            'required' => true,
            'Label' => _('Кому'),
            'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
            'newel' => false
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('users', array(
//                'required' => true,
//                'Label' => _('Кому'),
//                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
//                'newel' => false
//            )
//        ));

        

        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Сообщение'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Отправить')));
		$this->addElement('button', 'update', array('Label' => _('Проверить сообщения')));


        parent::init(); // required!

        $this->getElement('submit')->setDecorators(array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'dd', 'style' => 'float: left;')),
        ));
	}

}