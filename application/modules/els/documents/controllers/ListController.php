<?php

class Documents_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    private $_documentTemplateId = 0;
    private $_documentTemplate;
    /** @var HM_Document_DocumentTemplateService */
    protected $_defaultService;

    public function getDefaultService() {
        return $this->_defaultService;
    }

    public function init()
    {
        $request = $this->getRequest();
        $this->_documentTemplateId = (int)$request->getParam('document_template_id', 0);

        $this->setDefaultService($this->getService('DocumentTemplate'));

        if ($this->_documentTemplateId) {
            $this->_documentTemplate = $this->getDefaultService()->find($this->_documentTemplateId)->current();
        }

        $formParams = array();

        $formParams['cancelUrl'] = $this->view->url(array(
            'module'      => 'documents',
            'controller'  => 'list',
            'action'      => 'index',
        ), null, true);

        $formParams['document_template_id'] = $this->_documentTemplateId;

        if (isset($this->_documentTemplate)) {
            $formParams['documentTemplate'] = $this->_documentTemplate;
        }

        $form = new HM_Form_Document($formParams);
        $this->_setForm($form);

        parent::init();
    }

    public function indexAction()
    {
        $select = $this->getDefaultService()->getSelect();

        $select->from(
            array('dt' => 'documents_templates'),
            array(
                'document_template_id',
                'title',
                'document_type' => 'dt.type',
                'item_type',
                'item_id',
            )
        );

        $grid = HM_Documents_Grid_DocumentsGrid::create();
        $this->view->assign(array(
            'grid' => $grid->init($select)
        ));
    }

    public function create(Zend_Form $form)
    {
        $data = $form->getValues();
        unset($data['document_template_id']);
        unset($data['variables']);

        $this->getDefaultService()->insert($data);
    }

    public function update(Zend_Form $form)
    {
        $data = $form->getValues();
        unset($data['variables']);

        $this->getDefaultService()->update($data);
    }

    public function delete($id)
    {
        return $this->getDefaultService()->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $documentTemplate = $this->getService('DocumentTemplate')->find($this->_documentTemplateId)->current();
        $form->populate(
            $documentTemplate->getData()
        );
    }

} 