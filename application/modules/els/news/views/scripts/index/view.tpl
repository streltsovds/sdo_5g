<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/news.css');?>
<div class="news-side-bar">
	<?php if ($this->isPreviousExists) : ?>
        ‹ <a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => -1))?>"><?php echo _('Предыдущая')?></a> |
    <?php endif;?>
	<a href="<?php echo $this->url(array('news_id' => null, 'step' => null, 'action' => 'index'))?>"><?php echo _('Список новостей')?></a> |
    <?php if ($this->isNextExists) : ?>
	    <a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => 1))?>"><?php echo _('Следующая')?></a> ›
    <?php endif;?>
	</div>
	<div class="spacer">
</div>
	
<?php if ($this->news):?>
	
    <?php echo $this->newsPreview($this->news, 1)?>
    
    <div class="spacer"></div>
	<div class="news-side-bar">
	<?php if ($this->isPreviousExists) : ?>
        ‹ <a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => -1))?>"><?php echo _('Предыдущая')?></a> |
    <?php endif;?>
	<a href="<?php echo $this->url(array('news_id' => null, 'step' => null, 'action' => 'index'))?>"><?php echo _('Список новостей')?></a> |
    <?php if ($this->isNextExists) : ?>
	    <a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => 1))?>"><?php echo _('Следующая')?></a> ›
    <?php endif;?>
	</div>
<?php else:?>
    <?php echo _('Новость не найдена')?>
<?php endif;?>