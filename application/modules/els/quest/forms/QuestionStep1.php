<?php
class HM_Form_QuestionStep1 extends HM_Form_SubForm {

    protected $_typeElements = array();
    
    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('questionStep1');
        
        $subjectId = $this->getParam('subject_id', 0);
        $label = _('Далее');

        if ($questId = $this->getParam('quest_id', 0)) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
            if ($quest->scale_id) {
                $label =  _('Сохранить');
            }
        }        
        
        if ($questionId = $this->getParam('question_id', 0)) {
            $question = Zend_Registry::get('serviceContainer')->getService('QuestQuestion')->find($questionId)->current();
        }        

        //на всякий случай через moduleName
        $module = $this->getView()->getRequest()->getModuleName();
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    $subjectId ? array(
                        'module' => $module,
                        'controller' => 'question',
                        'action' => 'list',
                        'subject_id' => $subjectId,
                        'quest_id' => $questId, 'gridmod' => null,
                        'subForm' => null
                    ) : array('module' => $module, 'controller' => 'index', 'action' => 'card', 'quest_id' => $questId)
                , null, true)
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
            'question_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );
        
        $types = $quest->getAvailableTypes();
        if($quest->type == HM_Quest_QuestModel::TYPE_POLL && $quest->scale_id){
            unset($types[HM_Quest_Question_QuestionModel::TYPE_MULTIPLE]);
        }
        if (!empty($questionId)) {
            $this->addElement('hidden', 'type', array(
                'value' => $quest->type,
            ));          
        }
        
        $this->addElement($this->getDefaultSelectElementName(), 'type', array(
            'Label' => _('Тип'),
            'multiOptions' => $types,
            'disabled' => ($questionId) ? true : null,
            'value' => ($questionId) ? $quest->type : HM_Quest_Question_QuestionModel::TYPE_SINGLE,
        ));

        $clusters = $this->getService('QuestCluster')->getQuestClusters($questId);
        $this->addElement($this->getDefaultSelectElementName(), 'cluster_id', array(
            'Label' => _('Блок вопросов'),
            'filters' => array('int'),
            'multiOptions' => $clusters,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_QUEST_CLUSTER
        ));        

        $this->addElement($this->getDefaultCheckboxElementName(), 'shuffle_variants', array(
            'Label' => _('Перемешивать варианты ответов'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'shorttext', array(
            'Label' => _('Краткий текст'),
            //'Description' => _(''),
            'Required' => false,
            'Filters' => array('StripTags'),
        )
        );



        $this->addElement($this->getDefaultWysiwygElementName(), 'question', array(
            'Label' => _('Текст'),
            'description' => _('Для вопроса с типом "Заполнение пропусков" используйте символы "[" и "]" для обозначения пропусков в тексте.'),
            'Required' => true,
        ));

        $displayGroup = array(
            'cancelUrl',
            'type',
            'cluster_id',
            'shorttext',
            'shuffle_variants',
            'variants_use_wysiwyg',
            'question',
        );

        if (!$quest->scale_id) {
            $this->addElement($this->getDefaultCheckboxElementName(), 'variants_use_wysiwyg', array(
                'Label' => _('Использовать Wysiwyg-редактор в вариантах ответа'),
                'value' =>  HM_Quest_Question_QuestionModel::VARIANTS_USE_WYSIWYG_OFF,
            ));
        } else {
            unset($displayGroup['variants_use_wysiwyg']);
        }



        $this->addDisplayGroup(
           array(
                'cancelUrl',
                'type',
                'cluster_id',
                'shorttext',
                'shuffle_variants',
                'variants_use_wysiwyg',
                'question',
            ),
            'question_group',
            array('legend' => _('Общие свойства'))
        );        

        if ($quest->type) {
            $method = sprintf('init%s', ucfirst($quest->type));
            if (method_exists($this, $method)) $this->$method(); 
        }
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => $label));

        $this->appendTypeOnChangeScript();

        parent::init(); // required!
    }

    /************** tests **************/
    
    public function initTest()
    {
        $questionId = $this->getParam('question_id', 0);
        if ($questionId) {
            $question = $this->getService('QuestQuestion')->find($questionId)->current();
            if (!in_array($question->type, array(
                HM_Quest_Question_QuestionModel::TYPE_SINGLE,
                HM_Quest_Question_QuestionModel::TYPE_MULTIPLE))) {
                $this->addElement('hidden', 'mode_scoring', array('Value' => HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT));
            } else {
                $this->addElement($this->getDefaultRadioElementName(), 'mode_scoring', array(
                    'Label' => _('Способ оценивания'),
                    'Description' => _('Данная настройка применима только к вопросам с одиночным и множественным выбором'),
                    'Required' => false,
                    'Validators' => array(
                    ),
                    'Value' => HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT,
                    'Filters' => array(
                        'Int'
                    ),
                    'MultiOptions' => HM_Quest_Question_QuestionModel::getScoringModes(),
                ));
            }
        } else {
            $this->addElement($this->getDefaultRadioElementName(), 'mode_scoring', array(
                'Label' => _('Способ оценивания'),
                'Description' => _('Данная настройка применима только к вопросам с одиночным и множественным выбором'),
                'Required' => false,
                'Validators' => array(
                ),
                'Value' => HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT,
                'Filters' => array(
                    'Int'
                ),
                'MultiOptions' => HM_Quest_Question_QuestionModel::getScoringModes(),
            ));
        }

        $this->addElement($this->getDefaultTextElementName(), 'score_min', array(
            'Label' => _('Минимальный балл'),
            'class' => 'brief',
            'Value' => 0,
            'Required' => true,
            'Description' => _('Минимальный балл, который можно получить за ответ на этот вопрос. Целое число, большее или равное 0.'),
//            'Filters' => ['StringTrim', 'FloatPoint'],
//            'Validators' => ['FloatLocalized'],
            'Validators' => [
                'Int',
                ['GreaterThan', false, [-1]],
            ],
        ));

        $this->addElement($this->getDefaultTextElementName(), 'score_max', array(
            'Label' => _('Максимальный балл'),
            'class' => 'brief',
            'Value' => 1,
            'Required' => true,
            'Description' => _('Максимальный балл, который можно получить за ответ на этот вопрос. Целое число, большее или равное минимальному баллу.'),
//            'Filters' => ['StringTrim', 'FloatPoint'],
//            'Validators' => ['FloatLocalized', [
//                'FloatGreaterOrEqualThanValue',
//                false,
//                ['name' => 'score_min']
//            ]],
            'Validators' => [
                'Int',
                ['GreaterOrEqualThanValue', false, ['name' => 'score_min']],
            ],
        ));

        $this->addElement($this->getDefaultTextElementName(), 'justification', array(
            'Label' => _('Ссылка на материал'),
            'Value' => "",
            'Required' => false,
//            'Description' => _('Максимальный балл, который можно получить за ответ на этот вопрос. Может быть дробным; в этом случае десятичный разделитель - точка.'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'order', array(
            'Label' => _('Порядок'),
            'class' => 'brief',
            'Value' => 0,
            'Required' => false,
            'Description' => _('Порядковый номер вопроса в тесте'),
        ));
        
        $this->addDisplayGroup(array(
            'mode_scoring',
            'score_min',
            'score_max',
            'justification',
            'order'
        ),
            'group_test',
            array('legend' => _('Настройки вопроса в тесте'))
        );        
    }
    
    /************** psychos **************/    
    
    protected function initPsycho()
    {
        // value здесь не работает (баг); задаётся в setDefaults
        $this->addElement('hidden', 'show_free_variant', array('Value' => HM_Quest_Question_QuestionModel::SHOW_FREE_VARIANT_OFF));
        $this->addElement('hidden', 'mode_scoring', array('Value' => HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT));

        /* Если она скрыта, зачем её рисовать и скрывать, если можно сразу не рисовать?
         $this->addDisplayGroup(array(
            'mode_scoring',
            'show_free_variant',
        ),
            'group_test',
            array('legend' => _('Настройки вопроса в тесте'), 'hidden' => true)
        );*/
    }    
    
    /************** forms **************/    
    
    protected function initForm()
    {
        // воозможно это должно определяться на уровне вопроса
        $this->addElement($this->getDefaultRadioElementName(), 'show_free_variant', array(
            'Label' => _('Возможность открытого ответа'),
            'Description' => _('В этом случае к каждому вопросу формы автоматически добавляется поле для ввода пользователем произвольного ответа'),
            'Required' => false,
            'Validators' => array(
            ),
            'Value' => 1,
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => array(
                HM_Quest_Question_QuestionModel::SHOW_FREE_VARIANT_OFF => _('Нет'),
                HM_Quest_Question_QuestionModel::SHOW_FREE_VARIANT_ON => _('Да'),                
            ),
            'separator' => ' '
        ));
        
        // value здесь не работает (баг); задаётся в setDefaults
        $this->addElement('hidden', 'mode_scoring', array('Value' => HM_Quest_Question_QuestionModel::MODE_SCORING_OFF));

        $this->addDisplayGroup(array(
            'show_free_variant',
            'mode_scoring',
        ),
            'group_form',
            array('legend' => _('Настройки вопроса в форме'))
        );         
    }    
    
    /************** end **************/
    
    
    public function getElementDecorators($alias, $first = 'ViewHelper')
    {
        if (in_array($alias, array('cluster_id'))) {
            return array (
                array($first),
                array('RedErrors'),
                array('AddOption'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt')),
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }

    protected function appendTypeOnChangeScript() {
        $view = $this->getView();
        //хак чтобы консты в heredoc заюзать
        $questionTypes = array_keys(HM_Quest_Question_QuestionModel::getTypes());
        $questionTypes = array_combine($questionTypes, $questionTypes);
        $scoringModes = array_keys(HM_Quest_Question_QuestionModel::getScoringModes());
        $scoringModes = array_combine($scoringModes, $scoringModes);

        $typeOnChangeScript = <<<DOC
        if (window.hm) {
            window.console = window.hm.core.Console;
        }
        
        window.onload = function(){
            jQuery(function(){
        function checkQuestionType(element) {
            if(element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_SINGLE]}' ||
                element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_MULTIPLE]}' ||
                element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_FILE]}'
            ) {
                $('input[name=mode_scoring]').each(function() {
                    var \$this = $(this);
                    if(\$this.val() == '{$scoringModes[HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT]}'){
                        \$this.parent().parent().show();
                    }
                });
            } else {
                $('input[name=mode_scoring]').each(function() {
                    var \$this = $(this);
                    if(\$this.val() == '{$scoringModes[HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT]}'){
                        console.log('hide');
                        \$this.parent().parent().hide();
                    }
                    if(\$this.val() == '{$scoringModes[HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT]}'){
                        \$this.prop( "checked", this.checked );
                    }
                });
            }
            if(element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER]}'){
                $('input[name=shuffle_variants]').parent().hide();
            } else {
                $('input[name=shuffle_variants]').parent().show();
            }
            if(element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER]}' ||
                element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_TEXT]}' ||
                element.val() == '{$questionTypes[HM_Quest_Question_QuestionModel::TYPE_FILE]}'
            ){
                $('input[name=variants_use_wysiwyg]').parent().hide();
            } else {
                $('input[name=variants_use_wysiwyg]').parent().show();
            }
        };

        var select = $('input[name=type]');
        
        function onChange() {
            checkQuestionType(select);
        }

        onChange();
        select.change(onChange);
    });
    }
        
DOC;


        $view->inlineScript()->appendScript($typeOnChangeScript);
    }
}