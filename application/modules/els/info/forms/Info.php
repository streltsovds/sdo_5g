<?php
/**
 * Форма для редактирования конфиг-файлов Skillsoft курсов
 *
 */
class HM_Form_Info extends HM_Form
{
    //public $status;

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('info');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('info/list/')
        ));

        $this->addElement('hidden', 
                          'nID',
                          array('Required' => true,
                                'Validators' => array('Int'),
                                'Filters' => array('Int')));
        $this->addElement($this->getDefaultCheckboxElementName(),
                          'show',
                          array('label' => _('Опубликован'),
                          'value' => true));
        $this->addElement($this->getDefaultTextElementName(), 
                          'Title',
                          array('Label' => _('Название'),
                                'Required' => true,
                                'Validators' => array(array('validator' => 'StringLength',
                                                            'options' => array('max' => 255, 'min' => 3))),
                                'Filters' => array('StripTags')));



        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Содержание'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array('HtmlSanitizeRich'),
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder'
            )),
            //'toolbar' => 'hmToolbarMaxi',
            'fmAllow' => true,
        ));

        $this->addElement($this->getDefaultTagsElementName(), 'resource_id', array(
            'Label' => _('Ресурс'),
            'Validators' => array(
            ),
            'Description' => _('Используйте знак # для указания ID ресурса'),
            'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
            'value' => array(),
            'newel' => false,
            'height' => 1,
            'maxitimes' => 1,
            'Filters' => array()
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('resource_id', array(
//                'Label' => _('Ресурс'),
//                'Description' => _('Используйте знак # для указания ID ресурса'),
//                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
//                'value' => array(),
//                'newel' => false,
//                'height' => 1,
//                'maxitimes' => 1,
//                'Filters' => array()
//            )
//        ));
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        $this->addDisplayGroup(array('cancelUrl',
                                    'nID',
                                    'show',
                                    'Title',
                                    'message',
                                    'resource_id',
                                    'submit'),
                              'resourceGroup',
                               array('legend' => 'Информационный блок'));
        
        parent::init(); // required!
    }


}