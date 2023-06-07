<?php if (empty($this->totalResources)) :?>
    <hm-resources-block
        url='/infoblock/resources/get-data/format/json'
        :types-classification='<?php echo HM_Json::encodeErrorSkip($this->classifiers);?>'
        date-picker-from-label="<?php echo _('c')?>"
        date-picker-to-label="<?php echo _('по');?>"
        :classifier="<?= $this->session->classifier ?>"
        :date-picker-from-value='<?php echo HM_Json::encodeErrorSkip($this->session->from); ?>'
        :date-picker-to-value='<?php echo HM_Json::encodeErrorSkip($this->session->to); ?>'
    ></hm-resources-block>
<?php else:?>
    <v-card-text>
        <v-alert type="info" value="true" outlined>
            <?php echo sprintf(_('Всего ресурсов в базе: %s'), $this->totalResources)?><br>
            <?php echo _('Классификаторы ресурсов не созданы');?>
        </v-alert>
    </v-card-text>
<?php endif;?>
<hm-actions-download url="<?php echo $this->exportUrl; ?>"/>