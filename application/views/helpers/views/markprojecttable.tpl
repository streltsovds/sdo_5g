<?php
    $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css'));
    $this->headScript()->appendFile($this->baseUrl('js/application/marksheet/index/index/script.js'));
    $this->headScript()->appendFile($this->baseUrl('/js/lib/jquery/datefilter.js'));
    $js = "
	$('#from,#to').dfilter({
		startDate: '-3y',
		endDate: '+3y',
		fillFrom: '.date-from',
		fillTo: '.date-to',
		descFrom: '"._('от')."',
		descTo: '"._('до')."'
	});
    ";
    $this->jQuery()->addOnload($js);
?>

<form id="marksheet-form-filters" method="POST">
<div class="filter_wrap <?php if (!count($this->persons)):?>filter_wrap_nodata<?php endif; ?>">
	<div class="dateFilter classFWrap">
		<div class="filter_desc"><?php echo _('Фильтр по дате:'); ?></div>
		<div class="filterContent">
			<input type="hidden" name="from" id="from" value="<?php echo $this->dates['from'] ?>" disabled>
			<input type="hidden" name="to" id="to" value="<?php echo $this->dates['to'] ?>" disabled>
			<!-- <div class="current-filter-value"><strong><?php echo _("Активный фильтр") ?></strong>:
				<?php echo _('от') ?>
				<?php if ($this->dates['from']): ?>
				<?php echo $this->dates['from'] ?>
				<?php else: ?>
				-&#8734;
				<?php endif; ?>
				<?php echo _('до') ?>
				<?php if ($this->dates['to']): ?>
				<?php echo $this->dates['to'] ?>
				<?php else: ?>
				+&#8734;
				<?php endif; ?>
			</div> -->
		</div>
	</div>
	<div class="filterSubmit classFWrap">
		<button class="dateFilter">Фильтровать</button>
	</div>
</div>
</form>

<?php if (!count($this->persons)):?>
    <!-- TODO: Это вынести в отдельный файл при стайлинге -->
    <div style="padding:10px;text-align:center;color:brown;font-size:14px;"><?php echo _('Отсутствуют данные для отображения');?></div>
<?php else:?>
<?php $totalSchedules = count($this->schedules); ?>
<?php $totalPersons = count($this->persons); ?>

<form id="marksheet-form" method="POST" action="<?php echo $this->escape( $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score')) );?>">
<table id="marksheet" class="main-grid" cellspacing="0" data-schedules="<?php echo $totalSchedules;?>" data-persons="<?php echo $totalPersons;?>">
    <colgroup><col><col><col span="<?php echo $totalSchedules + 1;?>"></colgroup>
    <thead>
        <tr class="marksheet-labels">
            <!-- TODO: Убрать в нужное место кнопулю комментирования -->
            <td class="first-cell cell430" colspan="2"></td>
            <?php foreach($this->schedules as $key => $schedule):?>
            <?php $project_id = $schedule->CID;?>
            <td class="meeting-cell score-cell">
                <?php if ($schedule->getResultsUrl()):?>
                <a title="<?php echo _('Подробная статистика')?>" href="<?php echo $schedule->getResultsUrl();?>">
                <?php endif;?>
                    <img src="<?php echo $schedule->getIcon();?>" alt="<?php echo $this->escape($schedule->title)?>" title="<?php echo $this->escape($schedule->title)?>">
                    <div class="hm-marksheet-table-header-item-title" title="<?php echo $this->escape($schedule->title)?>"><?php echo $this->escape($schedule->title)?></div>
                <?php if ($schedule->getResultsUrl()):?>
                </a>
                <?php endif;?>
            </td>
            <?php endforeach;?>
            <td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Место") ?></span></td>
        </tr>
        <tr class="marksheet-head">
            <th class="marksheet-rowcheckbox first-cell"><?php echo $this->formCheckbox('')?></th>
            <th class="fio-cell"><?php echo _('ФИО');?></th>
            <?php foreach($this->schedules as $key => $schedule):?>
            <td class="meeting-cell score-cell"><input id="schedule_<?php echo $schedule->meeting_id?>" tabindex="0" type="checkbox" name="schedule[<?php echo $schedule->meeting_id;?>]" value="1"></td>
            <?php endforeach;?>
            <td class="total-cell last-cell score-cell"><input tabindex="0" type="checkbox" name="total"></td>
        </tr>
    </thead>
    <tbody>
        <?php
        $temp1 = 1;
        foreach($this->persons as $key => $person):?>
        <tr class="<?php echo ($temp1 % 2 == 0) ? "even" : "odd"; if ($temp1 == 1) { echo " first-row"; } else if ($temp1 == $totalPersons) { echo " last-row"; } ?>">
            <td class="marksheet-rowcheckbox first-cell"><input tabindex="0" type="checkbox" name="person[<?php echo $key;?>]" value="1"></td>
            <td class="fio-cell cell430">
            <?php echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => $person->MID)),_('Карточка пользователя'));?>
            <?php echo $this->escape($person->getName());?>
            </td>
            <?php
            $temp = 1;
            foreach($this->schedules as $schedule):?>
            <td class="score-cell meeting-cell<?php if(!isset($this->scores[$key.'_'.$schedule->meeting_id])):?> no-score<?endif;?>"><div>
            <?php if (isset($this->scores[$key.'_'.$schedule->meeting_id])):?>
                <?php echo $this->score(array(
                    'score' => $this->scores[$key.'_'.$schedule->meeting_id]->V_STATUS,
                    'user_id' => $key,
                    'lesson_id' => $schedule->meeting_id,
                    'scale_id' => $schedule->getScale(),
                    'mode' => HM_View_Helper_Score::MODE_MARKSHEET,
                    'tabindex' => $temp . '00' . $temp1
                ));?>
                <?php endif;?>
                <?php if(strlen($this->scores[$key.'_'.$schedule->meeting_id]->comments)):?>
                <div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_'.$schedule->meeting_id]->comments);?>"></div>
                <?php endif;?>
            </div></td>
            <?php $temp++;
            endforeach;?>
            <td class="score-cell total-cell last-cell">
                <div>
                <?php echo $this->score(array(
                    'score' => $this->scores[$key.'_total']['mark'],
                    'user_id' => $key,
                    'lesson_id' => 'total',
                    'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                    'mode' => HM_View_Helper_Score::MODE_MARKSHEET,
                    'tabindex' => $temp . '00' . $temp1
                ));?>
                <?php if(strlen($this->scores[$key.'_total']['comment'])):?>
                <div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_total']['comment']);?>"></div>
                <?php endif;?>
                </div>
            </td>
            <?php $temp++;?>
        </tr>
        <?php $temp1++;
        endforeach;?>
        <tr class="last-row ui-helper-hidden">
            <td class="first-cell" colspan="2"></td>
            <td class="slider-cell" colspan="<?php echo $totalSchedules; ?>"><div id="marksheet-slider"></div></td>
            <td class="last-cell"></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="<?php echo $totalSchedules + 3; ?>">
                <table cellspacing="0">
                    <colgroup><col width="1"><col width="1"><col width="1"><col width="*"></colgroup>
                    <tr class="first-row last-row">
                        <td class="first-cell">
                            <?php //echo _('Действия');?>:
                        </td>
                        <td>
                            <?php //echo $this->formSelect('scheduleMassAction', 'none', '', array('none' => _('Не выбрано'), $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'clear-schedule')) => _('Очистить оценки')));?>
                        </td>
                        <td class="button-cell">
                            <?php //echo $this->formButton('ParticipantButton', _('Выполнить'), '');?>
                        </td>
                        <td class="last-cell" rowspan="2">
                            <?php echo $this->formButton('commentButton', _('Добавить комментарий'), array('disabled' => 'disabled'));?>
                            <?php echo $this->formButton(
                                                'printButton',
                                                _('Распечатать'),
                                                array('onClick' => 'window.window.open("'.$this->serverUrl($this->url(array(
                                                        'module' => 'markproject',
                                                        'controller' => 'index',
                                                        'action' => 'print',
                                                        'project_id' => $this->projectId
                                                        ))).'"
                                                )')
                            );?>
                            <?php echo $this->formButton(
                                                'excelButton',
                                                _('Excel'),
                                                array('onClick' => 'window.open("'.$this->serverUrl($this->url(array(
                                                        'module' => 'markproject',
                                                        'controller' => 'index',
                                                        'action' => 'excel',
                                                        'project_id' => $this->projectId
                                                        ))).'"
                                                )')
                            );?>
                            <?php echo $this->formButton(
                                                'wordButton',
                                                _('Word'),
                                                array('onClick' => 'window.open("'.$this->serverUrl($this->url(array(
                                                        'module' => 'markproject',
                                                        'controller' => 'index',
                                                        'action' => 'word',
                                                        'project_id' => $this->projectId
                                                        ))).'"
                                                )')
                            );?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<div id="marksheet-comment-dialog" title="<?php echo _("Комментарии"); ?>">
    <div class="textarea-wrapper"><textarea id="textComment" name="comment"></textarea></div>
</div>


<?php
$this->inlineScript()->captureStart();
?>
initMarksheet({
    url: {
        comments: "<?php echo $this->url(array('module' =>'markproject', 'controller' => 'index', 'action' => 'set-comment'));?>",
        score: "<?php echo $this->url(array('module' =>'markproject', 'controller' => 'index', 'action' => 'set-score'));?>"
    },
    l10n: {
        save: "<?php echo _("Сохранить"); ?>",
        noParticipantActionSelected: "<?php echo _("Не выбрано ни одного действия с участником"); ?>",
        noParticipantSelected: "<?php echo _("Не выбрано ни одного участника"); ?>",
        noMeetingActionSelected: "<?php echo _("Не выбрано ни одного действия с мероприятием"); ?>",
        noMeetingSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
        formError: "<?php echo _("Ошибка формы") ?>",
        ok: "<?php echo _("Хорошо"); ?>",
        confirm: "<?php echo _("Подтверждение"); ?>",
        areUShure: "<?php echo _("Данное действие может быть необратимым. Вы действительно хотите продолжить?"); ?>",
        yes: "<?php echo _("Да"); ?>",
        no: "<?php echo _("Нет"); ?>"
    }
});
<?php
$this->inlineScript()->captureEnd();
?>

<?php endif;?>