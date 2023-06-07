<?php
class HM_Form_QuestStep1 extends HM_Form_SubForm {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('questStep1');

        $type = $this->getParam('type', 0);
        if ($questId = $this->getParam('quest_id', 0)) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
            $type = $quest->type;
        }

        if ($subjectId = $this->getParam('subject_id', 0)) {
            $cancelUrl = array('controller' => 'subject', 'action' => 'list', 'subject_id' => $subjectId, 'quest_id' => null, 'gridmod' => null, 'subForm' => null);
        } elseif($questId = $this->getParam('quest_id', 0)) {
            // вынес в отдельный иф, иначе в тестах внутри курса срабатывает ветка if ($subjectId) и тип теста можно редактировать
            $cancelUrl = array('controller' => 'index', 'action' => 'card', 'quest_id' => $questId);
        } else {
            $cancelUrl = array('controller' => 'list', 'action' => 'index', 'quest_id' => null);
        }

        $this->addElement($this->getDefaultStepperElementName(), 'stepper', [
            "steps" => array(
                _('Общие свойства') => ['quests'],
                _('Комментарии') => ['quests_comments'],
            ),
            "form" => $this
        ]);

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url($cancelUrl)
            )
        );

        $this->addElement('hidden',
            'quest_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );


        $this->addElement('hidden',
            'creator_role',
            array(
                'Required' => false,
                'Value' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'type', array(
            'Label' => _('Тип'),
            'multiOptions' => HM_Quest_QuestModel::getTypes(),
            'disabled' => $type ? true : null,
            'value' => $type ? $type : HM_Quest_QuestModel::TYPE_PSYCHO,
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        ));
        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Метки'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'quest', 'controller' => 'list', 'action' => 'tags')),
            'value' => '',
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
//            'Label' => _('Метки'),
//            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
//            'json_url' => $this->getView()->url(array('module' => 'quest', 'controller' => 'list', 'action' => 'tags')),
//            'value' => '',
//        )));

        $this->addElement($this->getDefaultWysiwygElementName(), 'info', array(
            'Label' => _('Комментарий перед началом заполнения'),
            'Description' => _('Комментарий отображается перед началом заполнения формы, до снятия попытки (если применимо).'),
            'Required' => false,
            'class' => 'wide'
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'comments', array(
            'Label' => _('Комментарий в процессе заполнения'),
            'Description' => _('Комментарий отображается непосредственно в процессе заполнения формы'),
            'Required' => false,
            'class' => 'wide'
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'status', array(
            'Label' => _('Статус'),
            'multiOptions' => HM_Quest_QuestModel::getStatuses(),
        ));            

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'type',
            'status',
            'description',
            'tags',
        ),
            'quests',
            array('legend' => _('Общие свойства'))
        );
        
        $this->addDisplayGroup(array(
            'info',
            'comments',
        ),
            'quests_comments',
            array('legend' => _('Комментарии'))
        );
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Далее')));

        parent::init(); // required!
    }
}
