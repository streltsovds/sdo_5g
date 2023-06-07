<?php
/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

class HM_Form_Decorator_AddOption extends Zend_Form_Decorator_Abstract
{
    const TYPE_EVENT = 0;
    const TYPE_CRITERION_CLUSTER = 1;
    const TYPE_CYCLE = 2;
    const TYPE_KPI = 3;
    const TYPE_KPI_CLUSTER = 4;
    const TYPE_KPI_UNIT = 5;
    const TYPE_QUEST_CLUSTER = 6;
    const TYPE_CYCLE_TC = 7;

    /**
     * Whether or not to escape the description
     * @var bool
     */
    protected $_escape;

    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'APPEND';

    /**
     * HTML tag with which to surround description
     * @var string
     */
    protected $_tag;

    /**
     * Set HTML tag with which to surround description
     *
     * @param  string $tag
     * @return Zend_Form_Decorator_Description
     */
    public function setTag($tag)
    {
        $this->_tag = (string) $tag;
        return $this;
    }

    /**
     * Get HTML tag, if any, with which to surround description
     *
     * @return string
     */
    public function getTag()
    {
        if (null === $this->_tag) {
            $tag = $this->getOption('tag');
            if (null !== $tag) {
                $this->removeOption('tag');
            } else {
                $tag = 'p';
            }

            $this->setTag($tag);
            return $tag;
        }

        return $this->_tag;
    }

    /**
     * Get class with which to define description
     *
     * Defaults to 'hint'
     *
     * @return string
     */
    public function getClass()
    {
        $class = $this->getOption('class');
        if (null === $class) {
            $class = 'add-option';
            $this->setOption('class', $class);
        }

        return $class;
    }

    /**
     * Set whether or not to escape description
     *
     * @param  bool $flag
     * @return Zend_Form_Decorator_Description
     */
    public function setEscape($flag)
    {
        $this->_escape = (bool) $flag;
        return $this;
    }

    /**
     * Get escape flag
     *
     * @return true
     */
    public function getEscape()
    {
        if (null === $this->_escape) {
            if (null !== ($escape = $this->getOption('escape'))) {
                $this->setEscape($escape);
                $this->removeOption('escape');
            } else {
                $this->setEscape(true);
            }
        }

        return $this->_escape;
    }

    /**
     * Render a description
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view || $element->disabled) {
            return $content;
        }

        if ($subjectId = Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1)) {

            if ($optionType = $element->getAttrib('OptionType')) {
        		$target = $element->getAttrib('id');

        		if (!$options = $this->_getAddOptions($optionType,strtolower($view->getParam('subjectName')))) {
                    return $content;
        		}
            } else {
                return $content;
            }
        } else {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag       = $this->getTag();
        $class     = $this->getClass();
        $escape    = $this->getEscape();

        $view->inlineScript(Zend_View_Helper_HeadScript::SCRIPT)->appendScript("
            jQuery(document).ready(function(){
                $(document).bind('keydown',function(e){
                    var keycode;
                    if (window.event) keycode = window.event.keyCode;
                    else if (e) keycode = e.which;
                    if(keycode==13){
                		if($('.ui-dialog').is(':hidden')) {return false}
	                    var title = jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#title').val();
	                    var subjectId = jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#subjectId').val();

                        if (!title || !subjectId) {	return false }
                        console.log('enter: '+jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#title').val())
                        $('.ui-dialog button').trigger('click')
                    }
                });
                var buttons = {};
                buttons['" . _('Сохранить') . "'] = function() {
                    $(this).dialog('close');
                    var title = jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#title').val();
                    var subjectId = jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#subjectId').val();
                    if (title && subjectId) {
                        jQuery.ajax({
                            url: '{$options['url']}',
                            type: 'POST',
                            data: {title: title},
                            dataType: 'json',
                            success: function(result){
                                jQuery('.add-option-dialog#{$options['redirectUrlParam']}').dialog('close');
                                jQuery('#{$target}')
                                    .append($('<option>', { value : result })
                                    .text(jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#title').val()));
                                jQuery('#{$target}').val(result).attr('selected',true);
                                jQuery('#redirectUrl').val('{$options['redirectUrl']}/{$options['redirectUrlParam']}/' + result);
                            }
                        });
                    }
                };

                jQuery('.add-option-dialog#{$options['redirectUrlParam']}').dialog({
                    autoOpen: false,
                    resizeable: false,
                    width: 400,
                    modal: true,
                    title: '{$options['titleUI']}',
                    buttons: buttons
                });
                jQuery('.add-option#{$options['redirectUrlParam']}').click(function(){
                    jQuery('.add-option-dialog#{$options['redirectUrlParam']} input#title').val('')
                    jQuery('.add-option-dialog#{$options['redirectUrlParam']}').dialog('open');
                });
            });
        ");

        $title = _('Название');
        $html = <<<E0D
            <div class='add-option' id='{$options['redirectUrlParam']}' title='{$options['title']}'>&nbsp;&nbsp;&nbsp;&nbsp;</div>
            <div class='add-option-dialog' id='{$options['redirectUrlParam']}'>
                <dl>
                    <dt id="title-label"><label class="required" for="title">{$title}</label> <span class="required-star">*</span></dt>
                    <dd class="element">
                        <input type="hidden" id="subjectId" value="{$subjectId}">
                        <input type="text" value="" id="title" name="title">
                    </dd>
                </dl>
            </div>
E0D;

        switch ($placement) {
            case self::PREPEND:
                return $html . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $html;
        }
    }


    // @todo: убрать это безобразие из library
    private function _getAddOptions($optionType,$subject)
    {
        $return = array();
        switch ($optionType) {
        	case HM_Event_EventModel::TYPE_COURSE:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать пустой учебный модуль и добавить в выпадающий список');
        		$return['titleUI'] = _('Создать учебный модуль');
                // тут и ниже с baseUrl хак... рефакторить времени мало, можно и закопаться надого
                $return['url'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'new-default', 'controller' => 'list', 'module' => 'course', 'subForm' => null));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'index', 'controller' => 'course', 'module' => 'subject', 'subForm' => null));
                $return['redirectUrlParam'] = 'course_id';
        		break;

        	case HM_Event_EventModel::TYPE_RESOURCE:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать пустой информационный ресурс и добавить в выпадающий список');
        		$return['titleUI'] = _('Создать информационный ресурс');
                $return['url'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'new-default', 'controller' => 'index', 'module' => 'resource', 'subForm' => null));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'edit-content', 'controller' => 'index', 'module' => 'resource', 'subForm' => null));
                $return['redirectUrlParam'] = 'resource_id';
        		break;

        	case HM_Event_EventModel::TYPE_TEST:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать пустой тест и добавить в выпадающий список');
        		$return['titleUI'] = _('Создать тест');
                $return['url'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'new-default', 'controller' => 'abstract', 'module' => 'test', 'subForm' => null));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'test', 'controller' => 'list', 'module' => 'question', 'subForm' => null));
                $return['redirectUrlParam'] = 'test_id';
        		break;

        	case HM_Event_EventModel::TYPE_TASK:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать пустое задание и добавить в выпадающий список');
        		$return['titleUI'] = _('Создать задание');
                $return['url'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'new-default', 'controller' => 'list', 'module' => 'task', 'subForm' => null));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'task', 'controller' => 'list', 'module' => 'question', 'subForm' => null));
                $return['redirectUrlParam'] = 'task_id';
        		break;

        	case HM_Event_EventModel::TYPE_POLL:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать пустой опрос и добавить в выпадающий список');
        		$return['titleUI'] = _('Создать опрос');
                $return['url'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'new-default', 'controller' => 'list', 'module' => 'poll', 'subForm' => null));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'quiz', 'controller' => 'list', 'module' => 'question', 'subForm' => null));
                $return['redirectUrlParam'] = 'quiz_id';
        		break;

        	case HM_Event_EventModel::TYPE_WEBINAR:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать пустой контейнер для материалов вебинара и добавить в выпадающий список');
        		$return['titleUI'] = _('Создать материалы вебинара');
                $return['url'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'new-default', 'controller' => 'list', 'module' => 'webinar', 'subForm' => null));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('baseUrl' =>'', 'action' => 'edit', 'controller' => 'list', 'module' => 'webinar', 'subForm' => null));
                $return['redirectUrlParam'] = 'webinar_id';
        		break;

        	case self::TYPE_CRITERION_CLUSTER:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать новый кластер критериев');
        		$return['titleUI'] = _('Создать кластер');
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'cluster', 'module' => 'criterion'));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'cluster', 'module' => 'criterion', 'subForm' => null));
                $return['redirectUrlParam'] = 'cluster_id';
        		break;

            case self::TYPE_CYCLE:
                $return['label'] = _('Название');
                $return['title'] = _('Создать новый период оценки');
                $return['titleUI'] = _('Создать период оценки');
                // @todo: Не работает, нужно передавать параметр "подсистема" (els vs at)
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'list', 'module' => 'cycle', 'baseUrl' => '', 'type' => HM_Cycle_CycleModel::CYCLE_TYPE_ASSESMENT));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'list', 'module' => 'cycle', 'subForm' => null));
                $return['redirectUrlParam'] = 'cycle_id';
                break;

            case self::TYPE_CYCLE_TC:
                $return['label'] = _('Название');
                $return['title'] = _('Создать новый период оценки');
                $return['titleUI'] = _('Создать период оценки');
                // @todo: Не работает, нужно передавать параметр "подсистема" (els vs at)
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'list', 'module' => 'cycle', 'baseUrl' => '', 'type' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'list', 'module' => 'cycle', 'subForm' => null));
                $return['redirectUrlParam'] = 'cycle_id';
                break;

            case self::TYPE_KPI:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать персональный показатель эффективности');
        		$return['titleUI'] = _('Создать показатель');
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'list', 'module' => 'kpi'));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'list', 'module' => 'kpi'));
                $return['redirectUrlParam'] = 'kpi_id';
        		break;

        	case self::TYPE_KPI_CLUSTER:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать кластер показателей эффективности');
        		$return['titleUI'] = _('Создать кластер');
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'cluster', 'module' => 'kpi', 'baseUrl' => 'at'));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'cluster', 'module' => 'kpi'));
                $return['redirectUrlParam'] = 'kpi_cluster_id';
        		break;

        	case self::TYPE_KPI_UNIT:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать единицу измерения показателей эффективности');
        		$return['titleUI'] = _('Создать единицу измерения');
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'unit', 'module' => 'kpi', 'baseUrl' => 'at'));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'unit', 'module' => 'kpi'));
                $return['redirectUrlParam'] = 'kpi_unit_id';
        		break;
        		
        	case self::TYPE_QUEST_CLUSTER:
        		$return['label'] = _('Название');
        		$return['title'] = _('Создать новый блок вопросов');
        		$return['titleUI'] = _('Создать блок вопросов');
                $return['url'] = Zend_Registry::get('view')->url(array('action' => 'new-default', 'controller' => 'cluster', 'module' => 'quest'));
                $return['redirectUrl'] = Zend_Registry::get('view')->url(array('action' => 'edit', 'controller' => 'cluster', 'module' => 'quest', 'subForm' => null));
                $return['redirectUrlParam'] = 'quest_cluster_id';
        		break;

        	default:
        	    $return = false;
        		break;
        }
        return $return;
    }
}
