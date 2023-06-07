<?php
class HM_Form_Psycho extends HM_Form {

    public function init()
    {
//        $sessionUserId = $this->getParam('session_user_id', 0);
//        $this->addElement('hidden', 'session_user_id', array('Value'=>$sessionUserId));

        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('psycho');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('module'=>'session', 'controller' => 'user', 'action' => 'list', 'session_user_id' => null))
            )
        );

        $fields = Zend_Registry::get('serviceContainer')->getService('QuestTypePsycho')->_getReportParts();

        $group = ['cancelUrl'];
        foreach($fields as $key=>$label) {
            $group[] = 'description_'.$key;
            $this->addElement($this->getDefaultWysiwygElementName(), 'description_'.$key, array(
                'Label' => $label,
//                'Description' => _('Комментарий отображается на странице со списком оценочных сессий пользователя'),
                'Required' => false,
                'class' => 'wide',
                )
            );
        }
    
        $this->addDisplayGroup($group,
            'general',
            array('legend' => _('Тексты к компетенциям'))
        );
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}