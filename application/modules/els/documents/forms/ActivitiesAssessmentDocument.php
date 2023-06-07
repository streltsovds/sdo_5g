<?php
class HM_Form_ActivitiesAssessmentDocument extends HM_Form
{
    protected $_cancelUrl = '';
    /** @var HM_Document_DocumentTemplateModel */
    protected $_documentTemplate;

    public function setCancelUrl($url)
    {
        $this->_cancelUrl = $url;
    }

    public function getCancelUrl()
    {
        return $this->_cancelUrl;
    }

    public function setDocumentTemplate($document) {
        $this->_documentTemplate = $document;
    }

    public function getDocumentTemplate() {
        return $this->_documentTemplate;
    }

	public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('ActivitiesAssessmentDocument');
        
        $this->addElement('hidden', 'document_template_id', array(
            'Required' => false,
            'Value' => 0,
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'variables', array(
            'Label' => _('Доступные переменные'),
            'Required' => false,
            'Disabled' => true,
            'Value' => HM_Document_Type_StudyOrderModel::getTemplateVariablesDescription(
                HM_Document_DocumentTemplateModel::TYPE_ACTIVITIES_ASSESSMENT
            ),
        ));

        $this->addElement('hidden', 'type', array(
            'Required' => true,
            'Value' => HM_Document_DocumentTemplateModel::TYPE_ACTIVITIES_ASSESSMENT,
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    255,
                    1
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        ));


        $this->addElement($this->getDefaultWysiwygElementName(), 'content', array(
            'Label' => _('Шаблон'),
            'Required' => true,
            'Filters' => array('HtmlSanitizeRich'),
            //'toolbar' => 'hmToolbarMaxi',
            'style' => 'width:100%; height:800px',
        ));

        $this->addDisplayGroup(
        	array(
                'type',
                'title',
                'variables',
                'content',
        	),
            'mainProperties',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init(); // required!
        
    }

}