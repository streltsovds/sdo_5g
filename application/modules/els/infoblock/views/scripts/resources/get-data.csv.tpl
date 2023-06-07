<?foreach ($this->series as $key => $value):?>
	<?php echo $value?>;<?php echo $this->data[$key] . "\r\n";?>
<?endforeach;?>