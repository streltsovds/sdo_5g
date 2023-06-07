<?php if(intval($this->score)>=0  && $this->score !== null ):?>
<div class="<?php echo ((intval($this->score) >= 0 && $this->score !== null) ? 'score_red' : 'score_gray') ?> number_number">
    <span>
        <input
            tabindex="<?php echo $this->tabindex;?>"
            data-target="<?php echo $this->key; ?>"
            type="text"
            placeholder="<?php echo $this->placeholder ?>"
            value="<?php echo (intval($this->score) != -1 ? $this->score : '') ?>"
            pattern="^[1-9]{1}\d?$|^0$|^100$"
            class="hm_score_numeric"
            <?php echo $this->disabled ? 'disabled' : '' ?>
        >
        <?php if(strlen($this->comments)):?>
        <div class="score-comments" title="<?php echo $this->escape($this->comments);?>"></div>
        <?php endif;?>
    </span>
</div>
<?php else:?>
<div class="score_gray number_number">
    <span align="center">
        <input
            tabindex="8001"
            data-target="<?php echo $this->key; ?>"
            type="text"
            placeholder="<?php echo $this->placeholder ?>"
            pattern="^[1-9]{1}\d?$|^0$|^100$"
            class="hm_score_numeric"
            <?php echo $this->disabled ? 'disabled' : '' ?>
        >
    </span>
</div> 
<?php endif?>
<input 
  type="hidden"
  id="<?php echo $this->key; ?>"
  name="score[<?php echo $this->key; ?>]"
  value="<?php echo $this->score; ?>"
  pattern="^[1-9]{1}\d?$|^0$|^100$|^-1$"
>