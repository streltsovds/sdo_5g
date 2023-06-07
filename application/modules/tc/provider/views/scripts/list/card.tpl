<?php
    echo $this->card(
        $this->provider,
        array_merge(
            array('name' => _('Название')),
            strlen($this->provider->description) ?
                array('getDescription()' => _('Краткое описание')) : array(),
            array('getCities(true)'       => _('Город'))
        ),
        array(
        'title' => _('Карточка провайдера'),
        'noico' => true
        )
    );
?>

