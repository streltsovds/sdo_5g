<?php $reservePositions = Zend_Registry::get('serviceContainer')->getService('HrReservePosition')->fetchAll(array('in_slider = ?' => 1), 'name'); ?>
<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<select name="results[<?php echo $this->question->question_id;?>][]">
    <option value="0">---выберите должность кадрового резерва---</option>
    <?php foreach ($reservePositions as $reservePosition):?>
        <option value="<?php echo $reservePosition->reserve_position_id; ?>"
            <?php echo ($reservePosition->reserve_position_id == $this->result[0]) ? 'selected' : ''; ?> >
            <?php echo $reservePosition->name; ?>
        </option>
    <?php endforeach;?>
</select>