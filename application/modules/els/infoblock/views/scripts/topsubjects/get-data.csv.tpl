<?php echo _('Курс');?>;<?php echo _('Количество заявок') . "\r\n";?>
<?foreach ($this->data as $key => $value):?>
<?php echo $key?>;<?php echo $value . "\r\n";?>
<?endforeach;?>