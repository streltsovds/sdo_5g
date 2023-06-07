<?php
class HM_Form_Programm extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('programm');

        $programId = $this->getParam('programm_id', 0);

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'programm', 'controller' => 'list', 'action' => 'index'), null, true)
        ));

        $this->addElement('hidden', 'programm_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'programm_type', array(
            'value' => HM_Programm_ProgrammModel::TYPE_ELEARNING,
        ));

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $icon = '';

        $program = $this->getService('Programm')->getById($programId);
        if (!empty($program)) {
            $icon = $program->getUserIcon();
        }

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
                'Label' => _('Загрузить иконку из файла'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Description' => _('Для использования в виджете "Витрина учебных курсов"'),
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
                'file_upload_limit' => 1,
                'subject' => null,
                'crop' => [
                    'ratio' => HM_Subject_SubjectModel::THUMB_WIDTH / HM_Subject_SubjectModel::THUMB_HEIGHT
                ],
                'preview_url' => $icon,
//            'delete_button' => true
            )
        );

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
                'Label' => _('Описание'),
                'Required' => false,
                'class' => 'wide',
            )
        );

        $fields = array(
            'cancelUrl',
            'programm_id',
            'name',
            'icon',
            'description',
        );

        $this->addDisplayGroup(
            $fields,
            'programmGroup',
            array('legend' => _('Учебная программа'))
        );

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}