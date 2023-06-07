<?php $id = $this->id('fe'); ?>
<div class="hm-action hm-action-read hm-action-area">
    <label for="<?= $id ?>"><?= $this->title; ?>:</label><form name="workflow-form-area">
        <span class="<?php if ($this->value != null): ?>has-data<?php else: ?>no-data<?php endif; ?>"><?=
            ($this->value != null) ? $this->value : _('[нет данных]');
        ?></span>
        <input type="hidden" name="names[]" value="<?= $this->selectName; ?>">
        <input type="hidden" name="state_id" value="<?= $this->stateId; ?>">
        <input type="hidden" name="forState" value="<?= $this->forState; ?>">
    </form>
</div>