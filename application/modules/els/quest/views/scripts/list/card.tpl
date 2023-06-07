<img src="<?php echo Zend_Registry::get('config')->url->base . 'images/subject-icons/test.png';?>" alt="<?php echo $this->escape($this->quest->name)?>" align="left" style="margin-right: 20px;"/>
<?php
    echo $this->card(
        $this->quest,
        array(
            'getType()' => _('Тип'),
            'description' => _('Краткое описание'),
        ),
        array('title' => _('Карточка'))
    );
?>
