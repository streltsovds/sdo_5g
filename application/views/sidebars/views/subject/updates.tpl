<?php //if (!$this->isEndUser): ?>
<!--<a href="--><?php //echo $this->url(['module' => 'news', 'controller' => 'index', 'action' => 'grid', 'subject_id' => $this->model->subid, 'subject' => 'subject'])?><!--">Редактировать новости</a>-->
<?php //endif;?>
<hm-sidebar-updates-news
    :data-sidebar='<?php echo $this->data ?>'
/>
