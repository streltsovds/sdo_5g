<?php
class HM_Form_Document extends HM_Form
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
        
        $this->setName('tcproviderdocuments');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getCancelUrl()
        ));
        
        $this->addElement('hidden', 'document_template_id', array(
            'Required' => false,
            'Value' => $this->getParam('document_template_id'),
        ));

        $documentTemplate = $this->getDocumentTemplate();
        $type = isset($documentTemplate) ? $documentTemplate->type : 0;

        $this->addElement($this->getDefaultTextAreaElementName(), 'variables', array(
            'Label' => _('Доступные переменные'),
            'Required' => false,
            'Disabled' => true,
            'Value' => HM_Document_Type_StudyOrderModel::getTemplateVariablesDescription($type),
        ));

        $url = $this->getView()->url(array(
            'module' => 'documents',
            'controller' => 'ajax',
            'action' => 'get-document-variables',
            'type' => ''
        ), null, false);
        $onChange = '$("#variables").val("Loading...");$.get("'.$url.'"+$("#type").val(), function(data){$("#variables").val(data.variables)});';

        $this->addElement($this->getDefaultSelectElementName(), 'type', array(
            'Label' => _('Тип документа'),
            'Required' => true,
            'MultiOptions' => HM_Document_DocumentTemplateModel::getTypes(),
            'class' => 'wide',
            'onChange' => $onChange,
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'item_type', array(
            'Label' => _('Привязка к сущности с типом'),
            'Required' => false,
            'MultiOptions' => HM_Document_DocumentTemplateModel::getItemTypes(),
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'item_id', array(
            'Label' => _('Id сущности'),
            'Required' => false,
            'MultiOptions' => HM_Document_DocumentTemplateModel::getItemTypes(),
            'class' => 'wide',
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


        //Отступы
        $this->addElement($this->getDefaultTextElementName(), 'margin_top', array(
            'Label' => _('Верхний'),
            'Required' => false,
            'Validators' => array('int'),
            'Value' => HM_Document_DocumentTemplateModel::MARGIN_TOP,
        ));

        $this->addElement($this->getDefaultTextElementName(), 'margin_right', array(
            'Label' => _('Правый'),
            'Required' => false,
            'Validators' => array('int'),
            'Value' => HM_Document_DocumentTemplateModel::MARGIN_RIGHT,
        ));

        $this->addElement($this->getDefaultTextElementName(), 'margin_bottom', array(
            'Label' => _('Нижний'),
            'Required' => false,
            'Validators' => array('int'),
            'Value' => HM_Document_DocumentTemplateModel::MARGIN_BOTTOM,
        ));

        $this->addElement($this->getDefaultTextElementName(), 'margin_left', array(
            'Label' => _('Левый'),
            'Required' => false,
            'Validators' => array('int'),
            'Value' => HM_Document_DocumentTemplateModel::MARGIN_LEFT,
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
                'cancelUrl',
                'type',
                'title',
                'variables',
                'content',
        	),
            'mainProperties',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(
            array(
                'margin_top',
                'margin_right',
                'margin_bottom',
                'margin_left',
            ),
            'margins',
            array('legend' => _('Отступы (pt)'))
        );

        $this->addDisplayGroup(
            array(
                'item_type',
                'item_id',
            ),
            'itemProperties',
            array('legend' => _('Привязка к сущности'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init(); // required!
        
    }

}