<?php if ($this->material): ?>

<div id="hm-training-modules-viewer"></div>
<?= $this->proctoringStudent($this->lesson->SHEID); ?>

    <?php echo $this->materialResponsive($this->material, $this->lesson); ?>
<?php else: ?>
    <?php
    /**
     * TODO вынести внутрь компонента
     *   или сделать красивое Vue-сообщение
     */
    echo _('Не найден материал занятия');
    ?>
<?php endif; ?>
