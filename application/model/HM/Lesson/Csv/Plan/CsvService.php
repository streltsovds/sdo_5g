<?php
class HM_Lesson_Csv_Plan_CsvService extends HM_Service_Import_Abstract
{
    public function fetchAll($filename = null, $where = null, $order = null, $count = null, $offset = null)
    {
        if (null !== $filename) {
            $this->getMapper()->getAdapter()->setFileName($filename);
        }
        return $this->getMapper()->fetchAll($filename, $where, $order, $count, $offset);
    }

    public function getForm($subjectId)
    {
        $form = new HM_Form_Upload();
        $form->getElement('file')->setOptions([
            'Label' => _('Файл данных (csv)'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Validators' => array(
                array('Count', false, 1),
                array('Extension', false, 'csv')
            ),
            'Required' => true,
            'file_size_limit' => 0,
            'file_types' => '*.csv',
            'file_upload_limit' => 1,
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/plan.csv',
        ]);
        $form->getElement('cancelUrl')->setOptions([
            'Required' => false,
            'Value' => $form->getView()->url(
                array(
                    'module' => 'subject',
                    'controller' => 'lesson',
                    'action' => 'edit',
                    'subject_id' => $subjectId
                )
            )
        ]);
        return $form;
    }
}