<style>
    .brd {
        border: 1px dotted black; /* Параметры границы */
        background: #fcffd8; /* Цвет фона */
        padding: 10px; /* Поля вокруг текста */
    }
</style>
<?php foreach ($this->reserves as $reserve): ?>
    <?php
        $url = $this->url(array(
            'baseUrl' => 'hr',
            'module' => 'reserve',
            'controller' => 'list',
            'action' => 'print-forms',
            'reserve_id' => $reserve->reserve_id
        ));
    ?>

    <?php
    $urlReserve = $this->url(array(
        'baseUrl' => 'hr',
        'module' => 'reserve',
        'controller' => 'report',
        'action' => 'index',
        'reserve_id' => $reserve->reserve_id
    ));
    ?>
    <div class="reserve-block brd">
        <p>Вы являетесь респондентом в рамках <a href="<?php echo $urlReserve; ?>">программы кадрового резерва</a> <br>Вам необходимо:</p>
        <ul>
            <?php foreach($this->events[$reserve->reserve_id] as $events): ?>
                <?php foreach($events as $event): ?>
                    <li><?php echo $event?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <br>
<?php endforeach; ?>