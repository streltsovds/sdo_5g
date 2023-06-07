<?php
class HM_Form_Functions extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('functions');
        
        if ($standardId = $this->getParam('standard_id', 0)) {
            $standard = Zend_Registry::get('serviceContainer')->getService('AtStandard')->find($standardId)->current();
        } 

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('module'=>'standard', 'controller' => 'functions', 'action' => 'index', 'function_id'=>null))
            )
        );
        
        $this->addElement('hidden',
            'function_id',
            array(
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );   
        $this->addElement('hidden',
            'standard_id',
            array(
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );   

       $this->addElement($this->getDefaultTextElementName(), 'name', array(
        	'Style' => 'width:550px',
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
        ));


        $this->addDisplayGroup(array(
            'name'
        ),
            'standards',
            array('legend' => _('Общие свойства'))
        );

        foreach (HM_At_Standard_Function_FunctionModel::getTypes() as $typeId => $title) {
            
            $this->addElement($this->getDefaultMultiSetElementName(), 'requirements_' . $typeId, array(
                'Required' => false,
                'dependences' => array(
                    new HM_Form_Element_Vue_Text(
                        'requirement',
                        array(
                        	'Label' => _(''),//№ п/п
//                        	'class' => 'wide multiset-trigger',
                        )
                    ), 
                )                
            ));     

            $this->addDisplayGroup(array(
                'cancelUrl',
                'requirements_' . $typeId,
            ),
                'group_' . $typeId,
                array('legend' => $title)
            );             
        }



        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

 }
