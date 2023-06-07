<?php
$fields = [
    ['column' => 'vacancy_name', 'title' => _('Сессия подбора'), 'html' => true],
    ['column' => 'name', 'title' => _('Название мероприятия'), 'html' => true],
    ['column' => 'date_begin', 'title' => _('Дата начала')],
    ['column' => 'date_end', 'title' => _('Дата окончания')],
    ['column' => 'candidate_name', 'title' => _('ФИО кандидата'), 'html' => true],
    ['column' => 'candidate_phone', 'title' => _('Телефон')],
    ['column' => 'candidate_email', 'title' => _('E-Mail')]
];
?>
<v-card-text>
    <hm-my-events-block
            value="<?php echo $this->date?>"
            date-picker-label="<?php echo _('Мероприятия по сессиям подбора на дату: '); ?>"
            url="/infoblock/my-events/index/events_date/"
            empty-response="<?php echo _('Отсутствуют данные для отображения'); ?>"
            :fields='<?php echo HM_Json::encodeErrorSkip($fields); ?>'
    ></hm-my-events-block>
</v-card-text>