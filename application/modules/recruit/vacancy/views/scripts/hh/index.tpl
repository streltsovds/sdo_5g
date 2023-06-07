<script>
    $( document ).ajaxComplete(function () {
        $( "div.ui-multiselect.ui-helper-clearfix.ui-widget" ).css({ "width": "97%", "height": 500 });
        $( "div.available.container" ).css({ "width": "49.5%", "height" : "100%" });
        $( "div.selected.container"  ).css({ "width": "49.5%", "height" : "100%" });
        $( "ul.available.connected-list" ).css({ "width": "99.7%", "height" : "87%" });
        $( "ul.selected.connected-list"  ).css({ "width": "99.7%", "height" : "87%" });
        $( "table#description_tbl"  ).css({ "width": "98%" });
        $( "iframe#description_ifr" ).css({ "width": "98%", "height" : 500 });
    });
</script>
<?php if (!$this->hh_vacancy_id) : ?>
<?php if (0 && 'development' == APPLICATION_ENV || Zend_Registry::get('config')->debug->on): ?>
<div style="border: 1px dotted black; margin: 10px; padding: 10px;">
    Статус авторизации на HH: <?php echo ($this->connectionInfo['user_id'] ? _('авторизован') : _('ошибка, не удалось авторизоваться')); ?><br>
    ID клиента: <?php echo $this->connectionInfo['user_id']; ?><br>
    <br>Служебная информация:
    <div style="margin-left: 10px;"><?php echo implode('<br>', $this->log) ?></div><br>
    <br>Потрачено времени на запросы к серверам HH: <?php echo round($this->time, 2) ?> секунд
    <br>Подробнее:
    <div style="margin-left: 10px;"><?php 
        foreach ($this->times as $time) {
            echo $time['name'].': '.round($time['value'], 2).' с.<br>';
        } 
    ?>
    </div>
</div>
<?php endif;?>
<?php echo $this->form;?>
<?php endif;?>