<?php
class HM_Quest_Question_Import_Txt_TxtService extends HM_Service_Import_Abstract
{
    public function fetchAll($filename = null, $where = null, $order = null, $count = null, $offset = null)
    {
        if (null !== $filename) {
            $this->getMapper()->getAdapter()->setFileName($filename);
        }
        return $this->getMapper()->fetchAll($filename, $where, $order, $count, $offset);
    }

    public function isTest($filename, $unlinkTempFile = true)
    {
        if (null !== $filename) {
            $this->getMapper()->getAdapter()->setFileName($filename);
        }
        return $this->getMapper()->isTest($unlinkTempFile);
    }

    public function getForm()
    {
        $form = new HM_Form_Upload();
        $form->getElement('file')->setOptions(
            array(
                'Label' => _('Файл данных (txt)'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Validators' => array(
                    array('Count', false, 1),
                    array('Extension', false, 'txt')
                ),
                'file_size_limit' => 0,
                'file_types' => '*.txt',
                'file_upload_limit' => 1,
                'file_sample' => Zend_Registry::get('config')->url->base . 'samples/questions.txt',
                'Required' => true
            )
        );
        $form->getElement('cancelUrl')->setValue(Zend_Registry::get('session_namespace_default')->question['import']['returnUrl']);
        return $form;
    }
}