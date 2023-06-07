<v-card-text>
    <hm-screencast-block
            label='<?php echo _("Выберите ролик"); ?>'
            :screencasts='<?php echo HM_Json::encodeErrorSkip($this->screencasts); ?>'
            selected='<?php echo $this->screencast; ?>'
    >
    </hm-screencast-block>
</v-card-text>