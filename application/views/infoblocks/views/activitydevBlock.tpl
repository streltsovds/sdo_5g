<hm-activity-dev-block
        url='/infoblock/activitydev/get-data/format/json'
        :activity-distributions='<?php echo HM_Json::encodeErrorSkip($this->activityDistributions);?>'
        activity-distribution='<?php echo $this->type; ?>'
        :periods='<?php echo HM_Json::encodeErrorSkip($this->periods);?>'
        period='<?php echo $this->period; ?>'
></hm-activity-dev-block>
<hm-actions-download url="<?php echo $this->export_url; ?>"/>
