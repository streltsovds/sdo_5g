<?php
class HM_Form_Category extends HM_Form_SubForm {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('category');
        
        if ($questId = $this->getParam('quest_id', 0)) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
        }        
        
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index'))
            )
        );

        $this->addElement('hidden',
            'quest_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('hidden',
            'category_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            //'Description' => _(''),
            'Required' => true,
            'Filters' => array('StripTags'),
        )
        );



        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Текст'),
            'Required' => false,
            )
        );

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'description',
        ),
            'category_group',
            array('legend' => _('Общие свойства'))
        );
        
        /******************** Ключ *********************/

        $this->addElement($this->getDefaultMultiSetElementName(), 'formula', [
            'Required' => false,
            'dependences' => [
                new HM_Form_Element_Vue_Text(
                    'from',
                    [
                        'Label' => _('От'),
                    ]
                ),
                new HM_Form_Element_Vue_Text(
                    'to',
                    [
                        'Label' => _('До'),
                    ]
                ),
                new HM_Form_Element_Vue_Text(
                    'description',
                    [
                        'Label' => _('Результат'),
                    ]
                ),
            ],
            'dependences-classes' => ['flex xs1', 'flex xs1', 'flex xs10']
        ]);

        $this->addDisplayGroup(array(
            'formula',
        ),
            'key',
            array('legend' => _('Интерпретация результатов'))
        );        

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
    
    public function getElementDecorators($alias, $first = 'ViewHelper'){
        if (in_array($alias, array('cluster_id'))) {
            return array (
                array($first),
                array('RedErrors'),
                array('AddOption'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt')),
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }    
    
}