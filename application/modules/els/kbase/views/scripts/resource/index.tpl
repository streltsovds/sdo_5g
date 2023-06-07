<?php if ($this->resource): ?>
    <?php echo $this->materialResponsive($this->resource); ?>
<?php else: ?>
    <?php
       /**
        * TODO вынести внутрь компонента
        *   или сделать красивое Vue-сообщение
        */
        echo _('Не найден материал');
    ?>
<?php endif; ?>