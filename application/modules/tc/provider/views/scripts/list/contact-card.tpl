<?php
    echo $this->card(
        $this->contact,
        array(
            'name' => _('ФИО'),
            'position' => _('Должность'),
            'phone' => _('Телефон'),
            'email' => _('E-mail')
        ),
        array(
        'title' => _('Карточка контактного лица'),
        'noico' => true
        )
    );
echo $this->reportList($this->data);?>