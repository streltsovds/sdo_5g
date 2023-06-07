<?php
    switch($this->programm->programm_type) {
        case HM_Programm_ProgrammModel::TYPE_RECRUIT: $title = _('Все методики подбора'); break;
        case HM_Programm_ProgrammModel::TYPE_RESERVE: $title = _('Все методики оценки КР'); break;
        default: $title = _('Все методики оценки');
    }
?>
<v-card>
    <?php if (!$this->editable): ?>
        <v-card-text>
            <v-alert type="warning" outlined value="true">
                <?php echo sprintf(_('Невозможно отредактировать программу, так как в настоящий момент её проходят пользователи (%s)'), count($this->processes));?>
            </v-alert>
        </v-card-text>
    <?php endif; ?>
    <hm-programm-builder
            :all-items='<?php echo json_encode($this->items, JSON_FORCE_OBJECT); ?>'
            :include-items='<?php echo json_encode($this->events, JSON_FORCE_OBJECT); ?>'
            :hidden-items='<?php echo json_encode($this->eventsHidden); ?>'
            save-url="<?php echo $this->serverUrl($this->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'assign', 'programm_id' => $this->programm->programm_id)))?>"
            edit-url="<?php echo $this->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'edit', 'baseUrl' => '', 'submethod' => ''));?>"
            :options='<?php echo json_encode($this->options); ?>'
            :actions='["remove", "add", "edit"]'
            <?php if ($this->showCopyButton) echo 'show-copy-button'; ?>
            <?php if (!$this->editable) echo 'read-only'; ?>
    >
        <template slot="allItemsTitle"><?php echo $title; ?></template>
        <template slot="includeItemsTitle"><?php echo _('Методики, включенные в программу'); ?></template>
        <template slot="modeFinalize"><?php echo _('Итоговая оценочная форма')?></template>
        <template slot="remove"><?php echo _('Удалить');?></template>
        <template slot="edit"><?php echo _('Редактировать');?></template>
        <template slot="add"><?php echo _('Добавить');?></template>
        <template slot="hiddenItemTooltip"><?php echo _('Для включения данного этапа отредактируйте элемент программы'); ?></template>
        <!--template slot="selectItemLabel"><?php echo _('Выберите категорию'); ?></template-->
        <template slot="copyButton"><?php echo _('Применить к связанным профилям'); ?></template>
    </hm-programm-builder>
</v-card>