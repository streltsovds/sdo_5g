<hm-card-link
    title="<?php echo ($this->title); ?>"
    url="<?php echo $this->url; ?>"
    rel="<?php echo $this->escape($this->rel) ?>"
    float="<?php echo $this->float ?>"
<?php if ($this->textVueColor): ?>
    :text-color="<?php echo $this->escape($this->textVueColor) ?>"
<?php endif; ?>
<?php if ($this->class): ?>
    class="<?php echo $this->class ?>"
<?php endif; ?>
    @click.native.stop
>
    <?php echo $this->content; ?>
</hm-card-link>