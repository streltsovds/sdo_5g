<?php
/** @var HM_Resource_ResourceModel $material */
$material = $this->material;
?>
<div class="hm-material-resource hm-material-download">
    <?php if ($this->externalViewerUrl):?>
        <hm-material-iframe url='<?php echo $this->externalViewerUrl; ?>'></hm-material-iframe>
    <?php else:?>
        <div class="hm-material-download__file-icon">
            <file-icon type='<?php echo $material->filetype; ?>'></file-icon>
        </div>
        <div class="hm-material-download__title">
            <?php echo $material->title; ?>
        </div>
        <div class="hm-material-download__note"
             :style="{ color: colors.grayDarker }"
        >
            {{ _('Данный тип материала не предназначен для отображения в браузере. Вы можете скачать его на локальный компьютер и открыть в соответствующей программе.') }}
        </div>
        <v-btn class="hm-material-download__button white--text"
               :color="colors.buttonDefault"
               href="<?php echo $this->materialContentUrl;?>"
               elevation="0"
               large
               title="Скачать"
        >
            <svg-icon
                name="download"
                color="white"
                stroke-width="0.5"
                style="margin-right: 10px"
                width="21"
            >
            </svg-icon>
            {{ _('Скачать файл') }}
        </v-btn>
    <?php endif;?>
</div>
