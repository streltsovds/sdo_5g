<?php
class HM_Form_ClassifiersTypes extends HM_Form
{

    public function init()
    {
        $model = new HM_Classifier_Type_TypeModel(null);        
        
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('classifiersTypes');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'index'))
        ));
        
        $this->addElement('hidden', 'type_id', array(
            'Required' => true,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int')));
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    255,
                    1)),
            'Filters' => array(
                'StripTags'))

        );
        
        $this->addElement($this->getDefaultMultiCheckboxElementName(), 'link_types', array(
            'Label' => _('Области применения'),
            'Required' => false,
            'MultiOptions' => HM_Classifier_Link_LinkModel::getEditTypes($this->getService('User')->getCurrentUserRole()),
            'Filters' => array(
                'StripTags'
            )
        ));


        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));
        
        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'type_id',
                'name',
                'link_types',
                'submit'
            ),
            'classifiersTypes', array(
            'legend' => _('Классификатор')));

        parent::init(); // required!
    }


    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'icon') {
            $decorators = parent::getElementDecorators($alias, 'ClassifierImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }


}