<?php
class HM_Form_Import extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $subject_id = $request->getParam('subject_id');
        $courseId = $request->getParam('course_id');

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('import');
/*
        if (!$subject_id) {
            $this->setAttrib('onSubmit', 'if (confirm("'._('Данная операция удалит структуру модуля (если она существует) и все материалы. Продолжить?').'")) return true; return false;');
        }
*/
        $this->addElement('hidden', 'send', array(
            'value' => 1,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

        $this->addElement('hidden', 'cid', array(
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => false
        ));

        $formats = array(0 => _('Выберите формат пакета'));
        foreach(HM_Course_CourseModel::getImportFormats() as $formatId => $formatTitle) {
            $formats[$formatId] = $formatTitle;
        }

        $this->addElement($this->getDefaultSelectElementName(), 'import_type', array(
            'Label' => _('Формат'),
            'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Filters' => array('Int'),
            'Required' => true,
            'MultiOptions' => $formats
        ));

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
            'Label' => _('Файл'),
            'Validators' => array(
                array('Count', false, 1),
                array('Extension', false, 'zip')
            ),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'file_size_limit' => 0,
            'file_types' => '*.zip',
            'file_upload_limit' => 1,
            'Required' => true
        ));

        if ($courseId) {
            $this->addElement($this->getDefaultCheckboxElementName(), 'ch_info', array(
                'Label' => _('Переписать название учебного модуля'),
                'Required' => true,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                )
            ));
        } 
        else 
        {
            $this->addElement('hidden', 'ch_info', array(
                'Value' => 1,
            ));
        }
/*
        $this->addElement($this->getDefaultCheckboxElementName(), 'test_in_knbase', array(
            'Label' => _('Импортировать тесты из учебного модуля как тесты курса'),
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));
*/


		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'send',
                'cid',
                'import_type',
                'file',
                'ch_info',
                'test_in_knbase',
                'submit'
            ),
            'importGroup',
            array('legend' => _('Импорт учебного модуля'))
        );

        parent::init(); // required!
	}

}
