<hm-claims url="/infoblock/claims/get-data/format/json"
           :periods='<?php echo HM_Json::encodeErrorSkip($this->periods); ?>'
           period="<?php echo $this->period; ?>"
           url-claims-list="<?php echo $this->url(array('module' => 'order', 'controller' => 'list')); ?>"
>
    <template slot="totalLabel"><?php echo _('Поступило заявок:'); ?> </template>
    <template slot="undoneLabel"><?php echo _('не обработано:'); ?> </template>
</hm-claims>
<hm-actions-download url="<?php echo $this->exportUrl; ?>"/>