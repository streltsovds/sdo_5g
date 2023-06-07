<?php
if (!$this->gridAjaxRequest):?>
    <?php if ($this->isModerator):?>
        <?php echo $this->Actions('blog', array(
            array(
                'title' => _('Создать запись'),
                'url' => $this->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'new', 'subject' => $this->subjectName, 'subject_id' => $this->subjectId), null, true)
            )
        ), null);?>
    <?php endif;?>
<?php endif;

echo $this->grid?>

<?php echo $this->pageRate('RATED'); //RATED UNRATED ?>
