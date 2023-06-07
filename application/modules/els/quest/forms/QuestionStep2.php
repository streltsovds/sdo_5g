<?php
class HM_Form_QuestionStep2 extends HM_Form_SubForm {

    const DEFAULT_ELEMENT = 'variants';
    
    protected $_quest;
    protected $_question;
    protected $_type;
    protected $_session;
    protected $_modeScoring;
    
    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('questionStep2');
        $this->_session = $this->getSession();
        
        $subjectId = $this->getParam('subject_id', 0);
        
        if ($questId = $this->getParam('quest_id', 0)) {
            $this->_quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
        }        
        
        if ($questionId = $this->getParam('question_id', 0)) {
            $this->_question = Zend_Registry::get('serviceContainer')->getService('QuestQuestion')->find($questionId)->current();
        }

        $this->_type = $this->_question ? $this->_question->type : $this->_session['questionStep1']['type'];
        $this->_modeScoring = $this->_session['questionStep1']['mode_scoring'] ? $this->_session['questionStep1']['mode_scoring'] : $this->_question->mode_scoring;

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

        // сначала инициализируем по типу вопроса
        if ($this->_type) {
            $method = sprintf('_initQuestion%s', ucfirst($this->_type));
            if (method_exists($this, $method)) $this->$method();

            if ($this->_quest) {
                $method = sprintf('_init%s', ucfirst($this->_quest->type));
                if (method_exists($this, $method)) $this->$method();
            }
        }
        
        // затем накладываем ограничения в зависимости от типа опросника
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!        
    }

    protected function _initQuestionSingle()
    {
        $this->_addDefaultSingleChoice();
    }

    protected function _initQuestionMultiple()
    {
        $this->_addDefaultMultiset();        
    }

    protected function _initQuestionText()
    {
        $this->_addDefaultMultiset();    
    }

    protected function _initQuestionImagemap()
    {
        $inputs = array();

        $this->addElement($this->getDefaultImageElementName(), $inputs[] = self::DEFAULT_ELEMENT, array(
            'Required' => false,
            'answerName' => 'variant'
        ));

        $this->addElement('hidden',
            'file_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), $inputs[] = 'show_variants', array(
            'Required' => false,
            'Label' => _('Показывать варианты ответов при прохождении теста')
        ));

        $this->addDisplayGroup($inputs, 'imageGroup', array(
            'legend' => _('Варианты ответов')
        ));

    }

    protected function _initQuestionMapping()
    {
        $this->addElement($this->getDefaultMultiSetElementName(), self::DEFAULT_ELEMENT, array(
            'Required' => false,
            'dependences' => array(
                new HM_Form_Element_Vue_Text(
                    'data',
                    array('Label' => _('Текст варианта'))
                ), 
                new HM_Form_Element_Vue_Text(
                    'variant',
                    array('Label' => _('Соответствие'))
                ), 
            )
        ));  

        $this->addDisplayGroup(array(
            'cancelUrl',
            self::DEFAULT_ELEMENT,
        ),
            'group',
            array('legend' => _('Варианты ответов'))
        );  
    }

    protected function _initQuestionClassification()
    {
        $this->addElement($this->getDefaultMultiSetElementName(), self::DEFAULT_ELEMENT, array(
            'Required' => false,
            'dependences' => array(
                new HM_Form_Element_Vue_Text(
                    'data',
                    array('Label' => _('Текст варианта'))
                ), 
                new HM_Form_Element_Vue_Text(
                    'variant',
                    array('Label' => _('Класс'))
                ), 
            )
        ));  

        $this->addDisplayGroup(array(
            'cancelUrl',
            self::DEFAULT_ELEMENT,
        ),
            'group',
            array('legend' => _('Варианты ответов'))
        );  
    }

    protected function _initQuestionSorting()
    {
        $this->addElement($this->getDefaultMultiSetElementName(), self::DEFAULT_ELEMENT, array(
            'Required' => false,
//            'onRowAdd' => "function(e, \$newRow) { "
//                . "\$newRow.find('input[name*=\"data\"]').val(\$newRow.parent().children('.multiset-row').length);"
//            . "}",
            'dependences' => array(
                new Zend_Form_Element_Hidden(
                    'data',
                    array()
                ),
                new HM_Form_Element_Vue_Counter(
                    'data',
                    array(
                        'Label'    => _('№'),
//                        'class'    => 'brief',
                        'class'    => 'question-sorting-counter'
                    )
                ), 
                new HM_Form_Element_Vue_Text(
                    'variant',
                    array(
                        'Label' => _('Текст варианта'),
                        'class' => 'wide multiset-trigger',
                    )
                ), 
            )                
        ));  

        $this->addDisplayGroup(array(
            'cancelUrl',
            self::DEFAULT_ELEMENT,
        ),
            'group',
            array('legend' => _('Варианты ответов'))
        );  
    }
    

    /************** tests **************/

    protected function _initTest()
    {
        // разница только в if($multiset instanceof HM_Form_Element_MultiSet) между QuestionStep2 и
        // QuestionStep2Wysiwyg  - поэтому вытащил сюда все

        // данные типы вопросов не имеют срок с правильными и неправильными ответами (или разными весами)
        if (!in_array($this->_type, array(
            HM_Quest_Question_QuestionModel::TYPE_TEXT,
            HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION,
            HM_Quest_Question_QuestionModel::TYPE_MAPPING,
            HM_Quest_Question_QuestionModel::TYPE_SORTING,
            HM_Quest_Question_QuestionModel::TYPE_IMAGEMAP, // @todo: доработать imagemap, чтобы работал с обычным multiset (сейчас использует что-то своё)
        ))) {
            $multiset = $this->getElement(self::DEFAULT_ELEMENT);
            if ($multiset instanceof HM_Form_Element_Vue_MultiSet) {
                $multiset->prependElement($this->_getElementScoring($this->_session['questionStep1']['mode_scoring']));

                if ((in_array($this->_type, array(HM_Quest_Question_QuestionModel::TYPE_MULTIPLE)))
                    && ($this->_session['questionStep1']['mode_scoring'] == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT)) {
                    $validators = $multiset->getValidators();
                    array_push(
                        $validators,
                        array(
                            'MultisetCheckboxesChecked',
                            false,
                            array(
                                'min' => 1,
                                'messages' => array(
                                    HM_Validate_MultisetCheckboxesChecked::CHECKED_MIN_OUT => _('Вопрос должен содержать хотя бы один правильный и один неправильный ответ')
                                )
                            )
                        )
                    );
                    $multiset->setValidators($validators);
                }
            }
            elseif ($multiset instanceof HM_Form_Element_Vue_SingleChoice) {
                $singleChoice = $multiset;
                if ((in_array($this->_type, array(HM_Quest_Question_QuestionModel::TYPE_SINGLE)))
                    && ($this->_session['questionStep1']['mode_scoring'] == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT)) {
                    $validators = $singleChoice->getValidators();
                    array_push(
                        $validators,
                        array(
                            'SingleChoiceCheckboxesChecked',
                            false,
                            array(
                                'min' => 1,
                                'messages' => array(
                                    HM_Validate_MultisetCheckboxesChecked::CHECKED_MIN_OUT => _('Необходимо указать правильный вариант ответа')
                                )
                            )
                        )
                    );
                    $singleChoice->setValidators($validators);
                }
            }
        }   
    }
    
    /************** psychos **************/
    
    public function _initPsycho()
    {
        $categories = array();
        if ($collection = Zend_Registry::get('serviceContainer')->getService('QuestCategory')->fetchAll(array('quest_id = ?' => $this->_quest->quest_id), 'name')) {
            $categories = $collection->getList('category_id', 'name');
        }

        /** @var HM_Form_Element_Vue_MultiSet $multiset */
        $multiset = $this->getElement(self::DEFAULT_ELEMENT);
        
        $multiset->appendElement(new HM_Form_Element_Vue_Select('category_id', array(
            'Label' => _('Показатель'),
            'multiple' => true, // обязательно
            'multiOptions' => $categories
        )));
        
        $multiset->appendElement(new HM_Form_Element_Vue_Text('weight', array(
            'Label' => _('Вес'),
            'class' => 'brief'
        )));
    }

    
    /************** forms **************/
    
    public function _initForm()
    {
        // don nothing
    }

    protected function _addDefaultSingleChoice()
    {
        $validators = [];
        $filtersDependence = [];

        /** @see \frontend\app\src\components\forms\hm-single-choice\index.vue, method checkboxNeeded() */
        if (
            $this->_quest->type === HM_Quest_QuestModel::TYPE_TEST
            && (int)$this->_modeScoring !== HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT
        ) {
            $validators[] = ['HasCorrectAnswers'];
        }

        $this->addElement($this->getDefaultMultiSetElementName(), self::DEFAULT_ELEMENT, [
            'Required' => false,
            'dependences' => [
                new HM_Form_Element_Vue_Text(
                    'variant',
                    [
                        'Label' => _('Текст варианта'),
                    ]
                ),
            ],
            'validators' => $validators
        ]);

        $this->addDisplayGroup([
            'cancelUrl',
            self::DEFAULT_ELEMENT,
        ],
            'group',
            ['legend' => _('Варианты ответов')]
        );

        return $this->getElement(self::DEFAULT_ELEMENT);
    }

    protected function _addDefaultMultiset()
    {
        $this->addElement($this->getDefaultMultiSetElementName(), self::DEFAULT_ELEMENT, array(
            'Required' => false,
            //                    'onRowAdd' => 'function(e, $newRow) { console.log($newRow); }', // $newRow - уже созданная обёртка jQuery для новой строки, дальше с ней работаешь
            'dependences' => array(
                new HM_Form_Element_Vue_Text(
                    'variant',
                    array('Label' => _('Текст варианта'))
                ),
            ),
        ));
    
        $this->addDisplayGroup(array(
            'cancelUrl',
            self::DEFAULT_ELEMENT,
        ),
            'group',
            array('legend' => _('Варианты ответов'))
        );
    
        return $this->getElement(self::DEFAULT_ELEMENT);
    }
    
    
    // DEPRECATED??
    protected function _addMultipleScript()
    {
        Zend_Registry::get('view')->inlineScript()->captureStart();
        echo <<<E0D
            var items = '.multiset-element-category_id';
            console.log($(items));
            $(document.body).delegate(items, 'change', function () {
                console.log($(this).val());
                $(items).val($(this).val());
            });
E0D;
        Zend_Registry::get('view')->inlineScript()->captureEnd();
    }

    /**
     * В зависимости от типа вопроса раздаём разные элементы оценки
     * @return HM_Form_Element_Vue_Checkbox|HM_Form_Element_Vue_Text
     */
    protected function _getElementScoring($mode)
    {
        switch ($mode) {
            case HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT:
                $scoring = new HM_Form_Element_Vue_Checkbox(
                    'is_correct',
                    ['Label' => _('Правильный ответ')]
                );
                break;

            case HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT:
                $scoring = new HM_Form_Element_Vue_Text(
                    'weight',
                    [
                        'Label' => _('Вес'),
                        'class' => 'brief',
//                        'Filters' => ['StringTrim', 'FloatPoint'],
//                        'Validators' => ['FloatLocalized'],
//                        'Description' => _('Может быть дробным; в этом случае десятичный разделитель - точка или запятая.'),
                        'Validators' => [
                            'Int',
                            ['GreaterThan', false, [-1]],
                        ],
                        'Description' => _('Целое число, большее или равное 0.'),
                    ]
                );
                break;

            case HM_Quest_Question_QuestionModel::MODE_SCORING_OFF:
            default:
                // todo: мы ведь сюда не должны попадать?
                break;
        }

        return $scoring;
    }


    protected function _initQuestionPlaceholder()
    {
        $questionText = $this->_session['questionStep1']['question'];
        $count = preg_match_all(HM_Quest_Question_Type_PlaceholderModel::PLACEHOLDER_PATTERN, $questionText, $matches);
        $placeHolderNames = array();

        if($count > 0) {

            $pattern = array_fill(0, $count, HM_Quest_Question_Type_PlaceholderModel::PLACEHOLDER_PATTERN);

            $variantName  = self::DEFAULT_ELEMENT.'_'.'variant'.'_';
            $variantType  = self::DEFAULT_ELEMENT.'_'.'type'.'_';
            $idName       = self::DEFAULT_ELEMENT.'_'.'id'.'_';

            for($i = 1; $i <= $count; $i++) {
                $group = [];

                $placeHolderNames[] = '<input style="text-align: center;width: 203px;height: 26px;background: rgba(212, 227, 251, 0.4);border-radius: 15px;color: #4A90E2;" type="text" disabled="disabled" value="' . _('Пропуск') . ' ' . $i . '">';
                $options = array(
                    'label' => _('Вариант отображения'),
                    'class' => 'hm-question-placeholder-type',
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => HM_Quest_Question_Type_PlaceholderModel::getDisplayModes(),
                    'refresh' => [
                        'enabled' => false,
                        'description' => _('Будет изменён вариант отображения, изменения на странице не сохранятся')
                    ],
                );

                $this->addElement($this->getDefaultSelectElementName(), $group[] = $variantType . $i, $options);

                $this->addElement($this->getDefaultMultiSetElementName(), $group[] = $variantName.$i, array(
                    'Required' => false,
                    'dependences' => [
                        new HM_Form_Element_Vue_Checkbox('is_correct', ['Label' => _('Прав.')]),
                        new HM_Form_Element_Vue_Text('variant',['Label' => _('Текст варианта')])
                    ],
                ));

                $this->addElement('hidden', $idName.$i, array());

                $this->addDisplayGroup($group,
                    'group'.$i,
                    array('legend' => _('Варианты ответов: Пропуск'). ' '.$i)
                );
            }


            $questionText = preg_replace($pattern, $placeHolderNames, $questionText, 1);
            $dummyTextElementTitle = _('Текст вопроса');
            $view = $this->getView();
            $view->dummyTextElement = <<<DUMMY_ELEMENT
            <div style="background-color: #ffffff; box-shadow: 0 10px 30px rgb(209 213 223 / 50%)">
                <h4 class="headline v-card__title">{$dummyTextElementTitle}</h4>
                <hr class="v-divider theme--light">
                <div class="v-card__text" style="font-family: Roboto;font-style: normal;font-weight: normal;font-size: 18px;line-height: 24px;letter-spacing: 0.02em;color: #000000;">
                    {$questionText}
                </div>
            </div>
            </br>
DUMMY_ELEMENT;


            Zend_Registry::get('view')->inlineScript()->captureStart();
            echo "
            $(document).ready(function() {
            $('.hm-question-placeholder-type').change(function() {
                placeholderDisplayInputHideCheckboxes(this);
            });

            $('.hm-question-placeholder-type').each(function() {
                placeholderDisplayInputHideCheckboxes(this);
            });

            function placeholderDisplayInputHideCheckboxes(elem) {
                if ($(elem).val() == ".HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_INPUT.") {
                    $(elem).parents('fieldset').addClass('no-checkbox');
                } else {
                    $(elem).parents('fieldset').removeClass('no-checkbox');
                }
            }
            });";
            Zend_Registry::get('view')->inlineScript()->captureEnd();
        }
    }

    
    public function getElementDecorators($alias, $first = 'ViewHelper')
    {
        if ($alias == self::DEFAULT_ELEMENT){
            return array ( // default decorator
                array($first),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }    
}