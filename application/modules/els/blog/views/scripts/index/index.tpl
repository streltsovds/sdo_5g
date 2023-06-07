<?php if (!$this->gridAjaxRequest):?>
    <?php if ($this->isParticipant || $this->isModerator):?>
        <?php echo $this->Actions('blog', array(
            array(
                'title' => _('Создать запись'),
                'url' => $this->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'new', 'subject' => $this->subjectName, 'subject_id' => $this->subjectId), null, true)
            )
        ), null);?>
    <?php endif;?>
<?php endif;?>

<div class="blog-middle">
    <div class="blog-list">
        <?php if(count($this->blogPosts) > 0):?>
        <?php foreach($this->blogPosts as $blogPost) :?>
            <?php include 'post.tpl';?>
        <?php endforeach;?>
        <?php /*paginator*/ echo $this->blogPosts?>
        <?php else:?>
        <?php echo _('Нет данных для отображения');?>
        <?php endif;?>
    </div>

    <div style="clear: both;"></div>
</div>

<?php echo $this->pageRate('RATED'); //RATED UNRATED ?>
