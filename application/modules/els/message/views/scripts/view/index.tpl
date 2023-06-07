<style type="text/css">

    .hm-select-headSwitcher .hm-select-option,
    .hm-select-headSwitcher .hm-select-option:hover {
        border: none;
        min-width: 20px;
        min-height: 20px;
        margin: 0 !important;
        padding: 1px !important;
        background-color: transparent !important;
        border-radius: 5px !important;
    }

    .hm-select-headSwitcher .hm-select-option-selected,
    .hm-select-headSwitcher .hm-select-option-selected:hover {
    }

    .hm-select-headSwitcher .hm-select-option span {
        display: none;
    }

    .hm-select-headSwitcher .hm-select-option-icon-1:before,
    .hm-select-headSwitcher .hm-select-option-programm:before,
    .hm-select-headSwitcher .hm-select-option-icon-2:before,
    .hm-select-headSwitcher .hm-select-option-list:before,
    .hm-select-headSwitcher .hm-select-option-icon-3:before,
    .hm-select-headSwitcher .hm-select-option-table:before {
        display: none;
    }

    .hm-select-headSwitcher ul :first-child {
        background: transparent url(/images/icons/list_cal.png) center center no-repeat;
    }

    .hm-select-headSwitcher ul :nth-child(2) {
        background: transparent url(/images/icons/list_text.png) center center no-repeat;
    }

    .hm-messenger-contact_selected {
        /*border-color: #1171b4;
        color: #1171b4 !important;
        background-color: #e3e3e3 !important;
        text-shadow: none;*/
    }

    .hm-messenger-contact_selected:after {
        border-color: transparent #1171b4 transparent transparent;
    }


    .hm-select-option-list.hm-select-option-selected {
        background: transparent url(/images/icons/list_text_active.png) center center no-repeat !important;
    }

    .hm-select-option-table.hm-select-option-selected {
        background: transparent url(/images/icons/list_cal_active.png) center center no-repeat !important;;
    }


</style>

<div class="hm-head-switchers">
    <div id="hm-head-switcher-default"></div>
</div>
<div class="hm-clear"></div>
<?php
$HM = $this->HM();

$HM->create('hm.core.ui.select.custom.HeadSwitcher', array(
    'renderTo' => '#hm-head-switcher-default',
    'paramName' => 'action',
    'options' => array(
        'system' => array('text' => _('Системные'), 'addClass' => 'hm-select-option-table'),
        'index' => array('text' => _('Личные'), 'addClass' => 'hm-select-option-list')
    ),
    'values' => array('index'),
    'multiple' => false
));
?>

<div id="messenger">
    <?php echo $this->form;?>
</div>