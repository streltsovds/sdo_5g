<?php
class HM_Form_Feedback extends HM_Form {
    protected $_tcFeedbackScaleValues;

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('feedback');

        $subjectId = (int) $this->getParam('subject_id', $this->getParam('subid', 0));

        //subject_id
        $this->addElement('hidden', 'subject_id', array(
                'Required' => false,
                'Value' => $subjectId
            )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'text_goal', array(
            'Label'    => _('Цель участия в учебном мероприятиии'),
            'Required' => false,
            'class'    => 'wide',
            'Filters'  => array('HtmlSanitize'),
        ));


        $this->addMarkElement('mark_goal', _('В какой степени программа способствовала достижению поставленной цели?'));
        $this->addMarkElement('mark_goal2', _('Насколько актуально для Вас было данное мероприятие, его тема?'));
        $this->addMarkElement('longtime', _('По продолжительности мероприятие было?'), array(
            0  => _('оптимальным'),
            -1 => _('слишком коротким'),
            1  => _('слишком длинным')));
        $this->addMarkElement('mark_usefull', _('В какой степени Вы сможете использовать полученные знания  в своей профессиональной деятельности?'));
        $this->addElement($this->getDefaultTextAreaElementName(), 'text_usefull', array(
            'Label'    => _('Что Вы будете использовать в работе?'),
            'Required' => false,
            'class'    => 'wide',
            'Filters'  => array('HtmlSanitize'),
        ));
        $this->addElement($this->getDefaultTextAreaElementName(), 'text_not_usefull', array(
            'Label'    => _('Что неприменимо в Вашей работе?'),
            'Required' => false,
            'class'    => 'wide',
            'Filters'  => array('HtmlSanitize'),
        ));
        $this->addMarkElement('mark_motivation', _('Насколько Вы мотивированы применять полученные знания и навыки?'));
        $this->addMarkElement('mark_course',     _('Оцените, пожалуйста, качество преподавания: уровень материала и его подача, погруженность тьютора в тему и т.п.?'));
        $this->addMarkElement('mark_teacher',    _('Как вы оцениваете качество контакта тьютора с аудиторией?'));
        $this->addMarkElement('mark_papers',     _('Как вы оцениваете качество раздаточного материала?'));


        $this->addMarkElement('mark_organization',  _('Насколько хорошо было организовано обучение (помещение, оборудование)?'));

        $this->addMarkElement('mark_final',  _('Учитывая все названные критерии, в какой степени Вы удовлетворены обучением в целом?'));
        $this->addMarkElement('recomend',  _('Можете ли вы рекомендовать посещение этого курса коллегам?'), array(
                1 => _('Да'),
                0 => _('Нет')));
        //Отзыв
        $this->addElement($this->getDefaultTextAreaElementName(), 'text', array(
            'Label' => _('Опишите Ваше впечатление от мероприятия и те моменты, которые, на Ваш взгляд, следует довести до сведения руководства.'),
            'Required' => false,
            'class' => 'wide',
            'Filters' => array('HtmlSanitize'),
        ));

/*
        //Оценка
        $marks = $this->getService('ScaleValue')->fetchAll('scale_id=' . HM_Scale_ScaleModel::TYPE_TC_FEEDBACK)->getList('value_id', 'value');
        $this->addElement($this->getDefaultSelectElementName(), 'mark', array(
                'Label'        => _('Оценка'),
                'Required'     => true,
                'multiOptions' => $marks,
            )
        );

        //Отзыв
        $this->addElement($this->getDefaultTextAreaElementName(), 'text', array(
            'Label' => _('Отзыв'),
            'Required' => false,
            'class' => 'wide',
            'Filters' => array('HtmlSanitize'),
        ));

*/


        $this->addDisplayGroup(array('text_goal'),
            'Feedback1',
            array('legend' => _('Цель участия в учебном мероприятии')));

        $this->addDisplayGroup(array(
                'mark_goal',
                'mark_goal2',
                'longtime',
                'mark_usefull',
                'text_usefull',
                'text_not_usefull',
                'mark_motivation',
                'mark_course',
                'mark_teacher',
                'mark_papers',
            ),
            'Feedback2',
            array('legend' => _('Оценка целесообразности и качества учебного мероприятия')));

        $this->addDisplayGroup(array('mark_organization'),
            'Feedback3',
            array('legend' => _('Оценка условий обучения')));

        $this->addDisplayGroup(array('mark_final', 'recomend'),
            'Feedback4',
            array('legend' => _('Общая оценка')));

        $this->addDisplayGroup(array('text'),
            'Feedback5',
            array('legend' => _('Комментарии')));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }


    public function addMarkElement($id, $label, $values = false)
    {
        if (!$values) {
            if (!$this->_tcFeedbackScaleValues) {
                $this->_tcFeedbackScaleValues = $this->getService('ScaleValue')->fetchAll('scale_id=' . HM_Scale_ScaleModel::TYPE_TC_FEEDBACK)->getList('value_id', 'value');
            }

            $values = $this->_tcFeedbackScaleValues;
        }

        $this->addElement($this->getDefaultRadioElementName(), $id, array(
            'Label'        => $label,
            'Required'     => true,
            'multiOptions' => $values,
            'Validators' => array(array('Int')),
            'Filters'    => array('Int'),
            'separator'  => '&nbsp;&nbsp;&nbsp;&nbsp;'));

    }
}