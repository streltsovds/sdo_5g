<?php if (!$this->gridAjaxRequest): ?>
    <?php echo $this->Actions('gridMenu', $this->gridMenuActions); ?>

    <div class="subject-catalog-list">
<?php endif; ?>

<?php echo $this->grid?>

<?php if (!$this->gridAjaxRequest):?>
    </div>
    <?php echo $this->footnote();?>
<?php endif;?>