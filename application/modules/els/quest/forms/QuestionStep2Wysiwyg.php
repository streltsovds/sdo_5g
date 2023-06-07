<?php
class HM_Form_QuestionStep2Wysiwyg extends HM_Form_QuestionStep2 {

    //ALARM !!!!! separateWysiwygVariants паддинг-боттом к визивигу добавляет.
    // так что все доп поля добавлять ДО визивига

    const VARIANTS_COUNT = 25;

    protected $_elementNames = array();
    protected function _initQuestionSingle()
    {
        $this->_initSingleMultiple();

        $checkboxName = self::DEFAULT_ELEMENT.'_'.'checkbox'.'_';

        if($this->getName() == 'questionStep2'){
            Zend_Registry::get('view')->inlineScript()->captureStart();
            echo <<<E0D
                $(document).ready(function(){
                    $("input[name^='{$checkboxName}']:checkbox").parent().on('mousedown', function(){
                        $("input[name^='{$checkboxName}']:checkbox").removeAttr('checked');
                    });
                });
E0D;
            Zend_Registry::get('view')->inlineScript()->captureEnd();
        }
    }

    protected function _initTest()
    {
        parent::_initTest();
        $this->separateWysiwygVariants();
        $this->addVariantsDisplayGroup();
    }

    protected function _initQuestionMultiple()
    {
        $this->_initSingleMultiple();
    }

    protected function _initSingleMultiple(){
        $checkboxName = self::DEFAULT_ELEMENT.'_'.'checkbox'.'_';
        $variantName  = self::DEFAULT_ELEMENT.'_'.'variant'.'_';
        $weightName = self::DEFAULT_ELEMENT.'_'.'weight'.'_';
        $idName       = self::DEFAULT_ELEMENT.'_'.'id'.'_';
        $variantCount = self::VARIANTS_COUNT;


        for($i = 1; $i <= $variantCount; $i++){
            $this->addElement('hidden', $idName.$i, array());
            if ($this->_session['questionStep1']['mode_scoring'] == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT) {
                $this->addElement($this->getDefaultCheckboxElementName(), $checkboxName.$i, array(
                    'Label' => _('Правильный ответ'),
                ));

            } else {
                if ($questId = $this->getParam('quest_id', 0)) {
                    $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
                    if ($quest->type == 'test') {
                        $this->addElement($this->getDefaultTextElementName(),$weightName.$i, array('Label' => _('Вес'), 'class' => 'brief'));
                    }

                }
            }

            $this->addElement($this->getDefaultWysiwygElementName(), $variantName.$i, array(
                'Label' => _('Текст варианта'),
                'Required' => ($i == 1 ? true : false),
                'class' => ''
            ));
            $this->_elementNames[] = $idName.$i;
            $this->_elementNames[] = $checkboxName.$i;
            $this->_elementNames[] = $weightName.$i;
            $this->_elementNames[] = $variantName.$i;
        }

        if($this->getName() == 'questionStep2'){
            Zend_Registry::get('view')->inlineScript()->captureStart();
            echo <<<E0D
                $(document).ready(setTimeout(function(){
                    var showElementCount = 3;
                    var elementsWithValues = 0;

                    elementsWithValues = $("input[id^='{$idName}']").filter("input[value!='']").length

                    if(elementsWithValues > showElementCount){
                        showElementCount = elementsWithValues;
                    }

                    $("input[name^='{$checkboxName}']:hidden").slice(showElementCount).parent().hide();
                    $("input[name^='{$weightName}']:text").slice(showElementCount).parent().parent().parent().parent().hide();
                    $("textarea[name^='{$variantName}']").slice(showElementCount).parent().hide();

                    if($("input[name^='{$checkboxName}']:hidden").first().parent().length > 0 ||
                        $("input[name^='{$weightName}']:text:hidden").first().parent().length > 0){
                        $("#btn_submit").before("<div><a href=# id='add_variants' class='v-btn v-btn--contained theme--light primary' style='margin-bottom: 5px;'>Добавить еще поле</a><div>");

                        $("#add_variants").on('click', function(e){
                            e.preventDefault();
                            $("input[name^='{$checkboxName}']:hidden").parent(":hidden").first().show();
                            $("input[name^='{$weightName}']:text:hidden").first().parent().parent().parent().parent().show();
                            $("textarea[name^='{$variantName}']").parent(":hidden").first().show();
                        });
                    }
                }, 1000));
E0D;
            Zend_Registry::get('view')->inlineScript()->captureEnd();
        }
    }


    protected function _initMappingClassification($elementName){
        $dataName     = self::DEFAULT_ELEMENT.'_'.'data'.'_';
        $variantName  = self::DEFAULT_ELEMENT.'_'.'variant'.'_';
        $idName       = self::DEFAULT_ELEMENT.'_'.'id'.'_';
        $variantCount = self::VARIANTS_COUNT;


        for($i = 1; $i <= $variantCount; $i++){
            $this->addElement('hidden', $idName.$i, array());
            $this->addElement($this->getDefaultTextElementName(), $variantName.$i, array(
                'Label' => $elementName,
                'class' => '',
            ));
            $this->addElement($this->getDefaultWysiwygElementName(), $dataName.$i, array(
                'Label' => _('Текст варианта'),
                'Required' => ($i == 1 ? true : false),
            ));
            $this->_elementNames[] = $idName.$i;
            $this->_elementNames[] = $variantName.$i;
            $this->_elementNames[] = $dataName.$i;

        }


        if($this->getName() == 'questionStep2'){
            Zend_Registry::get('view')->inlineScript()->captureStart();
            echo <<<E0D
                $(document).ready(setTimeout(function(){

                    var showElementCount = 3;
                    var elementsWithValues = 0;

                    elementsWithValues = $("input[id^='{$idName}']").filter("input[value!='']").length

                    if(elementsWithValues > showElementCount){
                        showElementCount = elementsWithValues;
                    }

                    $("input[name^='{$variantName}']:text").slice(showElementCount).parent().hide();
                    $("input[name^='{$variantName}']:text").slice(showElementCount).parent().parent().parent().parent().hide();
                    $("textarea[name^='{$dataName}']").slice(showElementCount).parent().hide();

                    if($("input[name^='{$variantName}']:text:hidden").first().parent().length > 0){
                        $("#btn_submit").before("<div><a href=# id='add_variants' class='v-btn v-btn--contained theme--light primary' style='margin-bottom: 5px;'>Добавить еще поле</a><div>");

                        $("#add_variants").on('click', function(e){
                            e.preventDefault();
                            $("input[name^='{$variantName}']:text:hidden").first().parent().show();
                            $("input[name^='{$variantName}']:text:hidden").first().parent().parent().parent().parent().show();
                            $("textarea[name^='{$dataName}']").parent(":hidden").first().show();
                        });
                    }
                }, 1000));
E0D;
            Zend_Registry::get('view')->inlineScript()->captureEnd();
        }
    }

    protected function _initQuestionMapping()
    {
        $this->_initMappingClassification(_('Соотвествие'));
    }

    protected function _initQuestionClassification()
    {
        $this->_initMappingClassification(_('Класс'));
    }

    protected function _initQuestionSorting()
    {
        $numberName       = self::DEFAULT_ELEMENT.'_'.'number'.'_';
        $numberHiddenName = self::DEFAULT_ELEMENT.'_'.'number_hidden'.'_';
        $variantName      = self::DEFAULT_ELEMENT.'_'.'variant'.'_';
        $idName           = self::DEFAULT_ELEMENT.'_'.'id'.'_';
        $variantCount     = self::VARIANTS_COUNT;

        for($i = 1; $i <= $variantCount; $i++){
            $this->addElement('hidden', $idName.$i, array());

            $this->addElement('hidden', $numberHiddenName.$i, array('value' => $i));
            $this->addElement($this->getDefaultTextElementName(), $numberName.$i, array(
                'Label'    => _('№'),
                'class'    => 'brief',
                'disabled' => true,
                'value'    => $i,
            ));

            $this->addElement($this->getDefaultWysiwygElementName(), $variantName.$i, array(
                'Label' => _('Текст варианта'),
                'Required' => ($i == 1 ? true : false),
            ));
            $this->_elementNames[] = $idName.$i;
            $this->_elementNames[] = $numberHiddenName.$i;
            $this->_elementNames[] = $numberName.$i;
            $this->_elementNames[] = $variantName.$i;
        }

        if($this->getName() == 'questionStep2'){
            Zend_Registry::get('view')->inlineScript()->captureStart();
            echo <<<E0D
                $(document).ready(setTimeout(function(){

                    var showElementCount = 3;
                    var elementsWithValues = 0;

                    elementsWithValues = $("input[id^='{$idName}']").filter("input[value!='']").length

                    if(elementsWithValues > showElementCount){
                        showElementCount = elementsWithValues;
                    }

                    $("input[name^='{$numberName}']:text").slice(showElementCount).parent().parent().parent().parent().hide();
                    $("textarea[name^='{$variantName}']").slice(showElementCount).parent().hide();

                    if($("input[name^='{$numberName}']:text:hidden").first().parent().length > 0){
                        $("#btn_submit").before("<div><a href=# id='add_variants' class='v-btn v-btn--contained theme--light primary' style='margin-bottom: 5px;'>Добавить еще поле</a><div>");

                        $("#add_variants").on('click', function(e){
                            e.preventDefault();
                            $("input[name^='{$numberName}']:text:hidden").first().parent().parent().parent().parent().show();
                            $("textarea[name^='{$variantName}']").parent(":hidden").first().show();
                        });
                    }
                }, 1000));
E0D;
            Zend_Registry::get('view')->inlineScript()->captureEnd();
        }
    }


    public function separateWysiwygVariants()
    {
        $view = $this->getView();
        $variantName      = self::DEFAULT_ELEMENT.'_'.'variant'.'_';
        $dataName = self::DEFAULT_ELEMENT.'_'.'data'.'_';
        $separateWysiwygScript = <<<SCRIPT
        $(document).ready(function(){
            $("textarea[id^='{$variantName}']").parent().addClass('wysiwyg-variant-separated');
            $("textarea[id^='{$dataName}']").parent().addClass('wysiwyg-variant-separated');
        });
SCRIPT;
        $view->inlineScript()->appendScript($separateWysiwygScript);
    }

        public function addVariantsDisplayGroup()
    {
        if(count($this->_elementNames)) {
            $this->addDisplayGroup($this->_elementNames, 'variants', array('legend' => _('Варианты ответов')));
        }
    }
}