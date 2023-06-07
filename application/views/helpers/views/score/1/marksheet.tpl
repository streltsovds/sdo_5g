<input
    type="text"
    tabindex="<?php echo $this->tabindex;?>"
    value="<?php echo ((intval($this->score) >= 0 && $this->score !== null) ? $this->score : '') ?>"
    pattern="^[1-9]{1}\d?$|^0$|^100$"
    data-target="<?php echo $this->key; ?>"
    class="hm_score_numeric">
<input 
  type="hidden"
  id="<?php echo $this->key;?>"
  name="score[<?php echo $this->key; ?>]"
  value="<?php echo $this->score; ?>"
  pattern="^[1-9]{1}\d?$|^0$|^100$|^-1$"
>