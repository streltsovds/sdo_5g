<li class="material <?php if ($this->disabledMsg): ?>disabled<?php endif;?>" <?php if ($this->disabledMsg): ?>title="<?php echo $this->disabledMsg;?>"<?php endif;?>>
    <div class="title">
        <?php if ($this->item->getViewUrl()): // проверка для ресурсов у которых опубликована только карточка?>
            <a href="<?= $this->url($this->item->getViewUrl());?>"><?= $this->item->getName();?></a>
        <?php else: ?>
            <p><?php echo $this->item->getName();?></p>
        <?php endif; ?>
        <?php if (count($this->item->tag)):?>
        <span class="keywords">
            <?php foreach ($this->item->tag as $tag):?>
                <?php if (is_a($this->item, 'HM_Resource_ResourceModel') && ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_RESOURCE)) continue;?>
                <?php if (is_a($this->item, 'HM_Course_CourseModel') && ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_COURSE)) continue;?>
                <span class="keyword"><?php echo $tag->body;?></span>
            <?php endforeach;?>
        </span>
        <?php endif;?>
    </div>
    <div class="clearfix"></div>
    <?php if ($this->checkbox): ?>
    <div class="checkbox">
        <input type="checkbox" id="massCheckBox_grid" class="mass-checkbox" name="gridMassActions_grid" value="<?php echo $this->item->getPrimaryKey();?>" <?php if ($this->disabledMsg): ?>disabled<?php endif;?>>
    </div>
    <?php endif;?>
    <?php if ($this->item->getIconClass()): ?>
        <div class="icon-wrapper"><?php
            echo $this->cardLink(
                    $this->url($this->item->getCardUrl()),
                    _('Карточка'),
                    'icon-custom',
                    'pcard',
                    'pcard',
                    $this->item->getIconClass()
                );
        ?>
        </div>
    <?php endif; ?>
    <div class="data-wrapper">
        <?php if ($this->item->getViewUrl()): ?>
            <p class="url"><a href="<?= $this->url(array_merge($this->unsetParams, $this->item->getViewUrl()));?>"><?php echo $this->serverUrl() . $this->url(array_merge($this->unsetParams, $this->item->getViewUrl())); // @todo: здесь надо unset'ить вообще все параметры расширенной формы?></a></p>
        <?php endif; ?>
        <?php if ($this->item->getCreateUpdateDate()): ?>
            <p class="date"><?php echo $this->item->getCreateUpdateDate();?></p>
        <?php endif; ?>
    </div>
	<div class="description"><?php echo nl2br($this->item->getDescription());?></div>
</li>