<?php
class HM_Tc_Application_Import_Template_TemplateService extends HM_Service_Import_Abstract
{
    public function fetchAll($filename = null, $where = null, $order = null, $count = null, $offset = null)
    {
        if (null !== $filename) {
            $this->getMapper()->getAdapter()->setFileName($filename);
        }
        return $this->getMapper()->fetchAll($filename, $where, $order, $count, $offset);
    }

    public function getForm()
    {
        $form = new HM_Form_Upload();
        $form->getElement('file')->setOptions(
            array(
                'Label' => _('Файл данных (zip, xlsx)'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Validators' => array(
                    array('Count', false, 1),
                    array('Extension', false, array('zip', 'xlsx'))
                ),
                'file_size_limit' => 0,
                'file_types' => array('*.zip', '*.xlsx'),
                'file_upload_limit' => 1,
                'file_sample' => Zend_Registry::get('config')->url->base . 'samples/year-planning-import-template.xlsx',
                'Required' => true
            )
        );
        $form->getElement('cancelUrl')->setValue(Zend_Registry::get('session_namespace_default')->quest['import']['returnUrl']);
        return $form;
    }
}