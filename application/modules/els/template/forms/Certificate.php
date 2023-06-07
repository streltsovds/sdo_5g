<?php
/**
 * Форма редактирования шаблона серификата 
 * меню "Орг.обуч. -> Настройки -> Шаблон сертификатов"
 */
class HM_Form_Certificate extends HM_Form
{
	public function init()
	{
		//настройка формы
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('certificate');
        
        $url = $this->getView()->url(array('module' => 'template', 'controller' => 'certificate', 'action' => 'preview'));
        $this->addElement('hidden',
            'previewUrl',
            array(
                'Required' => false,
                'onClick' => sprintf("window.open('{$url}')")
                //'onClick' => "$.post('{$url}', $('#certificate').serializeArray(), function (data) {var win=window.open('about:blank'); with(win.document) { open(); write(data); close(); }});"
            )
        );


		//поля формы
        $this->addElement($this->getDefaultWysiwygElementName(), 'template_certificate_text', array(
            'Label' => _('Шаблон'),
            'Description' => _('В тексте возможны следующие переменные:<br>
                <li>[CERTIFICATE] - номер сертификата;</li>
                <li>[NAME] - ФИО слушателя;</li>
                <li>[COURSE] - название курса;</li>
                <li>[GRADE] - оценка;</li>
                <li>[STUDY_PLAN] - план занятий;</li>
                <li>[DEPARTMENT] - подразделение;</li>
                <li>[STUDY_BEGIN] - дата начала обучения;</li>
                <li>[STUDY_END] - дата окончания обучения;</li>
            '),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            //'toolbar' => 'hmToolbarMidi',
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addDisplayGroup(
            array(
                'template_certificate_text',
            ),
            'orderGroup',
            array('legend' => _(''))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}
}