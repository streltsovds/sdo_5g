<?php if (!$this->gridAjaxRequest && $this->calendarAllowed): ?>
<?php // echo $this->headSwitcher(array('module' => 'session', 'controller' => 'event', 'action' => 'list', 'switcher' => 'list'));?>
<?php endif;?>


<?php echo $this->grid ?>

<?php if ($this->switchRole):?>
    <p>
        Если Вы являетесь руководителем подразделения, сейчас произойдёт автоматическое перенаправление в Кабинет руководителя.
    </p>
    <script>
        document.location.href = '/switch/role/<?php echo $this->switchRole;?>';
    </script>
<?php endif;?>
