<?php $id = $this->id('fe');  ?>
<div class="hm-action hm-action-select">
    <label for="<?= $id ?>"><?= $this->textDesc; ?>:</label><form name='workflow-form-select'>
        <?= $this->formSelect($this->selectName, $this->value, array('class' => 'workflow-select', 'id' => $id, 'data-important' => ($this->important == true)? 'true' : 'false'), array(0 => _('Выберите')) +  $this->values); ?>
        <input type="hidden" name="names[]" value="<?= $this->selectName; ?>">
<?php if (count($this->params)): ?>		
<?php foreach ($this->params as $key => $value): ?>		
<input type="hidden" name="<?= $key;?>" value="<?= $value;?>">
<?php endforeach;?>
<?php endif;?>
        <!--input type="hidden" name="state_id" value="<?= $this->stateId; ?>">
        <input type="hidden" name="forState" value="<?= $this->forState; ?>"-->
    </form>
</div>
