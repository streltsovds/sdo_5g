<hm-report-designer
    :report-domain='<?php echo HM_Json::encodeErrorThrow($this->domain); ?>'
    :report-id='<?php echo Zend_Json::encode($this->reportId); ?>'
    :report-fields-in-table='<?php echo HM_Json::encodeErrorThrow($this->dataFields); ?>'
    :report-fields='<?php echo HM_Json::encodeErrorThrow($this->fields['categories']); ?>'
/>