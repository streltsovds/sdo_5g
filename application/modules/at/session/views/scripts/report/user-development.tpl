<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print'); ?>
<div class="at-form-report">

    <?php if ($this->status != HM_At_Session_User_UserModel::STATUS_COMPLETED): ?>
    <div class="attention"><?php echo _('ВНИМАНИЕ! Пользователь ещё не прошел оценку, результаты предварительные');?></div>
    <?php endif;?>

    <v-card class="chart-container">
        <?php echo $this->chartJS(
            $this->analyticsChartData['data'],
            $this->analyticsChartData['graphs'],
            array(
                'id' => 'development',
                'colors' => ['#003F7E','#C759D2'],
                'type' => 'apexbar',
                'width'  => 1100,
                'height' => 400,
            )
        );?>
    </v-card>

    <div><?php echo $this->form;?></div>
    <v-card class="at-form-report__table-wrapper">
        <form method="post" id="assign-subjects" name="assign-subjects" action="<?php echo $this->serverUrl($this->url(array('action' => 'assign-subjects',)))?>">
            <table>
                <tr>
                    <th><?php echo _('Компетенция'); ?></th>
                    <th><?php echo _('Мероприятия по развитию'); ?></th>
                    <th><?php echo _('Комментарий'); ?></th>
                </tr>
                <?php foreach ($this->subjectsByCompetences as $competence => $data): ?>
                    <tr>
                        <td><?php echo $competence; ?></td>
                        <td>
                            <div class="at-form-report__table-td-wrapper">
                                <?php foreach ($data['subjects'] as $subjectId => $subject): ?>
                                    <div class="at-form-report__table-row-input">
                                        <input type="checkbox" id="subjects[]" name="subjects[]" value="<?php echo $subjectId; ?>">
                                        <label for="subjects[]"><?php echo $subject; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div class="at-form-report__table-td-wrapper">
                                <?php foreach ($data['comments'] as $subjectId => $comment): ?>
                                    <p class="at-form-report__table-row"><?php echo $comment; ?></p>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" class="v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--large primary">
                <?php echo _('Назначить'); ?>
            </button>
        </form>
    </v-card>
</div>
