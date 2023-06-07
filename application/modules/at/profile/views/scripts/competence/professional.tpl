<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<div class="clearfix"></div>
<div class="at-form-report table-criterion">
<form method="POST" action="<?= $this->escape($this->saveUrl) ?>">
<input type="hidden" name="profile_id" value="<?= $this->profile->profile_id?>">
<table>
    <thead><tr>
        <th><?php echo _('Квалификация'); ?></th>
        <th><?php echo _('Уровень успешности'); ?></th>
    </tr></thead>
    <tbody><?php foreach ($this->criteriaValues as $criteriaValue): ?>
        <?php if (count($criteriaValue->criterionTest)) $criterion = $criteriaValue->criterionTest->current(); else continue;?>
        <tr class="quest-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
            <td class="title"><?php echo $criterion->name;?></td>
            <td class="title">
                <input type="text" name="results[<?php echo $criterion->criterion_id;?>]" value="<?php echo $this->results[$criterion->criterion_id];?>"> %
                <?php if (in_array($criterion->criterion_id, $this->error)) echo '<div style="color:red">Пустое или не является числом!</div>';?>
            </td>
        </tr>
    <?php endforeach;?></tbody>
</table>
    <input type="submit" class="ui-button-red table-criterion__submit" value="<?php echo _('Сохранить');?>">
</form>
</div>