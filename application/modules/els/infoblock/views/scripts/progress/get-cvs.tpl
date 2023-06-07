<?php echo _('Название курса');?>;<?php echo _('Записаны');?>;<?php echo _('Учатся');?>;<?php echo _('Завершили');?>;<?php echo _('%') . PHP_EOL;?>
<?foreach ($this->data as $value):?>
    <?php echo iconv('UTF-8', Zend_Registry::get('config')->charset, $value[0]);?>;<?php echo $value[1]?>;<?php echo $value[2]?>;<?php echo $value[3]?>;<?php echo $value[4]?>;<?php echo PHP_EOL;?>
<?endforeach;?>
<?php 
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Progress.csv"');
die();
?>