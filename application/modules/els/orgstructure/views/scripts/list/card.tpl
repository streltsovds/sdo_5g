<?php echo $this->card(
    $this->subject,
    $this->subject->getCardFields(),
    array(
        'title' => (isset($this->title) ? $this->title : _('Карточка элемента оргструктуры'))
    ));
?>