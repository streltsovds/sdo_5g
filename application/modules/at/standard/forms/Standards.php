<?php
class HM_Form_Standards extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('standards');
        
        if ($standardId = $this->getParam('standard_id', 0)) {
            $standard = Zend_Registry::get('serviceContainer')->getService('AtStandard')->find($standardId)->current();
        } 

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index'))
            )
        );
        
        $this->addElement('hidden',
            'standard_id',
            array(
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );   

        $this->addTextElement('name', _('Название'), 1024, true);
        $this->addTextElement('number', _('Регистрационный номер'), 50);
        $this->addTextElement('code', _('Код'), 50);
        $this->addTextElement('area', _('Область проф. деятельности'), 1024);
        $this->addTextElement('vid', _('Вид проф. деятельности'), 1024);
        $this->addTextElement('prikaz_number', _('№ приказа Минтруда'), 50);
        //$this->addTextElement('prikaz_date', _('Дата приказа Минтруда'), 50);
        $this->addElement('datePicker', 'prikaz_date', array(
            'Label' => _('Дата приказа Минтруда'),
            'required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    50,
                    10
                )
            ),
            'Value' => date('d.m.Y'),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true',
                'buttonText' => _('Нажмите для выбора даты')
            )
        ));
        $this->addTextElement('minjust_number', _('№ приказа Минюста'), 50, false);
        //$this->addTextElement('minjust_date', _('Дата приказа Минюста'), 50, false);
        $this->addElement('datePicker', 'minjust_date', array(
            'Label' => _('Дата приказа Минюста'),
            'required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                    50,
                    10
                )
            ),
            'Value' => null,
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true',
                'buttonText' => _('Нажмите для выбора даты')
            )
        ));
        $this->addTextElement('sovet', _('Совет по проф. квалификациям'), 1024, false);
        $this->addTextElement('url', _('Ссылка на профстандарт'), 1024);

        $this->addDisplayGroup(array(
            'cancelUrl', 'name', 'number', 'code', 'area', 'vid', 'prikaz_number', 'prikaz_date', 'minjust_number', 'minjust_date', 'sovet', 'url'
        ),
            'standards',
            array('legend' => _('Профстандарт'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    function addTextElement($name, $title, $maxLength=255, $required=true) 
    {
        $this->addElement($this->getDefaultTextElementName(), $name, array(
        	'Style' => 'width:300px',
            'Label' => $title,
            'Required' => $required,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => $maxLength)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        ));
    }

}