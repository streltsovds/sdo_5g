<?php echo $this->chart('claims');?>
<?php
$export_url = $this->url(array(
							'module' => 'infoblock',
							'controller' => 'claims',
							'action' => 'get-data',
							'format' => 'csv',
));
$id = $this->id('button');
?>
<a title="<?php echo _('Экспортировать данные в .csv')?>" href="<?php echo $export_url; ?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
<?php $this->inlineScript()->captureStart(); ?>
$(function () { $('#<?php echo $id; ?>').button({text: false}); });
<?php $this->inlineScript()->captureEnd(); ?>
<p>
<?php echo sprintf(_('За период %s поступило заявок: %s'), $this->chartSelectPeriod($this->periodSet, $this->period), "<span id=\"claims-placeholder-total\" class=\"claims-total\">{$this->total}</span>");?>.&nbsp;
<?php if ($this->undone): ?>
<?php echo sprintf(_('Не обработано заявок: %s'), "<span class=\"claims-undone\"><a href=\"" . $this->url(array('module' => 'order', 'controller' => 'list')) . "\" title=\"" . _('Список заявок') . "\">{$this->undone}</a></span>");?>
<?php else: ?>
<?php echo _('Необработанных заявок нет'); ?>.</p>
<?php endif; ?>
</p>