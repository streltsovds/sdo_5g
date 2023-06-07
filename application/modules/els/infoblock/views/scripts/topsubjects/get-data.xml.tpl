<pie>
<?foreach ($this->data as $key => $value):?>
	<slice title="<?php echo iconv(Zend_Registry::get('config')->charset, 'UTF-8', $key);?>"><?php echo $value?></slice>
<?endforeach;?>
</pie>