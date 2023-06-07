<?php echo _('Дата');?>;<?php echo $this->legend . "\r\n";?>
<?foreach ($this->series as $key => $value):?>
	<?php echo $value?>;<?php echo $this->graphs[$key] . "\r\n";?>
<?endforeach;?>