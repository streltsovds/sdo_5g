<v-card>
    <?php if (isset($this->message)): ?>
        <v-card-text>
            <v-alert type="error" outlined value="true"><?php echo $this->message; ?></v-alert>
        </v-card-text>
    <?php else: ?>
        <v-card-text>
            <v-alert type="warning" outlined value="true"><?php echo _('При назначении курсов через программу начального обучения даты и длительности обязательных курсов задаются через календарь и рассчитываются относительно даты назначения в должность. Обучение на необязательных (элективных) курсах регламентируется собственными настройками курса.'); ?></v-alert>
        </v-card-text>
    <?php endif; ?>
    <hm-programm-builder
            :all-items='<?php echo json_encode($this->items, JSON_FORCE_OBJECT|JSON_HEX_APOS); ?>'
            :include-items='<?php echo json_encode($this->events, JSON_FORCE_OBJECT|JSON_HEX_APOS); ?>'
            save-url="<?php echo $this->serverUrl($this->url(array('module' => 'programm', 'controller' => 'subject', 'action' => 'assign', 'programm_id' => $this->programmId)))?>"
            edit-url="<?php echo $this->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'edit', 'baseUrl' => '', 'submethod' => ''));?>"
            :actions="['pin', 'remove', 'add']"
            remove-confirm="<?php echo _('Вы действительно желаете исключить этот курс из программы? При этом пользователи, уже зачисленные курс, не будут отчислены.'); ?>"
            pin-confirm="<?php echo _('Вы действительно желаете сделать этот курс обязательным? После сохранения программы данный курс будет автоматически назначен всем слушателям, проходящим обучение по данной программе.'); ?>"
            <?php if ($this->showCopyButton) echo 'show-copy-button'; ?>
    >
    <template slot="allSubjectsTitle"><?php echo _("Учебные курсы"); ?></template>
    <template slot="allItemsTitle"><?php echo _("Все учебные курсы"); ?></template>
    <template slot="includeItemsTitle"><?php echo _('Курсы, включенные в программу'); ?></template>
    <template slot="remove"><?php echo _('Исключить из программы'); ?></template>
    <template slot="edit"><?php echo _('Редактировать'); ?></template>
    <template slot="add"><?php echo _('Добавить'); ?></template>
    <template slot="pin"><?php echo _('Сделать обязательным'); ?></template>
    <template slot="unpin"><?php echo _('Сделать курсом по выбору'); ?></template>
    <template slot="hiddenItemTooltip"><?php echo _('Для включения данного этапа отредактируйте элемент программы'); ?></template>
    <template slot="selectItemLabel"><?php echo _('Выберите категорию'); ?></template>
    <template slot="copyButton"><?php echo _('Применить к связанным профилям'); ?></template>
    </hm-programm-builder>
</v-card>