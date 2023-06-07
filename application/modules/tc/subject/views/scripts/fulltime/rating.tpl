<?php
    echo $this->card(
    $this->subject,
    array(
        'rating'             => _('Рейтинг'),
        'graduated'          => _('Прошедших обучение, чел'),
        'effectivity'        => _('Средний показатель эффективности'),
        'feedback'           => _('Средний балл отзыва'),
    ),
    array(
        'noico' => true
        )
    );
?>
