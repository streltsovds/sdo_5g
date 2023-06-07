<?php $card = $this->card(
    $this->resource,
    array(
        'title' => _('Название'),
        'getType()' => _('Тип'),
		//'getTypeByClassifier()' => _('Тип'),
        'description' => _('Краткое описание'),
        'getCreateBy()' =>_('Создал'),
    ),
    array(
        'title' => _('Карточка информационного ресурса')
    ));
?>
<?php if ($this->isAjaxRequest): ?>
<div style="min-height: 150px;">
    <?php echo $card;?>
</div>

<?php else: ?>
<div class="pcard pcard_inline" style="min-height: 150px;">
    <?php echo $card;?>
</div>
<?php endif; ?>