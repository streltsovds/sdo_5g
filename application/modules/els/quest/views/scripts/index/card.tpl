<?php $cardId = $this->id('card-inline'); ?>
<div class="pcard pcard_inline" id="<?php echo $this->escape($cardId) ?>">
    <?php echo $this->partial('list/card.tpl', null, array('quest' => $this->quest));?>
</div>