<div class="activitydev-control">
<?php echo _("Распределение активности:")?><br>
<select class="activitydev-select-type" name="type">
<option id="activitydev-select-type-day" value="times" <?php echo ($this->type == 'times') ? 'selected' : ''; ?>><?php echo _('в течение суток')?></option>
<option id="activitydev-select-type-week" value="dates" <?php echo ($this->type == 'dates') ? 'selected' : ''; ?>><?php echo _('в течение недели')?></option>
</select>
</div>
<div class="activitydev-control">
<?php echo _("Период:")?><br>
<?php echo $this->chartSelectPeriod($this->periodSet, $this->period);?>
</div>
<?php
$export_url = $this->url(array(
	'module' => 'infoblock',
	'controller' => 'activitydev',
	'action' => 'get-data',
	'format' => 'csv',
));
$id = $this->id('button');
?>
<a href="<?php echo $export_url; ?>" title="<?php echo _('Экспортировать данные в .csv')?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
<?php $this->inlineScript()->captureStart(); ?>
$(function () { $('#<?php echo $id; ?>').button({text: false}); });
<?php $this->inlineScript()->captureEnd(); ?>
<div style="clear: both"></div>
<?php echo $this->chart('activitydev', 'amline');?>