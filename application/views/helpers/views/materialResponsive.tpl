<hm-material-responsive
    v-model="appContentFullscreen"
    :max-height="appComputedMaterialMaxHeight"
    :min-height="appComputedMaterialMinHeight"
    v-bind='<?php echo $this->propsJson ?>'
>
    <?php echo $this->content ?>
</hm-material-responsive>
