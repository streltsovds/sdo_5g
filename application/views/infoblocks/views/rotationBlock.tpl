<?php
    $url = $this->url(array(
        'baseUrl' => 'hr',
        'module' => 'rotation',
        'controller' => 'list',
        'action' => 'print-forms',
        'type' => 'plan',
        'rotation_id' => $this->rotation->rotation_id
    ));
?>

<?php
$urlRotation = $this->url(array(
    'baseUrl' => 'hr',
    'module' => 'rotation',
    'controller' => 'report',
    'action' => 'index',
    'rotation_id' => $this->rotation->rotation_id
));
?>
<div class="rotation-block">
    <p>В рамках <a href="<?php echo $urlRotation; ?>">программы ротации</a> Вам необходимо:</p>
<ul>
    <?php foreach($this->events as $event): ?>
    <li><?php echo $event?></li>
    <?php endforeach; ?>
</ul>
<p><a href="<?php echo $url; ?>">Шаблон индивидуальной программы ротации</a></p>
</div>