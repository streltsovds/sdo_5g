<?php
    $cssClasses = [];

    if ($this->material->edit_type == HM_Resource_ResourceModel::EDIT_TYPE_WYSIWYG) {
        $cssClasses[] = 'hm-material-html-wysiwyg';
    }

    $cssClassesString = implode(' ', $cssClasses)
?>
<hm-material-html
    class="<?php echo $cssClassesString; ?>"
>
    <template slot="content">
        <?php echo $this->material->content;?>
    </template>
</hm-material-html>



