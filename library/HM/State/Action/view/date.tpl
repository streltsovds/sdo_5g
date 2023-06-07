<?php $id = $this->id('fe'); ?>
<div class="hm-action hm-action-date">
    <label for="<?= $id ?>"><?= $this->title; ?>:</label><form name="workflow-form-select">
        <?= $this->datePicker($this->id, $this->value, array(), array('class' => 'workflow-input', 'id' => $id)) ?>
        <input type="hidden" name="names[]" value="<?= $this->selectName; ?>">
        <input type="hidden" name="state_id" value="<?= $this->stateId; ?>">
        <input type="hidden" name="forState" value="<?= $this->forState; ?>">
    </form>
</div>