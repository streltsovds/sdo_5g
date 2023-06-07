<h2><?php echo _('Экспорт результатов'); ?></h2>
<p>
    <?php echo _('Вы можете получить результаты своей работы с оффлайн-версией для отправки организатору обучения'); ?><br>
    <a href="<?php echo $this->url(array('module' => 'offline', 'controller' => 'export', 'action' => 'download')); ?>"><?php echo _('Скачать результаты'); ?></a>
</p>
