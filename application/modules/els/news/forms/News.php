<?php
class HM_Form_News extends HM_Form
{
	public function init()
	{
	    $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('news');
        $this->setAction($this->getView()->url(array('module' => 'news', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'news', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('hidden', 'subject_name', array(
            'required' => false,
            'filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('hidden', 'subject_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            )
        ));

        $this->addElement('hidden', 'icon_url');


        $this->addElement($this->getDefaultTextElementName(), 'name', array(
                'Label' => _('Название'),
                'Description' => _('Максимальное количество символов не должно превышать 255 символов.'),
                'Filters' => array('HtmlSanitizeRich'),
                'Validators' => array(
                    array('StringLength', 255, 1),
                ),
                'Required' => false,
            )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'announce', array(
            'Label' => _('Анонс новости'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 3),
            ),
            'Rows' => 5
        ));


        $this->addElement('checkbox', 'mobile2', array(
            'Label' => _('Показать на главном экране мобильного приложения'),
            'Description' => _('Если установлен, то страница будет отображаться в мобильном приложении'),
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Полный текст новости'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'url', array(
            'Label' => _('URL для перенаправления'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,3)
            ),
        ));


        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());


        $this->addDisplayGroup(
            array(
                'id',
                'cancelUrl',
                'subject_name',
                'subject_id',
                'name',
                'url',
                'announce',
                'mobile2',
                'message',
                'mobile2'
            ),
            'newsGroup',
            array('legend' => _('Общие свойства'))
        );


        $this->addElement($this->getDefaultCheckboxElementName(), 'visible', array(
           'Label' => _('Отображать страницу в виджете "Новости"'),
           'Description' => _('Если установлено, то страница будет отображаться в виджете "Новости"'),
        ));

        $this->addElement($this->getDefaultFileElementName(), 'icon_banner', array(
            'Label' => _('Изображение для новости'),
            'Description' => _('Для использования в виджете "Новости". Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'subject' => null,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'not_icon', array(
           'Label' => 'Нет изображения',
           'Description' => _('Если установлено, то изображение для этой страницы в виджете "Новости" будет сброшено'),
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'date_end', array(
           'Label' => _('Дата снятия с публикации'),
           'Required' => false,
           'Validators' => array(array(
               'StringLength',
               false,
               array('min' => 10, 'max' => 50),
           )),
           'Filters' => array('StripTags'),
           'JQueryParams' => array(
               'showOn' => 'button',
               'buttonImage' => "/images/icons/calendar.png",
               'buttonImageOnly' => 'true'
           ),
        ));

        $this->addDisplayGroup(
            array(
               'visible',
               'icon_banner',
               'not_icon',
               'date_end',
            ),
            'groupInfoBlock',
            array(
               'legend' => _('Отображение в виджете "Новости"')
            )
        );

        $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS, $this->getParam('news_id', 0));
        $this->addClassifierDisplayGroup($classifierElements);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

    /**
     * @param null $newsId
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public function saveIcon($newsId = null)
    {
        if ($this->getValue('not_icon')) {
            $this->getElement('icon_url')->setValue(null);
            return;
        }

        $newsId = $newsId ?: $this->getValue('id');
        if (!$newsId) return;

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
                $dst = HM_Htmlpage_HtmlpageModel::getIconFolder($newsId) . $newsId . $extension;

                $img = PhpThumb_Factory::create($src);
                $img->resize(HM_News_NewsModel::BANNER_WIDTH);
                $img->save($dst);

                unlink($src);
                $icon = Zend_Registry::get('config')->url->base . preg_replace('/^.+?public\//', '', $dst);
                $this->getElement('icon_url')->setValue($icon);
            }
        }
    }
}