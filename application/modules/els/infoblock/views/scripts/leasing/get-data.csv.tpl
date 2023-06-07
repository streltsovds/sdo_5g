<?php echo $this->legendY;?>;<?php echo $this->legendX . "\r\n";?>
<?foreach ($this->series as $key => $value):?>
<?php echo iconv('UTF-8', Zend_Registry::get('config')->charset, $value)?>;<?php echo $this->graphs[$key] . "\r\n";?>
<?endforeach;?>