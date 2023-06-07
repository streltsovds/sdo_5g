<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<div class="at-form-report table-competence">
<?php if ($this->success === true):?>
<?php echo $this->notifications(array(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Требования к компетенциям профиля успешно сохранены'))), array('html' => true));?>
<?php elseif($this->success === false):?>
<?php echo $this->notifications(array(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Внимание! Не все значения отмечены!'))), array('html' => true));?>
<?php endif; ?>
<v-card>
<form style="padding: 16px;" method="POST" action="<?= $this->escape($this->saveUrl) ?>">
<input type="hidden" name="profile_id" value="<?= $this->profile->profile_id?>">
<table>
    <thead><tr>
        <th><?php echo _('Компетенция'); ?></th>
        <?php foreach ($this->scale->scaleValues as $value): ?>
        <?php if ($value->value == HM_Scale_Value_ValueModel::VALUE_NA) continue; ?>
            <th>
                <?php echo !empty($value->text) ? $value->text : $value->value;?>
            </th>
        <?php endforeach;?>
    </tr></thead>
    <tbody><?php foreach ($this->criteriaValues as $criteriaValue): ?>
        <?php if (count($criteriaValue->criterion)) $criterion = $criteriaValue->criterion->current(); else continue;?>
        <tr class="quest-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
            <td class="title"><?php echo $criterion->name;?></td>
            <?php foreach ($this->scale->scaleValues as $value): ?>
            <?php if ($value->value == HM_Scale_Value_ValueModel::VALUE_NA) continue; ?>
                <td class="value">
                    <input id="results[<?php echo $criterion->criterion_id;?>]-<?php echo $value->value_id;?>" type="radio" class="quest-answer" name="results[<?php echo $criterion->criterion_id;?>]" value="<?php echo $value->value_id;?>" <?php if (isset($this->results[$criterion->criterion_id]) && ($this->results[$criterion->criterion_id] == $value->value_id)) :?>checked<?php endif;?>>
                    <label for="results[<?php echo $criterion->criterion_id;?>]-<?php echo $value->value_id;?>">
                        <svg data-v-15e7e7c4="" viewBox="0 0 24 18" data-debug-icon-name="Checkmark" xmlns="http://www.w3.org/2000/svg" class="svg-icon" style="width: 34px; height: 25.5px; vertical-align: middle; overflow: visible;">
                            <path d="M20.1626 0.699285L8.84043 12.8047L3.9271 6.80082C3.20271 5.91406 1.83696 5.73825 0.874067 6.40026C-0.0910516 7.06227 -0.287042 8.31629 0.437105 9.19898L6.98236 17.1989C7.37854 17.6851 7.99444 17.978 8.65705 17.9977C8.68052 18 8.704 18 8.72748 18C9.36241 18 9.96966 17.746 10.3829 17.3008L23.4734 3.30089C24.2596 2.46284 24.1531 1.20135 23.2369 0.482693C22.3249 -0.236188 20.9468 -0.142392 20.1626 0.699285Z"></path></svg>
                    </label>
                </td>
            <?php endforeach;?>
        </tr>
    <?php endforeach;?></tbody>
</table>
<input type="submit" class="ui-button-red table-competence__submit" value="<?php echo _('Сохранить');?>">
</form>
</v-card>
</div>