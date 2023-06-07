<?php
class HM_Form_Page extends HM_Form{
	
	public function init(){
		
		$this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('page');
        $pageId = $this->getParam('page_id', 0);


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
        
        $this->addElement('hidden', 'page_id', array(            
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('hidden', 'icon_url');

        $this->addElement('hidden', 'group_id', array(
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));

        $request = $this->getRequest();
        $role = $request->getParam('key');

        if (is_string($role))
            $this->addElement('hidden', 'role', array(
                'Required' => false,
                'Value' => $role
            ));

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
        ));

        $this->addElement($this->getDefaultTextElementName(), 'url', array(
            'Label' => _('URL-адрес для перенаправления'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    255,
                    1
                )
            ),
            'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое содержание'),
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'Label' => _('Содержимое'),
            'Filters' => array('HtmlSanitizeRich'),
            'Required' => false,
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder'
            )),
            //'toolbar' => 'hmToolbarMaxi',
            'fmAllow' => true,
            )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), 'visible', array(
            'Label' => _('Опубликована'),
            'Description' => _('Если атрибут установлен, то страница будет отображаться на главной странице соответствующей роли или в footer'),
            'value' => 1
        ));



        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'page_id',
                'name',
                'ordr',
                'url',
                'description',
                'text',
                'visible',
            ),
            'groupPages',
            array(
                'legend' => _('Информационная страница'),
            )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), 'in_slider', array(
            'Label' => _('Отображать страницу в виджете "Информационный слайдер"'),
            'Description' => _('Если установлен, то страница будет отображаться в виджете "Информационный слайдер" (вне зависимости от значения атрибута "Опубликована")'),
        ));

        $page = $this->getService('Htmlpage')->find($pageId)->current();
        $photo = '';
        if ($page)  $photo = $page->getUserIcon();
        if (strlen($photo) && substr($photo, 0, 1) !== '/') $photo = '/' . $photo;
        $this->addElement($this->getDefaultFileElementName(), 'icon_banner', array(
                'Label' => _('Изображение'),
                'Description' => _('Изображение для использования в виджете "Информационный слайдер". Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
                'file_upload_limit' => 1,
//                'delete_button' => true,
                'preview_url' => $photo,
                'subject' => null,
            )
        );


//        $icon = '';
//        if ($pageId != 0) {
//            $page =  $this->getService('Htmlpage')->getOne($this->getService('Htmlpage')->find($pageId));
//            if (false !== $page) $icon = $page->icon_url ? $page->icon_url : '';
//        }
//        $this->addElement('serverFile', 'server_icon_banner', array(
//                'Label' => _('Выбрать изображение для информационного слайдера на сервере'),
//                'Value' => $icon,
//                'preview' => $icon,
//            )
//        );

        $this->addDisplayGroup(array(
            'in_slider',
            'icon_banner',
//            'server_icon_banner',
        ),
            'groupInfoBlock',
            array(
                'legend' => _('Отображение в виджете "Информационный слайдер"')
            ));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init(); // required!
        
	}


	public function saveIcon($pageId = null)
    {
        if ($this->getElement('icon_banner')->isDeleted()) {
            $this->getElement('icon_url')->setValue(null);
            $this->getElement('icon_banner')->setPreviewUrl(null);
            return;
        }

        if (!$pageId) $pageId = $this->getValue('page_id');
        if (!$pageId) return;
        $session = new Zend_Session_Namespace('upload');
        $uploadId = $this->getRequest()->getParam('icon_banner');

        if (isset($session->{$uploadId})) {
            $upload = $session->{$uploadId};
            if (count($upload)) {
                $fileInfo = $upload[0];
                $src = $fileInfo['tmp_name'];

                $extension = '';
                if (preg_match('/\.([^\.]+?)$/', $src, $m) ) {
                    $extension = '.' . $m[1];
                }

                $dst = HM_Htmlpage_HtmlpageModel::getIconFolder($pageId) . $pageId . $extension;
                copy($src, $dst);
                unlink($src);
                $icon = Zend_Registry::get('config')->url->base . preg_replace('/^.+?public\//', '', $dst);
                $this->getElement('icon_url')->setValue($icon);
                $this->getElement('icon_banner')->setPreviewUrl($icon);
            }
        }
    }

    public function setDefaults(array $defaults)
    {
        parent::setDefaults($defaults); // TODO: Change the autogenerated stub
        $this->getElement('icon_banner')->setPreviewUrl($defaults['icon_url']);
        return $this;
    }
}