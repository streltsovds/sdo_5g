<?php echo iconv('utf-8','cp1251',_('Период'));?>;<?php echo iconv('utf-8','cp1251',_('Количество заявок')) . "\r\n";?>
<?foreach ($this->series as $key => $value):?>
<?php echo $value?>;<?php echo $this->graphs[$key] . "\r\n";?>
<?endforeach;?>