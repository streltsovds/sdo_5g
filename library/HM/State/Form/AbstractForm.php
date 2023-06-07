<?php
abstract class HM_State_Form_AbstractForm extends HM_Form
{
    protected $_stateId = 0;
    protected $_state = '';

    public function setStateId($stateId)
    {
        $this->_stateId = $stateId;
    }

    public function setState($state)
    {
        $this->_state = $state;
    }

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAction($this->_getSaveUrl());

        $this->_initGroupMain();

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'isAjax' => true,
            'Label' => _('Сохранить')
        ));

        $element = $this->getElement('submit');
        $element->removeDecorator('DtDdWrapper');

        parent::init();
        
    }

    abstract protected function _initGroupMain();
    abstract protected function _getSaveUrl();
    abstract public function getIconConfig();

    public function getButtonElementDecorators()
    {
        return array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'span')),
        );
    }

//    public function __toString()
//    {
//        $form = parent::__toString();
//
//        $view      = $this->getView();
//        $formId    = $this->getId();
//
//        $url = json_encode($this->_getSaveUrl());
//
//        $iconConfig = $this->getIconConfig();
//
//        $title     = $view->escape($iconConfig['title']);
//        $className = $view->escape($iconConfig['className']);
//
//        // kill me please
//        $script = <<<SCRIPT
//<script>
//    \$(function() {
//
//        var \$form = \$('#$formId'),
//            \$workflowItem = \$form.closest('.workflow_item'),
//            \$descContainer = \$workflowItem.find('.workflow_item_description'),
//            \$actionsContainer = \$workflowItem.find('.workflow_item_head .hm-workflow-actions'),
//            \$a = $('<a class="hm-workflow-action $className" title="$title">&nbsp;</a>');
//
//        \$actionsContainer.append(\$a);
//
//        var tempHeight = 0;
//
//        function toogleForm() {
//
//            if (!\$form.is(':visible')) {
//                \$workflowItem.find('form').hide();
//                \$form.show();
//                \$descContainer.find('.wid_deadline, .wid_text').hide();
//                tempHeight = \$descContainer.height();
//                \$descContainer.height(\$form.height());
//            } else {
//                \$form.hide();
//                \$descContainer.height(tempHeight);
//                \$descContainer.find('.wid_deadline, .wid_text').show();
//            }
//        }
//
//        \$a.on('click', toogleForm);
//
//        \$form.on('click', 'button[name="cancel"]', toogleForm);
//
//        \$form.on('change', 'input, textarea', function(e) {
//            e.stopPropagation();
//        });
//
//        \$form.on('click', 'input[name="submit"]', function(e) {
//
//            e.preventDefault();
//            e.stopPropagation();
//
//            jQuery
//                .ajax({
//                    url: $url,
//                    type: 'post',
//                    data: \$form.serialize()
//                })
//
//                .done(function() {
//                    if (\$form.is(':visible')) {
//                        //toogleForm();
//
//                        $('.grid-workflow-active').removeClass('grid-workflow-active');
//                        $('.workflow').closest('.els-content').remove();
//                    }
//                });
//        });
//    });
//</script>
//SCRIPT;
//
//        return $form.$script;
//
//    }

}