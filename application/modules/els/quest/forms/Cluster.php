<?php
class HM_Form_Cluster extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('cluster');

        $questId   = $this->getParam('quest_id', 0);
        $subjectId = $this->getParam('subject_id', 0);
        
        $url = array(
            'module'     => 'quest',
            'controller' => 'cluster',
            'action'     => 'list',
            'quest_id'   => $questId
        );
        
        if($subjectId){
            $url['subject_id'] = $subjectId;
        }
        
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url($url, null, true)
            )
        );

        $this->addElement('hidden',
            'cluster_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'order', array(
            'Label' => _('Порядок'),
            'class' => 'brief',
            'Value' => 0,
            'Required' => false,
            'Description' => _('Порядковый номер блока вопросов в тесте'),
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'order',
        ),
            'cluster',
            array('legend' => _('Блок вопросов'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}