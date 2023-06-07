<div style="text-align:center">
<?php if($this->isOffline): ?>
    <p style="margin-bottom:15px; margin-top:15px;"><a href="<?=$this->url(array(
        'module' => 'lesson',
        'controller' => 'export',
        'action' => 'csv',
        'subject_id' => $this->isOffline), null, true)?>">Экспортировать результаты</a></p>
<?php endif;?>
<?php if(!$this->isOffline): ?>
    <p style="margin-bottom:15px; margin-top:15px;"><a href="<?=$this->url(array(
        'module' => 'lesson',
        'controller' => 'import',
        'action' => 'csv'), null, true)?>">Импортировать результаты</a></p>
<?php endif;?>
</div>