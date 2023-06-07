<hm-rating-block
        :items='<?php echo HM_Json::encodeErrorSkip($this->report); ?>'
        :votes-label='<?php echo HM_Json::encodeErrorSkip([_("голос"), _("голоса"), _("голосов")]);?>'>
</hm-rating-block>
