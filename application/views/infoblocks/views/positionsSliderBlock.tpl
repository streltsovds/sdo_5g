<?php $this->Swiper('scroll')->captureStart();?>
<?php if(count($this->positions)): ?>
    <?php foreach($this->positions as $position):?>
    <v-card style="position: relative;padding-bottom: 44px;" width="270" height="270">
        <a href="<?php echo $this->url(array('baseUrl' => 'hr', 'module' => 'reserve', 'controller' => 'position', 'action' => 'description', 'position_id' => $position->reserve_position_id));?>" title="<?php echo _('Перейти к описанию должности КР');?>">
			<?php echo $position->getIconHtml();?>
        </a>
                <v-card-title class="title">
                    <a href="<?php echo $this->url(array('baseUrl' => 'hr', 'module' => 'reserve', 'controller' => 'position', 'action' => 'description', 'position_id' => $position->reserve_position_id));?>" title="<?php echo _('Перейти к описанию долджности КР');?>">
                        <?php echo strlen($position->name) > 60 ? substr($position->name, 0, 60) . '...' : $position->name;?>
                    </a>
                </v-card-title>
                <?php if (strtotime($position->app_gather_end_date) > 0):?>
                <v-list dense>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-action-text>
                                <?php echo _('Дата окончания сбора заявок:'); ?>
                            </v-list-item-action-text>
                            <v-list-item-title>
                                <?php echo date('d.m.Y', strtotime($position->app_gather_end_date)); ?>
                            </v-list-item-title>
                        </v-list-item-content>

                    </v-list-item>
                </v-list>
                <?php endif;?>
        </v-card-text>
        <v-card-actions style="position: absolute;bottom: 0;">
            <v-btn text color="primary"
                    data-url="<?php echo $this->url(array('baseUrl' => 'hr', 'module' => 'reserve-request', 'controller' => 'list', 'action' => 'create-request', 'position_id' => $position->reserve_position_id));?>"
                <?php if (!is_null($requests) && in_array($this->userId, $requests)): ?>
                    disabled="disabled"
                    title="<?php echo _('Вы уже подали заявку на участие в программе кадрового резерва на данную дложность.'); ?>"
                <?php endif;?>
            >
                <?php echo _('Подать заявку'); ?>
            </v-btn>
        </v-card-actions>
    </v-card>
    <?php endforeach; ?>
<?php $this->Swiper()->captureEnd();?>
<?php endif; ?>
<?php $this->inlineScript()->captureStart(); ?>
$(function () {
    var sliderClass = 'hm-catalog-showcase'
    var options = {
        horizontalScroll: true,
        scrollButtons: {
            enable: false
        }
    }

    // фикс инициализации слайдера
    // без таймаута он инициализируется
    // не корректно - контейнер не скроллится
    // setTimeout(function () {
    //   $('.' + sliderClass).mCustomScrollbar(options)
    //}, 0)

    $(document).on('click', '#positionsSliderBlock button', function (e){
        e.preventDefault();
        if (confirm('<?php echo _('Вы действительно желаете подать заявку на участии в программе кадрового резерва?')?>')) {
            document.location.href = $(this).data('url');
        }
        return false;
    });
})

<?php $this->inlineScript()->captureEnd(); ?>