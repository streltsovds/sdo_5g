<?php
class HM_Form_QuestStep2 extends HM_Form_SubForm {

    protected $_session;
    
    public function init() 
    {
        $quest = null;
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('questStep2');
        $this->_session = $this->getSession();
        
        if ($subjectId = $this->getParam('subject_id', 0)) {
            $cancelUrl = array('controller' => 'subject', 'action' => 'list', 'subject_id' => $subjectId, 'quest_id' => null, 'gridmod' => null, 'subForm' => null);
        } elseif ($questId = $this->getParam('quest_id', 0)) {
            $cancelUrl = array('controller' => 'index', 'action' => 'card', 'quest_id' => $questId);
        } else {
            $cancelUrl = array('controller' => 'list', 'action' => 'index', 'quest_id' => null);
        }

        if ($questId = $this->getParam('quest_id', 0)) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
        }

        $steps = [
            _('Отображение вопросов')     => ['group_display'],
            _('Дополнительные настройки') => ['group_test'],
            _('Классификация') => ['classifiers'],
        ];


        $this->addElement($this->getDefaultStepperElementName(), 'stepper', [
            "steps" => $steps,
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
        
        $this->addElement('RadioGroup', 'mode_display', array(
            'Label' => _('Разбиение на страницы'),
            'MultiOptions' => HM_Quest_QuestModel::getDisplayModes(),
            'separator' => '',
            'form' => $this,
            'dependences' => array(
                HM_Quest_QuestModel::MODE_DISPLAY_BY_CLUSTERS => array(),
                HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS => array('mode_display_questions'),
                HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS => array('mode_display_clusters'),
            )
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'mode_display_questions', array(
            'Label' => _('Количество вопросов, отображаемых на одной странице'),
            'Description' => _('В этом случае количество страниц будет зависеть от общего количества вопросов и количества вопросов, отображаемых на одной странице.'),
            'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Required'    => true,
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'mode_display_clusters', array(
            'Label'       => _('Количество страниц'),
            'Description' => _('В этом случае количество вопросов, отображаемых на одной странице будет зависеть от общего количества вопросов и количества страниц.'),
            'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Required'    => true,
        ));


            $type = ($quest) ? $quest->type : $this->getParam('type', 'test');
            $classifierLinkType = $this->getService('Quest')->getClassifierLinkType($type);
            $classifierElements = $this->addClassifierElements($classifierLinkType, $this->getParam('quest_id', 0));
            $this->addClassifierDisplayGroup($classifierElements, _('Классификация'));


        $this->addDisplayGroup(array(
            'mode_display',
            'mode_display_questions',
            'mode_display_clusters'
        ),
            'group_display',
            array('legend' => _('Отображение вопросов'))
        );

        if ($type = $quest ? $quest->type : $this->_session['questStep1']['type']) {
            $method = sprintf('init%s', ucfirst($type));
            if (method_exists($this, $method)) $this->$method(); 
        }        
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!        
    }
    
    /************** tests **************/

    public function initTest()
    {
        $questId = $this->getParam('quest_id', 0);

        $clusters      = array();
        $clusterIds    = array();
        if ($questId) {
            $clusters = $this->getService('QuestCluster')->fetchAll(array('quest_id=?' => $questId))->getList('cluster_id', 'name');
            foreach ($clusters as $clusterId => $clusterName) {
                $clusterIds[] = 'cluster_limit_'. $clusterId;
            }
        }


        $this->addElement('RadioGroup', 'mode_selection', array(
            'Label' => _('Выборка вопросов'),
            'MultiOptions' => HM_Quest_QuestModel::getSelectionModes(),
            'separator' => '',
            'form' => $this,
            'dependences' => array(
                HM_Quest_QuestModel::MODE_SELECTION_ALL              => array('mode_selection_all_shuffle'),
                HM_Quest_QuestModel::MODE_SELECTION_LIMIT            => array('mode_selection_questions'),
                HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER => array('mode_selection_questions_cluster'),
                HM_Quest_QuestModel::MODE_SELECTION_LIMIT_CLUSTER    => $clusterIds,
            ),
            'Filters'  => array('Int'),
        ));

        foreach ($clusters as $clusterId => $clusterName) {
            $this->addElement($this->getDefaultTextElementName(), 'cluster_limit_'. $clusterId, array(
                'Label'       => $clusterName,
                'Description' => _('Чтобы выбрать все вопросы данного блока, оставьте поле пустым.'),
                'Required'    => false,
                'Validators'  => array('Int', array('GreaterThan', false, array('min' => -1))),
            ));
        }

        $this->addElement($this->getDefaultTextElementName(), 'mode_selection_questions', array(
            'Label'       => _('Количество вопросов, выбранных случайным образом'),
            'Description' => _('Если общее количество вопросов в тесте меньше, чем данный параметр, в таком случае будут выбраны все имеющиеся вопросы.'),
            'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Required'    => true
        ));
        $this->addElement($this->getDefaultTextElementName(), 'mode_selection_questions_cluster', array(
            'Label'       => _('Количество вопросов, выбранных случайным образом'),
            'Description' => _('Если общее количество вопросов в каком-то блоке вопросов меньше, чем данный параметр, в таком случае будут выбраны все имеющиеся вопросы.'),
            'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Required'    => true
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'mode_selection_all_shuffle', array(
            'Label' => _('Перемешивать вопросы'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'limit_attempts', array(
            'Label'       => _('Ограничение по количеству попыток'),
            'Description' => _('Чтобы убрать ограничение по попыткам, оставьте поле пустым.'),
            'Required'    => false,
            'Value'       => '',
            'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'limit_time', array(
            'Label'       => _('Ограничение по времени выполнения, мин.'),
            'Description' => _('Чтобы убрать ограничение по времени, оставьте поле пустым.'),
            'Required'    => false,
            'Value'       => '',
            'Validators'  => array('Int', array('GreaterThan', false, array('min' => 0))),
        ));


        $this->addElement('RadioGroup', 'mode_test_page', array(
            'Label' => _('Переключение между страницами теста'),
            'MultiOptions' => HM_Quest_QuestModel::getPageModes(),
            'separator'    => '',
            'form'         => $this,
            'Required'    => true,
            'Filters'  => array('Int'),
        ));



        $this->addElement($this->getDefaultCheckboxElementName(), 'show_log', array(
            'Label' => _('Разрешить просмотр подробного отчёта пользователем'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'show_result', array(
            'Label' => _('По окончании отображать результат тестирования'),
            'Value' => true
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'mode_self_test', array(
            'Label' => _('Режим самопроверки'),
        ));

        $this->addDisplayGroup(array_merge(array(
                'mode_selection',
                'mode_selection_all_shuffle',
                'mode_selection_questions',
                'mode_selection_questions_cluster',
                'limit_attempts',
                'limit_time',
                'mode_test_page',
                'show_result',
                'show_log',
                'mode_self_test'
            ), $clusterIds),
            'group_test',
            array('legend' => _('Настройки теста'))
        );
    }

    public function initPoll()
    {
        $quest = null;
        if ($questId = $this->getParam('quest_id', 0)) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
        }


        if ($quest) {
            if ($quest->scale_id) {
                $this->addElement('hidden',
                    'poll_mode',
                    array(
                        'Required' => false,
                        'Value' => HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE
                    )
                );

                $this->addElement($this->getDefaultSelectElementName(), 'scale_id', array(
                        'Required' => true,
                        'Label' => _('Шкала оценивания'),
                        'multiOptions' => $this->getService('Scale')->fetchAll(array('mode=?' => HM_Scale_ScaleModel::MODE_FEEDBACK), 'scale_id')->getList('scale_id', 'name'),
                        'Validators' => array('Int'),
                        'Filters' => array('Int')
                    )
                );
            }
            else {
                $this->addElement('hidden',
                    'poll_mode',
                    array(
                        'Required' => false,
                        'Value' => HM_Quest_Type_PollModel::QUESTIONS_TYPE_MANUAL
                    )
                );
                $this->addElement('hidden',
                    'scale_id',
                    array(
                        'Required' => false,
                        'Value' => 0
                    )
                );
            }

        } else {
            $this->addElement('RadioGroup', 'poll_mode', array(
                'Label' => _('Оценивание вопросов'),
                'MultiOptions' => HM_Quest_Type_PollModel::getPollQuestionsTypes(),
                'separator' => '',
                'form' => $this,
                'dependences' => array(
                    HM_Quest_Type_PollModel::QUESTIONS_TYPE_MANUAL => array(),
                    HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE => array('scale_id'),
                )
            ));
            $this->addElement($this->getDefaultSelectElementName(), 'scale_id', array(
                    'Label' => _('Шкала оценивания'),
                    'multiOptions' => $this->getService('Scale')->fetchAll(array('mode=?' => HM_Scale_ScaleModel::MODE_FEEDBACK), 'scale_id')->getList('scale_id', 'name', ('Выберите шкалу')),
                    'Validators' => array('Int'),
                    'Filters' => array('Int')
                )
            );
        }


        $this->addElement($this->getDefaultCheckboxElementName(), 'displaycomment', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Возможность оставить комментарий')));




        $this->addDisplayGroup(array(
                'poll_mode',
                'scale_id',
                'displaycomment',
                'scale_id',
            ),
            'group_test',
            array('legend' => _('Настройки опроса'))
        );
    }

    public function initPsycho()
    {
        $this->addElement($this->getDefaultTextElementName(), 'limit_time', array(
            'Label' => _('Ограничение по времени выполнения, мин.'),
            'Required' => false,
            'Value' => '',
            'Filters' => array('Int'),
        )); 
        
        $this->addDisplayGroup(array(
            'limit_time',
        ),
            'group_test',
            array('legend' => _('Настройки психологического опроса'))
        );        
    }
    
    
    public function getElementDecorators($alias, $first = 'ViewHelper')
    {
        if ($alias == 'variants'){
            return array ( // default decorator
                array($first),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }    
}