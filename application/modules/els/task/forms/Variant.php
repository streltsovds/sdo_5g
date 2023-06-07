<?php
class HM_Form_Variant extends HM_Form
{

    public function setDefaults(array $defaults)
    {
        parent::setDefaults($defaults);

        $populatedFiles = $this->getService('TaskVariant')->getPopulatedFiles($defaults['variant_id']);

        $files = $this->getElement('files');
        $files->setValue($populatedFiles);

        return $this;
    }

	public function init()
	{
        $subjectId = (int) $this->getParam('subject_id', 0);
        $taskId = (int) $this->getParam('task_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('task-variant');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('kod'=>null, 'action' => 'task', 'controller' => 'list', 'module' => 'variant', 'subject_id' => $subjectId, 'task_id' => $taskId))
        ));

        $this->addElement('hidden', 'variant_id', array(
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Required' => false,
            'Label' => _('Название'),
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Формулировка варианта задания'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 1)
            ),
            'Filters' => array('HtmlSanitizeRich'),
            //'toolbar' => 'hmToolbarMidi',
            'rows' => 3,
        ));

        $this->addElement($this->getDefaultFileElementName(), 'files',
            array(
                 'Label'      => _('Файлы'),
                 'Required'   => false,
                 'Filters'    => array('StripTags'),
                 'Destination' => Zend_Registry::get('config')->path->upload->tmp,//->tasks
                 'file_size_limit' => 0,
                 'file_upload_limit' => 10,
//                 'preview_url'=> '/upload/files/form_study_plan.docx'//???//Сделать когда будет реализовано множественное отображение в элементе
            )
        );

        $fields = array(
            'cancelUrl',
            'name',
            'description',
            'files'
        );

        $this->addDisplayGroup(
            $fields,
            'mainGroup',
            array('legend' => _('Вариант'))
        );

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'task',
                'controller' => 'variant',
                'action' => 'list',
                'task_id' => $taskId,
                'subject_id' => $subjectId ? : null,
                'variant_id' => null,
                'gridmod' => null,
            ))
        ));


        parent::init(); // required!
	}

}