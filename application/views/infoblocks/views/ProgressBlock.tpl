<v-card-text>
	<hm-grid load-url="<?php echo $this->url; ?>"></hm-grid>
</v-card-text>
<v-divider></v-divider>
<v-card-actions>
	<v-btn text small color="primary" href="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'progress', 'action' => 'get-cvs')); ?>" target="_blank"><?php echo _('Экспортировать данные в .csv')?></v-btn>
</v-card-actions>

