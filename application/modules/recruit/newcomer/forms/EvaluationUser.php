<?php
class HM_Form_EvaluationUser extends HM_Form {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('evaluation_user');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index', 'newcomer_id' => null))
            )
        );

        $this->addElement('hidden',
            'newcomer_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );
        $this->addElement('hidden',
            'today',
            array(
                'Required' => true,
                'value' => date('Y-m-d')
            )
        );

        $this->addElement($this->getDefaultTagsElementName(), 'evaluation_user_id', array(
            'required' => true,
            'Label' => _('Куратор'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из имени или должности'),
            'json_url' => '/user/ajax/users-list-for-newcomer-evaluation',
            'newel' => true,
            'maxitems' => 1
        ));

//        $this->addElement(
//            new HM_Form_Element_FcbkComplete('evaluation_user_id', array(
//                'required' => true,
//                'Label' => _('Куратор'),
//                'Description' => _('Для поиска можно вводить любое сочетание букв из имени или должности'),
//                'json_url' => '/user/ajax/users-list-for-newcomer-evaluation',
//                'newel' => true,
//                'maxitems' => 1
//            )
//        ));


        $this->addDisplayGroup(array(
            'cancelUrl',
            'newcomer_id',
            'evaluation_user_id',
            'evaluation_date',
        ),
            'evaluation',
            array('legend' => _('Общие свойства'))
        );


        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}