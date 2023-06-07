<?php if (!$this->editable): ?>
    <div class="error-box" id="error-box"><div title="" class="ui-widget ui-els-flash-message"><div class="ui-state-error ui-corner-all"><span class="ui-icon ui-icon-check"></span><div class="ui-message-here"><?php echo _('Редактирование задач недоступно на данном этапе сессии кадрового резерва');?></div></div></div></div><br>
<?php else:?>
    <?php echo $this->actions();?>
<?php endif;?>

<?php echo $this->grid;?>