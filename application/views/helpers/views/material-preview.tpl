<hm-material-preview url='<?php echo $this->url;?>'
                     icon-class='<?php echo $this->material->getIconClass(); ?>'
                     title='<?php echo $this->title ?>'
                     stats-url='<?= $this->statsUrl ?>'
                     server-url='<?= $this->serverUrl("/images/content-modules/course-index/stats.png") ?>'
                     :actions='<?php echo json_encode($this->actions, JSON_FORCE_OBJECT)?>'
                     description='<?= nl2br($this->escape($this->description)) ?>'
                     :rating='<?php echo json_encode($this->rating) ?>'
                     :classifiers='<?php echo json_encode($this->classifiers, JSON_FORCE_OBJECT) ?>'
                     :tags='<?php echo json_encode($this->tags) ?>'
>
<template slot="resource">
    <?php
        if ($helperPreviewName = $this->helperName) {
            echo $this->$helperPreviewName($this->material, $this->lesson);
        }
    ?>
</template>

</hm-material-preview>


