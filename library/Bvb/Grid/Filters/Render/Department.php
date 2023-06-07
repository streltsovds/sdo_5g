<?php

class HM_Form_TreeSelect extends HM_Form {
}

class Bvb_Grid_Filters_Render_Department extends Bvb_Grid_Filters_Render_RenderAbstract
{


    function getFields ()
    {
        return true;
    }

    public function getConditions ()
    {
        return '=';
    }


    function render ()
    {
        $out = '';
        $fieldId = $this->getFieldName();

        $filterField = "filter_".$this->getGridId()."{$fieldId}";
        $filterParam = $this->getGridId()."{$fieldId}";
        $clearId = "{$fieldId}_clear";
        $openId = "{$fieldId}_open";
        $filterName = "{$fieldId}_name";

        $tree = "{$fieldId}_tree";
        $treeSubmit = "{$tree}_submit";
        $treeWrapper ="{$tree}_wrapper";

        $form = new HM_Form_TreeSelect(array('id'=> "{$tree}"));
        $form->addElement($form->getDefaultTreeSelectElementName(), $tree, array(
            'Label' => _('Подразделение'),
            'params' => array(
                'remoteUrl' => '/orgstructure/ajax/tree/only-departments/1',
            )
        ));
//        $form->addElement(
//            'submit',
//            $treeSubmit,
//            array(
//                'Label' => _('Искать')
//            )
//        );
        $form->setAttrib('id', "{$tree}_form");
        $form->setAttrib('class', 'treeselect');

        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

        $allWord = _('[все]');
        $dep = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($this->getDefaultValue() ? $this->getDefaultValue(): $params[$filterParam]);//$params[$tree/*_field.$this->getGridId()*/]);
        $defaultName = count($dep) ? $dep->current()->name : $allWord;
        $defaultId = count($dep) ? $dep->current()->soid : '';

        $out .= <<< EOD
<input name={$fieldId} id={$filterField} style='width:1%' type='hidden' value='{$defaultId}'></input>
<input disabled=1 id={$filterName} style='width:70%' type=text value='{$defaultName}' title='{$defaultName}'></input>
<span id="{$clearId}" class="clearFilterSpan x_filter" style='position:static; background:url(/images/reset_filters.gif) no-repeat 0 0px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<button id="{$openId}" style='width:15px; padding:0'>...</button>
EOD;

        if (!Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest()) {

            $out .= "<div style='left: 25%; top: 25%; position:absolute; z-index:100000' id={$treeWrapper}>" .
                $form->render() .
                "</div>";

            $out .= <<< EOD
<script>

    $('body').on('mousedown', '.last-cell button', function(){
        $('#{$tree}_form').css('display', 'none');
    });
    
    $('body').on('change', '#{$tree}', function(){ //Вытаскиваем значения из контрола
        $('#{$filterName}').val($('li[data-value='+$('#{$tree}').val()+']').text());
        $('#{$filterField}').val($('#{$tree}').val());
        //$('#{$tree}_form').css('display', 'none');
    });

    $('body').click(function (event) {//Для закрытия окна
	    if ($(event.target).closest('#{$tree}_form').length === 0) {
            $('#{$tree}_form').css('display', 'none');
    	}
    });

    $('body').on('click', '#{$openId}', function(event){

        $("#{$treeWrapper}").css("left", event.pageX-128);
        $("#{$treeWrapper}").css("top", event.pageY+16);
        $("body").append($("#{$treeWrapper}").detach());

    //    $("#{$filterName}").val("{$allWord}");
    //    $("#{$filterField}").val("");

        $("#{$tree}_form").css("display", "block");
        $("#{$tree}").css("display", "none");

        event.stopPropagation();
    });

    $('body').on('click', '#{$clearId}', function(event){

        event.stopImmediatePropagation();
        $('#{$filterName}').val('{$allWord}');
        $('#{$filterField}').val('');

//        $('#{$tree}').val('');

	    $('.last-cell button').trigger('click');
    });

    $('body').on('click', '#{$treeSubmit}', function(event){
//	    $('.last-cell button').trigger('click');
    });
    
    var onTreeSelect = function(value){
        alert(value);
    }    

</script>
EOD;
        }
        return $out;
    }

    public function hasConditions()
    {
        return false;
    }


    public function buildQuery($filter, $grid)
    {
        if(!$filter) return;

        $fieldName  = $this->getFieldName();
        $columns = $this->getSelect()->getPart('columns');

        $foundAlias = false;
        foreach($columns as $column) {
            if($column[2]!=$fieldName) continue;
            $foundAlias = $column[0];
            break;
        }
        $foundAlias = $foundAlias ? "{$foundAlias}." : '';
        $dep = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($filter);
        if(!$dep || !count($dep)) return;
        $dep = $dep->current();

        $this->getSelect()->where("{$foundAlias}lft>={$dep->lft} AND {$foundAlias}rgt<={$dep->rgt} ");
    }


}