<hm-chart
        :data='<?php echo HM_Json::encodeErrorSkip($this->data);?>'
        type='<?php echo $this->options["type"];?>'
        :options='<?php echo json_encode($this->options);?>'
        data-value='<?php echo $this->options["dataValue"];?>'
></hm-chart>
