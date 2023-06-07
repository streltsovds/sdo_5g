<?php echo $this->card(
    $this->resource,
    array(
//        'title' => _('Название'),
        'getType()' => _('Тип'),
        'description' => _('Краткое описание'),
    ),
    array(
        'title' => _('Карточка информационного ресурса')
    ));
?>