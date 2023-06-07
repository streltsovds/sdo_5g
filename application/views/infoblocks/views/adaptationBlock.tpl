<?php
    $url = $this->url(array(
        'baseUrl' => 'recruit',
        'module' => 'newcomer',
        'controller' => 'list',
        'action' => 'print-forms',
        'newcomer_id' => $this->newcomer->newcomer_id
    ));
?>
<div class="adaptation-block">
<p>В рамках программы адаптации Вам необходимо:</p>
<ul>
    <?php foreach($this->events as $event): ?>
    <li><?php echo $event?></li>
    <?php endforeach; ?>
</ul>
<p><a href="<?php echo $url; ?>">Шаблон плана адаптации</a></p>
</div>