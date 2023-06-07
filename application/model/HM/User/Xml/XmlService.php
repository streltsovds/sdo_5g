<?php
class HM_User_Xml_XmlService extends HM_Service_Import_Abstract
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
                'Label' => _('Файл данных (xml)'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Validators' => array(
                    array('Count', false, 1),
                    array('Extension', false, 'xml')
                ),
                'Required' => true,
                'file_size_limit' => 0,
                'file_types' => '*.xml',
                'file_upload_limit' => 1,
                'file_sample' => Zend_Registry::get('config')->url->base . 'samples/people.xml',
            )
        );
        $form->getElement('cancelUrl')->setOptions(array(
                'Required' => false,
                'Value' => $form->getView()->url(
                        array(
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'index'
                        )
                    )
            )
        );
        return $form;
    }
}