<?php if ($this->mode != 'template'): ?>
<?php if (isset($this->key)): ?>
<li id="<?php echo $this->escape("coach-${$this->listId}-{$this->key}"); ?>"
        <?php if ($this->total == $this->index && $this->readonly): ?>class="last"<?php endif; ?>
        data-dt-key="<?php echo $this->escape($this->key); ?>">
    <span class="title<?php if ($this->readonly && $this->isCustom): ?> custom<?php endif;?>"><?php echo $this->title; ?><?php if (!$this->readonly): ?> <a href="#" class="remove-criteria">[x]</a><?php endif; ?></span>
    <span class="els-icon check <?php if ($this->value === HM_At_Session_Event_Method_FieldModel::SCALE_VALUE_POSITIVE): ?>check-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/forms/field-training/btt.gif')) ?>">
    </span>
    <span class="els-icon cross <?php if ($this->value === HM_At_Session_Event_Method_FieldModel::SCALE_VALUE_NEGATIVE): ?>cross-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/forms/field-training/btt.gif')) ?>">
    </span>
    <?php if (!$this->readonly): ?>
    <input type="hidden" class="criteria-value" name="criteria[<?php echo $this->key ?>]" value="<?php echo $this->value; ?>">
    <?php endif; // !$this->readonly ?>
</li>
<?php else:  // isset($this->key) ?>
<li class="added-criteria new-criteria <?php if ($this->index == 3): ?> last<?php endif; ?>">
    <input type="hidden" name="extra_criteria[][criterion_id]" disabled value="<?php echo $this->escape($this->criterionId) ?>">
    <span class="title"><span class="input-wrapper"><input type="text" class="criteria-title" placeholder="<?php echo _('Введите название критерия'); ?>" name="extra_criteria[][title]" value=""></span><?php if (!$this->readonly): ?> <a href="#" class="remove-criteria">[x]</a><?php endif; ?></span>
    <span class="els-icon check"><img src="<?php echo $this->escape($this->serverUrl('/images/forms/field-training/btt.gif')) ?>"></span>
    <span class="els-icon cross"><img src="<?php echo $this->escape($this->serverUrl('/images/forms/field-training/btt.gif')) ?>"></span>
    <?php if (!$this->readonly): ?>
    <input type="hidden" class="criteria-value" name="extra_criteria[][value]" disabled value="">
    <?php endif; // !$this->readonly ?>
</li>
<?php endif  // isset($this->key) ?>
<?php else:  // $this->mode != 'template' ?>
<li id="coach-<%- listId %>-<%- key %>" data-dt-key="<%- key %>">
    <span class="title"><%= title %><?php if (!$this->readonly): ?> <a href="#" class="remove-criteria">[x]</a><?php endif; ?></span>
    <span class="els-icon check"><img src="<?php echo $this->escape($this->serverUrl('/images/forms/field-training/btt.gif')) ?>"></span>
    <span class="els-icon cross"><img src="<?php echo $this->escape($this->serverUrl('/images/forms/field-training/btt.gif')) ?>"></span>
    <?php if (!$this->readonly): ?><input type="hidden" class="criteria-value" name="criteria[<%- key %>]" value=""><?php endif; // !$this->readonly ?>

</li><?php endif; // $this->mode != 'template' ?>