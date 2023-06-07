<?php

class HM_Form extends ZendX_JQuery_Form {

    private $_serviceContainer = null;
    private $_wysiwygElementName = 'vue_TinyMce';
    private $_fileElementName = 'vue_File';
    private $_serverFileElementName = 'vue_ServerFile';
    private $_imageElementName = 'vue_Image';
    private $_textElementName = 'Vue_Text';
    private $_textAreaElementName = 'vue_TextArea';
    private $_checkboxElementName = 'vue_Checkbox';
    private $_multiCheckboxElementName = 'vue_MultiCheckbox';
    private $_selectElementName = 'vue_Select';
    private $_multiSelectElementName = 'vue_MultiSelect';
    private $_multiSetElementName = 'vue_MultiSet';
    private $_singleChoiceElementName = 'vue_SingleChoice';
    private $_datePickerElementName = 'vue_DatePicker';
    private $_treeSelectElementName = 'vue_TreeSelect';
    private $_submitElementName = 'vue_Submit';
    private $_submitLinkElementName = 'vue_SubmitLink';
    private $_multiTextElementName = 'vue_MultiText';
    private $_radioElementName = 'vue_Radio';
    private $_tagsElementName = 'vue_Tags';
    private $_passwordCheckboxElementName = 'vue_PasswordCheckbox';
    private $_timeSliderElementName = 'vue_TimeSlider';
    private $_timePickerElementName = 'vue_TimePicker';
    private $_sliderElementName = 'vue_Slider';
    private $_searchMaterialElementName = 'vue_SearchMaterial';
    private $_tabsElementName = 'vue_Tabs';
    private $_stepperElementName = 'vue_Stepper';
    private $_iframeElementName = 'vue_Iframe';
    private $_materialList = 'vue_MaterialList';
    private $_isAjaxRequest = null;
    private $__isValid = true;
    private $_elementsWithPrefix = array();

    protected $_modifiers = array();

    protected $_classifierElements = false;

    public function __construct($options = null) {
        $this->addPrefixPath('HM_Form_Element', 'HM/Form/Element/', 'element');
        $this->addPrefixPath('HM_Form_Decorator', 'HM/Form/Decorator/', 'decorator');
        $this->addElementPrefixPath('HM_Validate', 'HM/Validate', 'validate');
        $this->addElementPrefixPath('HM_Validate_File', 'HM/Validate/File', 'validate');
        $this->addElementPrefixPath('HM_Filter', 'HM/Filter', 'filter');
//        $this->_wysiwygElementName = Zend_Registry::get('config')->wysiwyg->editor;
//        $this->_fileElementName = Zend_Registry::get('config')->form->file->uploader;
        parent::__construct($options);
    }

    public function init() {

        foreach($this->getElements() as $element) {
            if (!$element->isArray()) $element->addFilter('StringTrim');
            if (!$element->loadDefaultDecoratorsIsDisabled()) {
                if (!in_array($element->getType(),
                    array(
                        /*'Zend_Form_Element_Captcha'*/
                    )
                )) {
                    if ($element->getType() == 'Zend_Form_Element_Hidden') {
                        $element->setDecorators($this->getHiddenElementDecorators($element->getName()));
                    }elseif(in_array($element->getType(), array(
                        'HM_Form_Element_Lists'
                    ))) {
                        $element->setDecorators($this->getCustomElementDecorators($element->getName()));
                    }elseif(in_array($element->getType(), array('Zend_Form_Element_File'))) {
                        $element->setDecorators($this->getFileElementDecorators($element->getName()));
                    }elseif(in_array($element->getType(),
                        array(
                            'Zend_Form_Element_Submit',
                            'Zend_Form_Element_Button',
                            'HM_Form_Element_SubmitCancel'
                        ))) {
                        $element->setDecorators($this->getButtonElementDecorators($element->getName()));
                    } elseif ($element instanceof Zend_Form_Element_Checkbox) {
                        $element->setDecorators($this->getCheckBoxDecorators($element->getName()));
                    } elseif ($element instanceof HM_Form_Element_TreeCheckbox) {
                        $element->setDecorators($this->getTreeCheckBoxDecorators($element->getName()));
                    } elseif ($element instanceof Zend_Form_Element_Select) {
                        $element->setDecorators($this->getSelectDecorators($element->getName()));
                    } elseif ($element instanceof HM_Form_Element_FcbkComplete) {

                    } elseif ($element instanceof Zend_Form_Element_Text) {
                        $element->setDecorators($this->getTextDecorators($element->getName()));
                    } elseif ($element instanceof Zend_Form_Element_Password) {
                        $element->setDecorators($this->getPasswordDecorators($element->getName()));
//                    } elseif ($element instanceof Zend_Form_Element_Radio) {
//                        $element->setDecorators($this->getRadioDecorators($element->getName()));
                    } elseif ($element instanceof HM_Form_Element_Vue_File
                        || $element instanceof HM_Form_Element_Vue_Select
                        || $element instanceof HM_Form_Element_RadioGroup
                        || $element instanceof HM_Form_Element_Vue_Radio
                        || $element instanceof HM_Form_Element_Vue_Element
                    ) {
                        $element->setDecorators($this->getVueElementsDecorators($element->getName()));
                    } else {
                        if ($element instanceof ZendX_JQuery_Form_Element_UiWidget) {
                            $element->setDecorators($this->getElementDecorators($element->getName(), 'UiWidgetElement'));
                        } else {
                            $element->setDecorators($this->getElementDecorators($element->getName()));
                        }
                    }
                }
            }
        }

        $this->setDisplayGroupDecorators(
            array(
                'FormElements',
//                array('HtmlTag', array('tag' => 'dl')),
                'Fieldset',
                //'DtDdWrapper'
            )
        );
        /*
        $this->setDisplayGroupDecorators(array(
             'FormElements',
             'Legend',
             array('HtmlTag', array('tag' => 'table', 'border' => 0, 'width' => '100%', 'class' => 'main')),
             array(array('br' => 'HtmlTag'), array('tag' => 'br', 'placement' => 'append'))
        ));
         *
         */

        $displayGroups = $this->getDisplayGroups();
        if (count($displayGroups)>1) {
            //$this->getView()->headScript()->appendFile($this->getView()->serverUrl('/js/content-modules/fieldset.js'));
        }
        //$this->getView()->headLink()->appendStylesheet( $this->getView()->serverUrl('/css/content-modules/forms.css') );

        // Эти штуки добавляются в гриды и портят там всё
        // $this->getView()->headScript()->prependFile('/frontend/libs/materializecss/materialize.min.js');
        // $this->getView()->headLink()->prependStylesheet('/frontend/libs/materializecss/materialize.css');


        $translator = new Zend_Translate('array', APPLICATION_PATH.'/system/errors.php');
        $this->setTranslator($translator);
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();

        if (empty($decorators)) {

            $this->addDecorator('FormElements')
                 //->addDecorator('HtmlTag', array(/*'tag' => 'dl',*/ 'class' => 'v-card'))
                 ->addDecorator('Form');
        }
    }

    public function getRadioDecorators($alias, $first = 'ViewHelper')
    {
        $w = 1;
        return array (
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND /*'separator' => '&nbsp;'*/)),
            //array(array('data' => 'HtmlTag'), array('tag' => 'p')),
//            array('Materialize_Radio')
        );

    }

    public function getCheckBoxDecorators($alias, $first = 'ViewHelper')
    {
        return array (
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND /*'separator' => '&nbsp;'*/)),
            //array(array('data' => 'HtmlTag'), array('tag' => 'p')),
            array('Materialize_Checkbox')
        );

    }

    public function getTreeCheckBoxDecorators($alias, $first = 'ViewHelper')
    {
        return array (
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND /*'separator' => '&nbsp;'*/)),
            //array(array('data' => 'HtmlTag'), array('tag' => 'p')),
            array('Materialize_TreeCheckbox')
        );

    }

    public function getVueElementsDecorators()
    {
        return array (
            array('decorator' => 'VueViewHelper', 'options' => array('formName' => $this->getName()))
        );

    }

    public function getPasswordDecorators($alias, $first = 'ViewHelper') {
        $decorators = array ( // default decorator
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            //array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
            array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND)),
            array('Materialize_Password')
        );

        return $decorators;
    }

    public function getTextDecorators($alias, $first = 'ViewHelper') {
        $decorators = array ( // default decorator
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            //array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
            array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND)),
            array('Materialize_Text')
        );

        return $decorators;
    }

    public function getSelectDecorators($alias, $first = 'ViewHelper') {
        $decorators = array ( // default decorator
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            //array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
            array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND)),
            array('Materialize_Select')
        );

        return $decorators;
    }

    public function getElementDecorators($alias, $first = 'ViewHelper') {
    $decorators = array ( // default decorator
        array($first),
        array('RedErrors'),
        //array('Description', array('tag' => 'p', 'class' => 'description')),
        //array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
        array('Label', array('placement' => Zend_Form_Decorator_Abstract::PREPEND))

    );

    return $decorators;
    /*
    return array ( // default decorator
            array($first),
            array('RedErrors'),
            array('Description', array('tag' => 'p', 'class' => 'description')),
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );
     *
     */
}

    public function getHiddenElementDecorators($alias, $first = 'ViewHelper') {
        return array ( // default decorator
                array($first)
        );
    }

    public function getFileElementDecorators($alias, $first = 'ViewHelper') {
        return $this->getElementDecorators($alias, 'File');
    }

    public function getButtonElementDecorators($alias, $first = 'ViewHelper') {
        $decorators = array($first);

        if (null != $this->getElement('prevSubForm')) {
            $decorators[] = array(array('prev' => 'Button'), array('placement' => 'prepend', 'label' => _('Назад'), 'url' => $this->getView()->url(array('subForm' => $this->getElement('prevSubForm')->getValue()))));
        }

        if (null != $this->getElement('cancelUrl')) {
            $decorators[] = array(array('cancel' => 'Button'), array('placement' => 'append', 'label' => _('Отмена')/*, 'url' => $this->getElement('cancelUrl')->getValue()*/));
        }

        if (null != $this->getElement('previewUrl')) {
            $decorators[] = array(array('preview' => 'Button'), array(
                'placement' => 'append', 
                'label' => _('Предварительный просмотр'), 
                'onClick' => $this->getElement('previewUrl')->getAttrib('onClick'),
            ));
        }

        // урл для сброса настроек дизайна
        if (null != $this->getElement('resetUrl')) {
            $decorators[] = array(array('cancel' => 'Button'), array(
                'placement' => 'append',
                'label' => _('Сбросить'),
                'onClick' => sprintf("if ($('#resetUrl')) {
                                            window.location.href = $('#resetUrl').val();
                                         }  return false;")
            ));
        }

        $decorators = array_merge($decorators, array(
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'v-flex'))
        /*
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element', 'colspan' => 2, 'align' => 'right')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
            */
        ));
        return $decorators;
    }

    public function getCustomElementDecorators($alias, $first = 'ViewHelper') {
        $decorators = array(
            array(array('data' => 'HtmlTag'), array('tag' => 'dd'))
        /*
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element', 'colspan' => 2, 'align' => 'right')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
            */
        );
        return $decorators;
    }

    public function getMessagesUtf8()
    {
        $messages = $this->getMessages();
        if (is_array($messages) && count($messages)) {
            foreach($messages as &$errors) {
                if (is_array($errors) && count($errors)) {
                    foreach($errors as &$error) {
                        $error = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $error);
                    }
                }
            }
        }
        return $messages;
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function isAjaxRequest()
    {
        if (null === $this->_isAjaxRequest) {
            if ($this->getRequest()->isXmlHttpRequest()
                || $this->getRequest()->getParam('ajax', false)
                || ($this->getRequest()->getParam('gridmod') == 'ajax')) {

                $this->_isAjaxRequest = true;
            } else {
                $this->_isAjaxRequest = false;
            }
        }
        return $this->_isAjaxRequest;
    }

    public function getValue($name, $cp1251 = null)
    {
        $value = parent::getValue($name);
        if ($this->isAjaxRequest() && !$cp1251) {
            if (is_string($value)) {
                $value = iconv("UTF-8", Zend_Registry::get('config')->charset, $value);
            }

            // todo: массив
        }
        return $value;
    }

    /*
     *  @param $model - модель, которую обновляем из данных формы
     *  чтобы не пролезли атрибуты, отсутствующие в модели
     */
    public function getValues($suppressArrayNotation = false, HM_Model_Abstract $model = null)
    {
        $values = parent::getValues($suppressArrayNotation);
        unset($values['submit']);
        unset($values['cancelUrl']);

        if ($model) {
            // array_filter только с 5.6 .(
            $onlyPropertyValues = array();
            $properties = array_keys($model->getData());
            foreach ($values as $key => $value) {
                if (in_array($key, $properties)) {
                    $onlyPropertyValues[$key] = $value;
                }
            }
            $values = $onlyPropertyValues;
        }

        if ($this->isAjaxRequest()) {
            foreach($values as $key => &$value) {
                if (is_string($value)) {
                    $value = iconv("UTF-8", Zend_Registry::get('config')->charset, $value);
                }
            }
        }

        return $values;
    }

    public function getParam($name, $default = false) {
        return $this->getRequest()->getParam($name, $default);
    }

    public function setServiceContainer($container)
    {
        $this->_serviceContainer = $container;
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        if (null == $this->_serviceContainer) {
            $this->_serviceContainer = Zend_Registry::get('serviceContainer');
        }
        return $this->_serviceContainer->getService($name);
    }

    public function getDefaultWysiwygElementName()
    {
        return $this->_wysiwygElementName;
    }

    public function getDefaultFileElementName()
    {
        return $this->_fileElementName;
    }

    public function getDefaultServerFileElementName()
    {
        return $this->_serverFileElementName;
    }


    public function getDefaultImageElementName()
    {
        return $this->_imageElementName;
    }

    public function getDefaultTextElementName()
    {
        return $this->_textElementName;
    }

    public function getDefaultTextAreaElementName()
    {
        return $this->_textAreaElementName;
    }

    public function getDefaultCheckboxElementName()
    {
        return $this->_checkboxElementName;
    }

    public function getDefaultMultiCheckboxElementName()
    {
        return $this->_multiCheckboxElementName;
    }

    public function getDefaultSelectElementName()
    {
        return $this->_selectElementName;
    }

    public function getDefaultMultiSelectElementName()
    {
        return $this->_multiSelectElementName;
    }

    public function getDefaultDatePickerElementName()
    {
        return $this->_datePickerElementName;
    }

    public function getDefaultTreeSelectElementName()
    {
        return $this->_treeSelectElementName;
    }

    public function getDefaultSubmitElementName()
    {
        return $this->_submitElementName;
    }

    public function getDefaultSubmitLinkElementName()
    {
        return $this->_submitLinkElementName;
    }

    public function getDefaultMultiTextElementName()
    {
        return $this->_multiTextElementName;
    }

    public function getDefaultRadioElementName()
    {
        return $this->_radioElementName;
    }

    public function getDefaultTagsElementName()
    {
        return $this->_tagsElementName;
    }

    public function getDefaultPasswordCheckboxElementName()
    {
        return $this->_passwordCheckboxElementName;
    }

    public function getDefaultSingeChoiceElementName()
    {
        return $this->_singleChoiceElementName;
    }

    public function getDefaultMultiSetElementName()
    {
        return $this->_multiSetElementName;
    }

    public function getDefaultMaterialListElementName()
    {
        return $this->_materialList;
    }

    public function getDefaultTimeSliderElementName()
    {
        return $this->_timeSliderElementName;
    }

    public function getDefaultTimePickerElementName()
    {
        return $this->_timePickerElementName;
    }

    public function getDefaultSliderElementName()
    {
        return $this->_sliderElementName;
    }

    public function searchMaterialElementName()
    {
        return $this->_searchMaterialElementName;
    }

    public function getDefaultTabsElementName()
    {
        return $this->_tabsElementName;
    }

    public function getDefaultStepperElementName()
    {
        return $this->_stepperElementName;
    }

    public function getDefaultIframeElementName()
    {
        return $this->_iframeElementName;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $result = parent::render($view);
        if (!$this->__isValid) {
//            $result .= $this->getView()->Notifications(array(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Внимание! Не все поля заполнены корректно!'))), array('html' => true));
        }
        return $result;
    }

    public function isValid($data)
    {
        $result = parent::isValid($data);
        if (!$result)
        {
            $this->__isValid = false;
        }
        return $result;
    }

    public function addClassifierElements($linkType, $itemId = 0, $current = '', $classifiers_types = array())
    {
        $ret = array();
        $classifierTypes = $this->getService('ClassifierType')
            ->getClassifierTypes($this->checkLinkType($linkType), $classifiers_types);

            foreach($classifierTypes as $type) {
                if ($type->type_id == HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS) continue;
                $name = 'classifier_'.$type->type_id;
                $this->addElement($this->getDefaultMultiSelectElementName(), $name,
                    array(
                        'Label' => $type->name,
                        'Required' => false,
                        'Filters' => array(
                            'Int'
                        ),
                        'idName' => 'classifier_id',
                        'remoteUrl' => $this->getView()->url(
                            array(
                                'baseUrl' => '',
                                'module' => 'classifier',
                                'controller' => 'ajax',
                                'action' => 'list',
                                'item_id' => $itemId,
                                'item_type' => $linkType,
                                'type' => $type->type_id,
                                'current' => $current
                            ),
                            null,
                            true
                        )
                    )
                );

                $ret[] = $name;
                $this->_classifierElements[] = $name;
            }


        //$this->_classifierElements = $ret;
        return $ret;
    }

    protected function checkLinkType($linkType){

        $linkType = (int) $linkType;

        if (in_array($linkType, HM_Classifier_Link_LinkModel::getResourceTypes())) {
            $linkType = HM_Classifier_Link_LinkModel::TYPE_RESOURCE;
        }

        if (in_array($linkType, HM_Classifier_Link_LinkModel::getUnitTypes())) {
            //$linkType = HM_Classifier_Link_LinkModel::TYPE_UNIT;
        }

        return $linkType;

    }

    public function addClassifierDisplayGroup($classifierElements = null, $legend = null)
    {
        if (null === $legend) {
            $legend = _('Классификация');
        }

        if (null === $classifierElements) {
            $classifierElements = $this->_classifierElements;
        }

        if ($classifierElements) {
            $this->addDisplayGroup(
                $classifierElements,
                'classifiers',
                array('legend' => $legend)
            );
        }
    }

    public function getNonClassifierValues()
    {
        $values = array();
        foreach ($this->getValues() as $key => $value) {
            if (!in_array($key, $this->_classifierElements)) {
                $values[$key] = $value; 
            }
        }  
        return $values;      
    }
    public function getClassifierValues()
    {
        $values = array();
        if ($this->_classifierElements) {
            foreach($this->_classifierElements as $name) {
                $value = $this->getValue($name);
                if (is_array($value) && count($value)) {
                    $values = array_merge($values, $value);
                } elseif (!empty($value)) {
                    $values[] = $value; // бывают плоские классификаторы
                }
            }
        }
        return $values;
    }

    public function getClassifierTypes()
    {
        $values = array();
        if ($this->_classifierElements) {
            foreach($this->_classifierElements as $name) {
                $values[] = str_replace('classifier_', '', $name);
            }
        }
        array_unique($values);
        return $values;
    }

    public function addElement($element, $name = null, $options = null)
    {
        if (is_string($element)) {
            if (strtolower($element) == 'textarea') {
                if (!isset($options['cols']) && Zend_Registry::get('config')->form->textarea->cols) {
                    $options['cols'] = Zend_Registry::get('config')->form->textarea->cols;
                }

                if (!isset($options['rows']) && Zend_Registry::get('config')->form->textarea->rows) {
                    $options['rows'] = Zend_Registry::get('config')->form->textarea->rows;
                }
            }

            if (isset($options['Validators'])) {
                foreach ($options['Validators'] as $key => $validator) {
                    if (is_array($validator)) {
                        if (isset($validator['validator']) && isset($validator['options'])) {
                            if ($validator['validator'] == 'StringLength' &&
                                is_array($validator['options']) &&
                                !array_key_exists('encoding', $validator['options']) &&
                                Zend_Registry::get('config')->charset) {
                                $options['Validators'][$key][2]['encoding'] = Zend_Registry::get('config')->charset;
                            }
                        } else {
                            if ($validator[0] == 'StringLength' &&
                                is_array($validator[2]) &&
                                !array_key_exists('encoding', $validator[2]) &&
                                Zend_Registry::get('config')->charset) {
                                $options['Validators'][$key][2]['encoding'] = Zend_Registry::get('config')->charset;
                            }
                        }
                    }
                }
            }
        }
        parent::addElement($element, $name, $options);
    }

    /*
     * @var $modifier HM_Form_Modifier
     *
     */
    public function addModifier($modifier){
        $this->_modifiers[] = $modifier;
        $modifier->setForm($this);
        $modifier->init();

        return $this;
    }

    public function getModifiers()
    {
        return $this->_modifiers;
    }

    public function hasModifier($className)
    {
        foreach($this->getModifiers() as $modifier){
            if($modifier instanceof $className){
                return true;
            }
        }
        return false;
    }

    /*
     * elements  = array();
     * $prefixes = array('lang'=>'desc') or null;
     */
    public function addElementsPrefixLanguages($elements,$prefixes=null)
    {
        $addingElements = array();

        if($this->getService('Lang')->countLanguages() <= 1)
            return false;
        if(!count($elements))
            return false;
        if($prefixes === null){
            $prefixes = Zend_Registry::get('config')->form->more->languages;
        }


        if(count($prefixes) && count($elements)){
            foreach($prefixes as $prefix => $desc){
                foreach($elements as $elementName){
                    $element =  clone $this->getElement($elementName);
                    if($element){
                        $element->setLabel($this->getLabelWithDesc($element,$desc));
                        $element->setName($this->getNameWithPrefix($element,$prefix));
                        $this->addElement($element);
                        //$displayGroup->addElement($this->getElement($element->getName()));
                        $this->setElementWithPrefix($element->getName());
                    }
                }
            }
        }
    }

    public function setElementWithPrefix($name)
    {
        $this->_elementsWithPrefix[] = $name;
    }

    public function getElementsWithPrefix()
    {
        if(count($this->_elementsWithPrefix))
            return $this->_elementsWithPrefix;
        else
            return false;
    }

    public function unsetElementsWithPrefix()
    {
        $this->_elementsWithPrefix = array();
    }

    protected function getNameWithPrefix($element,$prefix){
        if(strlen($prefix)){
            return $element->getName()."_".$prefix;
        }
        return $element->getName();
    }

    protected function getLabelWithDesc($element,$desc){
        if(strlen($desc)){
            return $element->getLabel()." ("._($desc).") ";
        }
        return $element->getLabel();
    }

    public function addDisplayGroup(array $elements, $name, $options = null)
    {
        $elementsWithPrefix = $this->getElementsWithPrefix();
        if($elementsWithPrefix){
            $maxPosition =  $this->getMaxPositionElementsFromDisplayGroupByName($elements,$elementsWithPrefix)+1;
            $elements =  $this->getSliceElementByPosition($elements, $maxPosition);
            $elements = array_merge($elements['first'],$elementsWithPrefix,$elements['end']);
            $this->unsetElementsWithPrefix();
        }

        parent::addDisplayGroup($elements, $name, $options);
    }

    protected function getMaxPositionElementsFromDisplayGroupByName($elements,$elementsWithPrefix)
    {
        $positionElements = array();
        $prefixes = Zend_Registry::get('config')->form->more->languages;
        foreach($elementsWithPrefix as $elementWithPrefix){
            foreach($prefixes as $key => $desc){
                $elementWithPrefix = str_replace('_'.$key,'',$elementWithPrefix);
                if(array_search($elementWithPrefix,$elements)){
                    $positionElements[] = array_search($elementWithPrefix,$elements);
                }
            }
        }
        return max($positionElements);
    }

    protected function getSliceElementByPosition($elements = array(), $maxPosition = 0)
    {
        $slice = array();
        if(count($elements) && $maxPosition > 0){
            $slice['first'] = array_slice($elements,0,$maxPosition);
            $slice['end'] = array_slice($elements,(count($slice['first'])));
        }
        return $slice;
    }
}