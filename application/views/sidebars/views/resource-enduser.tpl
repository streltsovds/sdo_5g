<!--v-card style="position: relative;">
    <div style="padding: 20px;">
        <div><?= _('Дата обновления') ?>: <?php echo date('d.m.Y', strtotime($this->resource->updated)); ?></div>

        <div><?= _('Рейтинг') ?>: <?php echo $this->materialRating; ?></div>
        <div><?= _('Кол-во голосов') ?>: <?php echo $this->materialRatingCount; ?></div>

        <div><?= _('История изменений') ?></div>

        <div class="materials-recent revisions">
            <?php if(count($this->revisions) > 0): ?>
            <ul>
                <?php foreach($this->revisions as $revision):?>
                <li class="material revision">
                    <?php $num = count($this->revisions) - $i++;?>
                    <?php echo $revision->date($revision->updated)?> -
                    <a href="<?= $this->url(array('action' => 'index', 'num' => $num, 'resource_id' => $this->resource->resource_id, 'revision_id' => $revision->revision_id));?>" class="material-icon-small <?= $this->resource->getIconClass();?>"></a>
                    <a href="<?= $this->url(array('action' => 'index', 'num' => $num, 'resource_id' => $this->resource->resource_id, 'revision_id' => $revision->revision_id));?>"><?=sprintf(_('Версия #%s'), $num);?></a>
                </li>
                <?php endforeach;?>
            </ul>
            <?php else: ?>
            <p><?= _('Это первая версия ресурса, история изменений пуста.'); ?></p>
            <?php endif; ?>
        </div>

        <div><?= _('Связанные ресурсы') ?></div>
        <div class="materials-recent">
            <?php if(count($this->relatedResources) > 0): ?>
            <ul>
                <?php foreach($this->relatedResources as $resource):?>
                <li class="material <?php if ($resource->status != HM_Resource_ResourceModel::STATUS_PUBLISHED):?>unpublished<?php endif;?>"><a href="<?= $this->url(array('action' => 'index', 'resource_id' => $resource->resource_id));?>" class="material-icon-small <?= $resource->getIconClass();?>"></a><a href="<?= $this->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'resource_id' => $resource->resource_id));?>"><?= $resource->title;?></a></li>
                <?php endforeach;?>
            </ul>
            <?php else:?>
            <p><?= _('Данный ресурс не связан с другими ресурсами Базы знаний.'); ?></p>
            <?php endif;?>
        </div>

    </div>
</v-card-->
<hm-sidebar-resource-enduser :data-sidebar='<?php echo $this->data; ?>'/>