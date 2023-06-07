<?php
/**
 * Форма для создания и редактирования пользователей
 *
 */
class HM_Form_Idea extends HM_Form {

    public function setDefaults(array $defaults)
    {
        parent::setDefaults($defaults);

        $ideaId = $this->getParam('idea_id', 0);
        $populatedFiles = $this->getService('Idea')->getPopulatedFiles($ideaId);

        $files = $this->getElement('files');
        $files->setValue($populatedFiles);

        return $this;
    }

    public function init() {

        $ideaId = $this->getParam('idea_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('idea');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $_SERVER['HTTP_REFERER'],
            )
        );

        $this->addElement('hidden',
            'idea_id',
            array(
                'Required' => false,
                'value' => $this->getParam('idea_id', 0)
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array('Label' => _('Фомулировка'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1),
//                array('AlphaForNames'),
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
                'Label' => _('Описание'),
                'Required' => false,
                'class' => 'wide',
        ));


        $this->addElement($this->getDefaultSelectElementName(), 'status', array(
            'Label' => _('Статус'),
            'Required' => false,
            'Validators' => array(

            ),
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => HM_Idea_IdeaModel::getStates(),
        ));

        $tags = $ideaId ? $this->getService('Tag')->getTags($ideaId, $this->getService('TagRef')->getIdeaType() ) : '';
        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Ключевые слова'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'idea', 'controller' => 'list', 'action' => 'tags')),
            'value' => $tags,
            'Filters' => array()
        ));


        $idea = $this->getService('Idea')->find($ideaId)->current();

        $this->addElement($this->getDefaultFileElementName(), 'photo', array(
            'Label' => _('Изображение'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,//true,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
//            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'user_id' => 0,
//            'delete_button'=>true,
            'preview_url'=> $ideaId ? '/upload/idea/'.$ideaId.'.jpg?rng='.rand() : null
        ));

        $photo = $this->getElement('photo');
        $photo->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                    )
                )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);
        
        $this->addElement($this->getDefaultCheckboxElementName(), 'anonymous', array(
            'Label' => 'Анонимная',
            'Value' => $idea->anonymous,
        ));

        $this->addElement($this->getDefaultFileElementName(), 'files',
            array(
                 'Label'      => _('Файлы'),
                 'Required'   => false,
                 'Filters'    => array('StripTags'),
                 'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                 'file_size_limit' => 0,
                 'file_upload_limit' => 10,
//                 'preview_url'=> '/upload/files/form_study_plan.docx'//???//Сделать когда будет реализовано множественное отображение в элементе
            )
        );

        $this->addDisplayGroup(array(
            'name',
            'status',
            'photo',
            'description',
        ),
            'common',
            array('legend' => _('Общее'))
        );

        $this->addDisplayGroup(array(
            'tags',
            'files',
            'anonymous'
        ),
            'additional',
            array('legend' => _('Дополнительно'))
        );

        $this->addElement($this->getDefaultMultiSetElementName(), 'urls', array(
            'Required' => false,
            'dependences' => array(
                new HM_Form_Element_Vue_Text(
                    'variant',
                    array('Label' => _('Адрес ссылки'), 
                        'placeholder' => 'http://',
                    )
                ),
            ),
        ));
        $this->addDisplayGroup(array('urls'),'urls_group',array('legend' => _('Ссылки')));

        $classifierElements = $this->addClassifierElements(
                HM_Classifier_Link_LinkModel::TYPE_IDEA,
                $ideaId
        );
        $this->addClassifierDisplayGroup($classifierElements);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); 
    }


    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'photo') {
            $decorators = parent::getElementDecorators($alias, 'UserImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }


}