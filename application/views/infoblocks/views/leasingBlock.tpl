<hm-leasing
        :resourses='<?php echo HM_Json::encodeErrorSkip($this->resources); ?>'
        selected="<?php echo $this->selected; ?>"
        url="<?php echo $this->loadUrl; ?>"
        label='<?php echo _("Использование ресурсов:")?>'
>
</hm-leasing>