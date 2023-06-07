<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->headScript()?>
<?php endif;?>

<?php if(count($this->articles) > 0):?>
<div class="wiki">
    <div class="wiki-article">
        <div class="article-body">
        <h1><?php echo _('Оглавление');?></h1>
<ul class="articles-list">
<?php foreach($this->articles as $article):?>
    <li>
        <a href="<?php echo $this->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'view',
            'subject' => $this->subjectName,
            'subject_id' => $this->subjectId,
            'title' => $article->getUrl()
        ), null, true,FALSE)?>"><?php echo $article->title?></a>
    </li>
<?php endforeach;?>
</ul>
        </div>
        <div class="spacer"></div>
    </div>
    <?php /*include 'sidebar.tpl';*/?>
</div>
<?php else:?>
<div><?php echo _('Нет данных для отображения');?></div>
<?php endif;?>

