<?php
/**
 * Форма для редактирования конфиг-файлов Skillsoft курсов
 *
 */
class HM_Form_Video extends HM_Form
{
    //public $status;

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('info');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('video/list/')
        ));

        $this->addElement('hidden', 
          'videoblock_id', 
          array('Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(),
          'name', 
          array('Label' => _('Название'),
                'Required' => true,
                'Validators' => array(array('validator' => 'StringLength',
                                              'options' => array('max' => 255, 'min' => 3))),
                'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
            'Label' => _('Файл ресурса'),
            'Required' => false,
            'Destination' => realpath(Zend_Registry::get('config')->path->upload->files),
            'validators' => array(
                array('Count', false, 1),
                //array('Extension', false, 'zip'),
                //array('IsCompressed', false, 'zip')
            )
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'embedded_code', array(
            'Label' => _('Код для вставки'),
            'Description' => _('Сюда можно вставить HTML-код внешнего сервиса, например Youtube или Vimeo. Если это поле заполнено, поле "Файл" не используется.'),
            'Required' => false,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'is_default', array(
                'Label' => _('Отображать по умолчанию в рабочей области виджета'),
                'Required' => false,
            )
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        $this->addDisplayGroup(array(
                'cancelUrl',
                'name',
                'file',
                'embedded_code',
                'is_default',
                'submit'
            ),
           'resourceGroup',
           array('legend' => '')
        );
        
        parent::init(); // required!
    }


}