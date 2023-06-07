<?php
    $score = intval($this->score);
?><span class="form-score-binary">
    <span class="hm-score-binary-icon els-icon check <?php if ($score === HM_Scale_Value_ValueModel::VALUE_BINARY_ON): ?>check-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-binary.gif')) ?>">
    </span>
    <input
        type="hidden"
        name="score[<?php echo $this->key; ?>]"
        value="<?php echo $score; ?>">
</span>