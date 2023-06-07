<?php if ($this->gridAjaxRequest): ?>
    <div id="grid-ajax">
        <?php // if($this->treeajax == 'true'): ?>
            <?php // echo $this->Actions('orgstructure', array(), array('parent' => $this->orgId));?>
        <?php // endif;?>
        <?php echo $this->grid?>
    </div>
<?php else: ?>
    <div class="orgstructure-list">
        <div id="grid-ajax">
            <?php // echo $this->Actions('orgstructure', array(), array('parent' => $this->orgId));?>
            <?php echo $this->grid?>
            <?php // echo $this->footnote();?>
        </div>
    </div>
<?php endif; ?>