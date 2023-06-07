<div class="wiki-side-bar">
    <div class="menu-bar">
        <div class="label lwiki">Wiki</div>
        <div class="spacer"></div>
        <a href="<?php echo $this->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'view',
            'model' => $this->model->name,
            'subject_id' => $this->model->subid,
            'title' => 'Главная страница' // @todo: сделать константу
        ), null) ?>">
            <?php echo _('Главная страница') ?>
        </a><br/>
        <a href="<?php echo $this->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'content',
            'model' => $this->model->name,
            'subject_id' => $this->model->subid,
        ), null) ?>">
            <?php echo _('Оглавление') ?>
        </a><br/>
        <a href="<?php echo $this->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'history',
            'model' => $this->model->name,
            'subject_id' => $this->model->subid,
        )) ?>">
            <?php echo _('Общая история изменений') ?>
        </a>
    </div>
    <?php echo $this->wikiHistory('wikiHis'); ?>
</div>
