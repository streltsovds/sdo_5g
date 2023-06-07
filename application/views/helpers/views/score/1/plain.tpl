<?php if (intval($this->score) >= 0):?>
<div class="<?php echo (intval($this->score) >= 0 ? 'plain_score_red' : 'plain_score_gray') ?>">
    <span>
            <?php echo (intval($this->score) >= 0 ? $this->score . '%' : _('нет оценки')) ?>
    </span>
</div>
<?php else:?>
<div class="plain_score_gray">
    <span align="center"><?php echo _('нет оценки');?></span>
</div>
<?php endif?>