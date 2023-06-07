<?php
class HM_Form_GroupPage extends HM_Form{
	
	public function init(){
		
		$this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('group-page');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(
                array(
                    'module' => 'htmlpage',
                    'controller' => 'list',
                    'action' => 'index'
                )
            )
        ));

        $this->addElement('hidden', 'role',
            [
                'Required' => false,
                'Validators' => [],
                'Filters' => ['StripTags'],
            ]
        );

        $this->addElement('hidden', 'group_id',
            [
                'Required' => false,
                'Validators' => [],
                'Filters' => ['StripTags'],
            ]
        );
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    255,
                    1
                )
            ),
            'Filters' => array('StripTags')
        )
        );
        
        $this->addElement($this->getDefaultTextElementName(), 'ordr', array(
            'Label' => _('Порядок следования'),
            'Required' => false,
            'Value' => HM_Htmlpage_HtmlpageModel::ORDER_DEFAULT,
            'Validators' => array(
                array('Digits')
            ),
            'Filters' => array('StripTags')
        )
        );        
                
		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array(            
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'group_id',
        	'role',
        	'name',
        	'ordr',
            'submit'),
            'groupPages',
            array(
            'legend' => _('Группа страниц')
            ));
        
        parent::init(); // required!
        
	}
	
}