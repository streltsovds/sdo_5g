<?php
class HM_Form_TestEdit extends HM_Form_SubForm
{
    private $_event;
    private $_step1;

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('lessonStep2');
 
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($this->getParam('subject_id', 0)));

        $this->addElement('hidden', 'prevSubForm', array(
            'Required' => false,
            'Value' => 'step1'
        ));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)), null, true)
        ));

        $this->addElement('hidden', 'redirectUrl', array(
            'Required' => false
        ));

        $this->addElement('hidden', 'lesson_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $session = $this->getSession();
        $this->_step1 = $session['step1'];

        $eventId = $session['step1']['event_id'];
        if ($eventId < 0) {
            $event = $this->getService('Event')->getOne(
                $this->getService('Event')->find(-$eventId)
            );

            if ($event) {
                $eventId = $event->tool;
                $this->_event = $event;
            }
        }

        $this->initTest();

        if(count($this->getElements()) == 0) {
            return;
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Далее')));

        parent::init(); // required!
    }

    public function initTest()
    {
        $lessonId = $this->getParam('lesson_id', 0);
        
        $lesson = $this->getService('Test')->fetchAll(
          $this->getService('Test')->quoteInto(
            array('lesson_id = ?'),
            array($lessonId)
          )
        )->current();
        
        //var_dump($lesson->getValues());die;
     
        //$this->addElement('hidden', 'questions_by_theme');

        $this->addElement('ajaxRadioGroup', 'questions', array(
            'Label' => _('Способ выборки'),
            'required' => false,
            'multiOptions' => HM_Test_TestModel::getQuestionsByThemes(),
            'form' => $this,
            'dependences' => array(
                HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED =>
                   $this->getView()->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'themes', 'baseUrl' => '', 'test_id' => $lesson->test_id))
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'lim', array(
            'Label' => _('Количество вопросов из общего числа для включения в тест'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Description' => _('При нулевом значении включаются все вопросы'),
            'Value' => 0
        ));

        $this->addElement($this->getDefaultTextElementName(), 'qty', array(
            'Label' => _('Количество вопросов для одновременного отображения на странице'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultTextElementName(), 'startlimit', array(
            'Label' => _('Количество попыток слушателю на прохождение теста'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 1,
            'Description' => _('При нулевом значении количество попыток не ограничено')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'limitclean', array(
            'Label' => _('Количество дней, после которых обнуляется счетчик попыток'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
            'Description' => _('При нулевом значении счетчик никогда не обнуляется')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'timelimit', array(
            'Label' => _('Время (в минутах) на прохождение теста'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
            'Description' => _('При нулевом значении время не ограничено')
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'random', array(
            'Label' => _('Выбирать вопросы случайным образом'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));

/*        $this->addElement($this->getDefaultCheckboxElementName(), 'adaptive', array(
            'Label' => _('В случае неверного ответа включать вопрос из той же темы'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));*/

/*        $this->addElement($this->getDefaultCheckboxElementName(), 'questres', array(
            'Label' => _('Показывать страницу промежуточных результатов учащегося, по итогам последних вопросов'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int')
        ));*/

/*        $this->addElement($this->getDefaultCheckboxElementName(), 'showurl', array(
            'Label' => _('Там же показывать ссылку'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));*/

        $this->addElement($this->getDefaultCheckboxElementName(), 'endres', array(
            'Label' => _('По окончании отображать результат тестирования'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'skip', array(
            'Label' => _('Разрешить досрочное завершение теста с получением оценки'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int')
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'allow_view_log', array(
            'Label' => _('Разрешить слушателю просмотр подробного отчета'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));

/*        $this->addElement($this->getDefaultWysiwygElementName(), 'comments', array(
            'Label' => _('Комментарий к заданию'),
            'required' => false,
            'validators' => array(),
            'filters' => array()
        ));*/


        $this->addDisplayGroup(array(
                'lim',
                'questions',
                'random',
//                'adaptive'
        ),
            'questionSelect',
            array('legend' => _('Выборка вопросов'))
        );

        $this->addDisplayGroup(array(
                'startlimit',
                'timelimit',
        		'limitclean',
        		'mode',
                'skip',
            ),
            'progress',
            array('legend' => _('Режим прохождения'))
        );
        $this->addDisplayGroup(array(
                'qty',
                'endres',
                'allow_view_log'
            ),
            'view',
            array('legend' => _('Режим отображения'))
        );
    }



    public function getElementDecorators($alias, $first = 'ViewHelper'){
            if (in_array($alias, array('allow_view_log', 'random', 'endres', 'skip'))) {
            return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );
        } elseif ($alias == 'module') {
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
}
