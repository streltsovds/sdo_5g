<?php

/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 28.07.2016
 * Time: 10:41
 */
class HM_Form_DesignSettings extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('design-settings');

        $this->addElement($this->getDefaultTextElementName(), 'windowTitle', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'index_description', array(
            'Label' => _('Текст в окне авторизации'),
            'Description' => '',
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'skin', array(
            'Label' => _('Цветовая схема'),
            'Required' => false,
            'Validators' => array(
            ),
            'MultiOptions' => $this->getService('Option')->getAvailableSkins(),
        ));

        $this->addFileElement('logo', _('Логотип'));

        for($i=1; $i <= 5; $i++) {
            $this->addFileElement('loginBg'.$i, _('Фоновое изображение ' . $i));
        }

        $resetUrl = $this->getView()->url(array(
            'module' => 'interface',
            'controller' => 'edit',
            'action' => 'design-settings',
            'reset-settings' => 1), null, true);

        $this->addElement('hidden', 'resetUrl', array(
            'Required' => false,
            'Value' => $resetUrl
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'windowTitle',
                'logo',
                'skin',
            ),
            'title',
            array('legend' => 'Портал')
        );

        $this->addDisplayGroup(
            array(
                'index_description',
                'loginBg1',
                'loginBg2',
                'loginBg3',
                'loginBg4',
                'loginBg5',
            ),
            'backgroundImages',
            array('legend' => 'Страница/окно авторизации')
        );

        $this->addElement($this->getDefaultTextElementName(), 'vk', ['Label' => _('vk'),]);
//        $this->addElement($this->getDefaultTextElementName(), 'facebook', ['Label' => _('facebook'),]);
        $this->addElement($this->getDefaultTextElementName(), 'youtube', ['Label' => _('youtube'),]);
        $this->addElement($this->getDefaultTextElementName(), 'telegram', ['Label' => _('telegram'),]);
//        $this->addElement($this->getDefaultTextElementName(), 'instagram', ['Label' => _('instagram'),]);

        $this->addDisplayGroup(
            [
                'vk',
//                'facebook',
                'youtube',
                'telegram',
//                'instagram',
            ],
            'socialGroup',
            ['legend' => 'Ссылки на социальные сети в footer']
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init();

        // Почему-то работает после init, но не в addFileElement

        $logoElement = $this->getElement('logo');
        $logoElement->addDecorator('Image');

        for($i=1; $i <= 5; $i++) {
            $bgElement = $this->getElement('loginBg'.$i);
            $bgElement->addDecorator('Image');
        }
    }

    private function addFileElement($name, $title)
    {
        $this->addElement($this->getDefaultFileElementName(), $name, array(
            'Label' => $title,
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Validators' => array(
                array('Count', false, 1),
                array('Extension', false, 'png,jpg,gif')
            ),
            'file_size_limit' => 5*1024*1024,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg;*.svg',
            'file_upload_limit' => 1,
            'Required' => false
        ));

        $element = $this->getElement($name);
        $element
            ->addDecorator('Image')
            ->addValidator('FilesSize', true,['max' => '5MB'])
            ->addValidator('Extension', true, 'jpg,png,gif,jpeg,svg')
            ->setMaxFileSize(5*1024*1024);
    }
}