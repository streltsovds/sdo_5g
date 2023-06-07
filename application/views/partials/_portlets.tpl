<?php $iteration    = 0; ?>
<?php $updateAction = $this->isEditMode ? 'update' : 'update-my'; ?>
<?php $urlMode      = $this->isEditMode ? 'edit' : 'view'; ?>
<?php if (is_array($this->blocks) && count($this->blocks)):?>
<?php foreach($this->blocks as $key => $value):?>
    <?php $this->placeholder('columns')->captureStart((++$iteration) == 1 ? 'SET' : 'APPEND'); ?>
    <?php
        foreach($value as $val){
            try {
                if (isset($val['content'])) {
                    echo $this->{$val['block']}($val['title'], $val['content'], $val['attribs']);
                } else {
                    echo $this->{$val['block']}($val['title'], $val['attribs']);
                }
            } catch (Exception $e) {
                Zend_Registry::get('log_system')->debug($e->getMessage().'\n'.$e->getTraceAsString());
                echo $this->ScreenForm($val['title'], "<img src=\"".$this->serverUrl('/images/errors/500.png')."\"/>", array());
            }
        }
    ?>
    <?php $this->placeholder('columns')->captureEnd(); ?>
<?php endforeach;?>
<?php endif;?>
<?php if ($this->isEditMode || $this->user): ?>
<div class="portlets-editor-bar" style="display: none;">
    <?php if ($this->isAdmin && $this->isEditMode): ?>
    <span class="role-select">
        <span class="role-select-label"><?php echo _("Для роли:"); ?></span>
        <?php echo $this->formSelect('role', $this->role, array('data-url' => $this->url(
            array('module' => 'interface', 'controller' => 'edit', 'action' => 'index', 'role' => '')
        )), $this->roles)?>
    </span>
    <?php endif; ?>
    <span class="infoblock-select"></span>
    <?php if ($this->isEditMode): ?>
    <a href="#" class="add-column"><span><?php echo _("Добавить колонку"); ?></span></a>&nbsp;
    <a href="#" class="remove-column"><span><?php echo _("Удалить колонку"); ?></span></a>&nbsp;
    <?php endif; ?>
    <?php if (!$this->isEditMode && $this->user): ?>
    <a href="#" class="clear-settings"><span><?php echo _("Вернуть значения по умолчанию"); ?></span></a>&nbsp;
    <a href="<?php echo $this->escape($this->url(array())) ?>"><span><?php echo _("Закрыть"); ?></span></a>&nbsp;
    <?php endif; ?>
</div>

<?php if ($this->forcedBlocks): ?>
<div class="hm-widgets-forced">
    <div class="hm-widgets-row-forced">
        <?php
        foreach($this->forcedBlocks as $block) {
            $content = $this->{$block['name']}($block['title'], $block['attribs']);
            if (! empty($content)) {
                echo '<div class="hm-widgets-widget-content-forced" style="float: left;">' . $content . '</div>';
            }
        }
        ?>
    </div>
</div>
<?php endif; ?>

<?php $this->inlineScript()->captureStart(); ?>
<?php if ($this->isAdmin): ?>
$(document).delegate('#role', 'change', function (event) {
    document.location.href = $(event.target).data('url') + event.target.value;
});
<?php endif; ?>
var settings = {
    l10n: {
        del: <?php echo HM_Json::encodeErrorSkip(_("Удалить")) ?>,
        select: <?php echo HM_Json::encodeErrorSkip(_("Добавить инфоблок")) ?>
    },
    empty: <?php echo HM_Json::encodeErrorSkip($this->screenForm("title", "content", array('id' => ''))); ?>,
    url: {
        list:    <?php echo HM_Json::encodeErrorSkip($this->url(array('module' => 'infoblock',   'controller' => 'index', 'action' => 'index',       'role' => ''))); ?>,
        content: <?php echo HM_Json::encodeErrorSkip($this->url(array('module' => 'infoblock',   'controller' => 'index', 'action' => 'view',        'role' => '_role_', 'mode' => $urlMode, 'name' => '_name_'))); ?>,
        upload:  <?php echo HM_Json::encodeErrorSkip($this->url(array('module' => 'interface',   'controller' => 'edit',  'action' => $updateAction, 'role' => ''))); ?>,
        clear:   <?php echo HM_Json::encodeErrorSkip($this->url(array('module' => 'interface',   'controller' => 'edit',  'action' => 'clear-me'))); ?>,
        self:    <?php echo HM_Json::encodeErrorSkip($this->url(array())); ?>
    },
    role: <?php echo HM_Json::encodeErrorSkip($this->role); ?>,
    columns: {
        minWidth: 15, // in percent
        widthDelta: 0.1, // ???
        max: 3
    },
    admin: <?php echo HM_Json::encodeErrorSkip($this->isAdmin); ?>,
    isEdit: <?php echo HM_Json::encodeErrorSkip($this->isEditMode === true); ?>
};
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>
<?php
    $columns = $this->placeholder('columns')->getArrayCopy();
    $columnsCount = count($columns);
    $cssFileName = "upload/user-css/index-{$this->role}.css";
    $settingsFileName = "upload/user-css/index-{$this->role}-settings.json";
    $appendStylesheet = false;
    if (is_file(PUBLIC_PATH."/".$cssFileName) && is_file(PUBLIC_PATH."/".$settingsFileName)) {
        $settingsFileContent = file_get_contents($settingsFileName);
        $settingsFileContent = Zend_Json::decode($settingsFileContent);
        if ($settingsFileContent !== NULL && $settingsFileContent['columns'] && is_int($settingsFileContent['columns']['count']) && $settingsFileContent['columns']['count'] > 0) {
            $columnsCount = $settingsFileContent['columns']['count'];
        }
        if ($columnsCount >= count($columns))
            $appendStylesheet = true;
    }
    while ($columnsCount > count($columns)) {
        if ($this->isEditMode)
            array_push($columns, '&nbsp;');
        else
            array_push($columns, $this->screenForm(_('Пустая колонка'), _('Пустая колонка'), array('data-undeletable' => false, 'id' => '')));
    }
?>
<?php echo $this->partial('_columns.tpl', 'default', array(
    'columns' => $columns,
    'classes' => "user-dashboard user-all-dashboard user-{$this->role}-dashboard"
)); ?>
<?php
    if ($appendStylesheet) {
        $this->headLink()->appendStylesheet( $this->serverUrl("/$cssFileName")."?".filemtime(PUBLIC_PATH."/".$cssFileName) );
    }
?>
<?php $this->inlineScript()->captureStart(); ?>
jQuery(function ($) {
	$('.user-dashboard .column > .ui-portlet')
		.removeClass('ui-portlet-last-in-column');
	$('.user-dashboard .column').each(function () {
		 $('> .portlet:visible', this).last().addClass('ui-portlet-last-in-column');
	});
});
<?php $this->inlineScript()->captureEnd(); ?>

<?php if ($this->forcedBlocks): ?>
    <?php $this->inlineScript()->captureStart(); ?>

    jQuery(function($){

        var $blocks = $('.hm-widgets-row-forced > div');

        if ($blocks.length) {
            var paddings = 100* 22 * $blocks.length / window.innerWidth;
            var percent = (100 - paddings) / $blocks.length;
            $blocks.each(function(){
                $(this).css('width', percent + '%');
            });
        }
    });


    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>