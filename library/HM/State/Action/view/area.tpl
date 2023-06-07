<?php $id = $this->id('fe'); ?>
<div class="hm-action hm-action-area">
    <label for="<?= $id ?>"><?= $this->title; ?>:</label><form name="workflow-form-area">
        <?= $this->formTextarea($this->id, $this->value, array('class' => 'workflow-area', 'id' => $id, 'rows' => 4)) ?>
        <input type="hidden" name="names[]" value="<?= $this->selectName; ?>">
        <input type="hidden" name="state_id" value="<?= $this->stateId; ?>">
        <input type="hidden" name="forState" value="<?= $this->forState; ?>">
    </form>
</div>