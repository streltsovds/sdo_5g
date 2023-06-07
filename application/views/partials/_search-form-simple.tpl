<form class="d-flex" style="width: 100%" action="<?php echo $this->url; ?>" method="POST">
	<v-text-field :browser-autocomplete="false" hide-details color="primary" label="Поиск по Базе Знаний" style="width: 90%;" name="search_query" type="text" value="<?php echo $this->query;?>"></v-text-field>
	<v-btn class="primary" type="submit"><v-icon left>search</v-icon><?php echo _('Найти') ?></v-btn>
	<v-divider vertical></v-divider>
	<v-btn text color="primary" href="<?php echo $this->url(array('action' => 'advanced-search', 'page' => null, 'search_query' => null));?>">
		<?php echo _('Расширенный поиск');?>
	</v-btn>
</form>