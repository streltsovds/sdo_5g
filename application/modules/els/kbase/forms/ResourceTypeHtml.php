<?php
class HM_Form_ResourceTypeHtml extends HM_Form_ResourceType
{
    const TAB_PAGE = 'tab-page';
    const TAB_SLIDER = 'tab-slider';
    const TAB_EMBED = 'tab-embed';
    const TAB_URL = 'tab-url';

	public function init()
	{
        parent::init();

        $this->addElement($this->getDefaultTabsElementName(), 'tabs', [
            'tabs' => [
                self::TAB_PAGE => [
                    'title' => _('Страница'),
                    'description' => _('На этой вкладке можно создать одну HTML-страницу (например, путём копирования из другой программы)'),
                    'groups' => ['page'],
                ],
                self::TAB_SLIDER => [
                    'title' => _('Слайдер'),
                    'description' => _('На этой вкладке можно создать ресурс, состоящий из нескольких HTML-страниц с возможностью их переключения'),
                    'groups' => ['slider'],
                ],
                self::TAB_EMBED => [
                    'title' => _('Код для вставки'),
                    'description' => _('На этой вкладке можно создать ресурс путём вставки готового HTML-кода (например с YouTube)'),
                    'groups' => ['embed'],
                ],
                self::TAB_URL => [
                    'title' => _('Ссылка'),
                    'description' => _('На этой вкладке можно создать ресурс-ссылку на внешний сайт'),
                    'groups' => ['url'],
                ],
            ],
            'form' => $this,
        ]);

        /****** Код ******/
        $this->addElement($this->getDefaultTextAreaElementName(), 'content_embed', array(
            'Label' => _('Код для вставки'),
            'Description' => _('Сюда можно вставить: <li>специальный HTML-код для вставки с внешнего ресурса (например, с YouTube)'),
            'Required' => false,
            'class' => 'wide',
        ));

        $this->addDisplayGroup(
            ['resource_id' ,'content_embed'],
            'embed',
            array('legend' => '')
        );

        /****** URL ******/
        $this->addElement($this->getDefaultTextElementName(), 'content_url', array(
            'Label' => _('Ссылка'),
            'Required' => false,
        ));

        $this->addDisplayGroup(
            ['resource_id' ,'content_url'],
            'url',
            array('legend' => '')
        );

        /****** Страница ******/

        $this->addElement($this->getDefaultWysiwygElementName(), 'content_page', array(
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            //'toolbar' => 'hmToolbarMidi',
        ));

        $this->addDisplayGroup(
            ['content_page'],
            'page',
            array('legend' => '')
        );

        /****** Конструктор ******/

        $this->addElement('hidden', 'content_slider', array(
            'value' => true,
            'Required' => false,
        ));

        $resourceId = $this->_resource ? $this->_resource->resource_id : null;
        if (!$resourceId) {
            $resourceId = HM_Material_MaterialModel::MATERIAL_NEW;
        }

        $this->addElement($this->getDefaultIframeElementName(), 'content_slider_iframe', array(
            'Required' => false,
            'url' => '/editor/index.html?id=' . $resourceId,
            'Validators' => array(
//                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            ),
            'htmlAttribs' => [
                'class' => 'slides-editor-iframe',
                'data-form-element-iframe-before-form-save' => 'saveSlidesRequestPromise',
                'data-slider-editor-no-save-button' => 1,
            ]
        ));

        $this->addDisplayGroup(
            ['content_slider', 'content_slider_iframe'],
            'slider',
            array('legend' => '')
        );


        $this->addSubmitBlock();
	}
}
