<v-card-text style="padding: 0 !important;" class="hm-widget-quiz">

<?php if ($this->enabled): ?>
<?php $containerId = $this->id('at-form'); ?>
    <div id="poll-body" class="overflow">
        <hm-quiz load="<?php echo $this->ajaxUrl; ?>" saveUrl="<?php echo $this->saveUrl; ?>"></hm-quiz>
    </div>
<?php else: ?>

    <v-card-title class="title  lighten-4">
        <?php echo _('Опрос') ?>
    </v-card-title>

<hm-empty > 
    <?php echo _('Нет данных для отображения') ?>
</hm-empty>
<?php endif; ?>
</v-card-text>
<?php if ($this->isModerator): ?>
<div style="clear: both"></div>

<!--разобраться с позиционирование hm-action-...-->

<!--hm-actions-results url="<?php echo $this->url(array(
    'module'         => 'quest',
    'controller'    => 'report',
    'action'        => 'poll',
    'quest_id'      => $this->questId,
    'context'      => 'widget',
    'fromwidget'      => 'true'
));?>"/-->

<hm-actions-edit url="<?php echo $this->url(array(
    'module'         => 'infoblock',
    'controller'    => 'quizzes',
    'action'        => 'many-edit',
));?>"/>

<?php endif; ?>