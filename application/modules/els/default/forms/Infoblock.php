<?php
class HM_Form_Infoblock extends HM_Form_SubForm
{
    /**
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('infoblock');

        $this->addElement(
            'hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'index'
                    ),
                    NULL,
                    TRUE
                )
            )
        );

        $this->addElement($this->getDefaultFileElementName(), 'background', array(
            'Label' => _('Загрузить изображение для заднего фона'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для использования в вижете "Витрина учебных курсов (расширенная)". Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'infoblock_name' => '',
            'delete_button'=>true,
        ));

        $background = $this->getElement('background');
        $background->addDecorator('InfoblockImage')
            ->addValidator('FilesSize', true, array(
                    'max' => '10MB'
                )
            )
            ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
            ->setMaxFileSize(10485760);

        $this->addDisplayGroup(array(
                'cancelUrl',
                'background'
            ),
            'settings',
            array('legend' => _('Настройки'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'background') {
            $decorators = parent::getElementDecorators($alias, 'InfoblockImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }
}