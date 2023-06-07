<hm-activity-block
        url='/infoblock/activity/get-data/format/json'
        :courses='<?php echo htmlspecialchars(HM_Json::encodeErrorSkip($this->courses), ENT_QUOTES, 'UTF-8'); ?>'
        :users='<?php echo HM_Json::encodeErrorSkip($this->users);?>'
        :types='<?php echo HM_Json::encodeErrorSkip($this->types);?>'
        type='<?php echo $this->type; ?>'
        :periods='<?php echo HM_Json::encodeErrorSkip($this->periods);?>'
        period='<?php echo $this->period; ?>'
></hm-activity-block>
<hm-actions-download url="<?php echo $this->export_url; ?>"/>