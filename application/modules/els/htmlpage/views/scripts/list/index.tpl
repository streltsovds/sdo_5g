<?php if ($this->gridAjaxRequest): ?>
    <div id="grid-ajax">
        <?php echo $this->grid; ?>
    </div>
<?php else: ?>
    <div class="orgstructure-list">
        <div id="grid-ajax">
            <?php echo $this->grid; ?>
        </div>
    </div>
<?php endif;
