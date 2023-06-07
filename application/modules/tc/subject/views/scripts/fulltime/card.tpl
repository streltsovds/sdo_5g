<div class="hm-tc-subject-card">
    <img src="<?php echo ($this->subject->getIcon());?>" alt="<?php echo $this->escape($this->subject->name)?>" align="left" style="margin-right: 20px;"/>
    <?php
    echo $this->card(
        $this->subject,
        array(
            'getType()'             => _('Тип'),
            'getFulltimeCategory()' => _('Категория'),
            'getCriterionName()'    => _('Компетенция/ квалификация'),
            'getPrice()'            => _('Стоимость'),
            'longtime'              => _('Длительность курса, дней'),
            //        'getColorField()'       => _('Цвет в календаре'),
            //'description'           => _('Описание'),
        ),
        array(
            'title' => _('Карточка внешнего курса'),
            'noico' => false
        )
    );
    ?>
    <?php if ($this->subject->description): ?>
    <div class="card_description">
        <strong><?php echo $this->escape(_('Описание'))?>:</strong>
        <div><?php echo nl2br($this->subject->description);?></div>
    </div>
    <?php endif; ?>
</div>
