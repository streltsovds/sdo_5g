<div class="v-card-title__testing-start">
    <span ><?php echo $this->quest->name;?></span>
</div>
<?php if ($this->quest->info || $this->messages) : ?>
<div style="
                -webkit-box-align: start;
                -ms-flex-align: start;
                align-items: start;
                background: #faf3d8;
                border-radius: 4px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                width: 100%;
                margin: 16px 0;
                min-height: 56px;
                padding: 16px 0 15px 0;
            ">
    <div style="width: 56px; height: 100%; padding-left: 17px">
        <v-icon color="#2D6BB1">
            error
        </v-icon>
    </div>
    <?php if (is_array($this->messages) and count($this->messages)) : ?>
        <?php foreach($this->messages as $id=>$message): ?>
            <div class="hm-test__comment" id='comment_<?= $id?>'><?php echo $message;?></div>
        <?php endforeach; ?>
    <?php endif;?>
    <div class="hm-test__comment"><?= $this->quest->info; ?></div>
</div>
<?php endif;?>
<v-card>
    <v-card-text>
        <v-layout wrap="true" fill-height>
            <v-flex>
                <v-list subheader>
                    <v-subheader class="title secondary--text start-testings-header">
                        <span style="font-family: Roboto; font-size: 20px; line-height: 24px; letter-spacing: 0.02em; color: #1e1e1e;"><?=$this->settings['globalTitle']?></span>
                    </v-subheader>
                    <div class="testings">
                        <?php echo $this->reportList($this->settings['global']);?>
                    </div>
                </v-list>
            </v-flex>
            <?php if ($this->settings['showClusters']): ?>
            <v-flex>
                <v-list subheader>
                    <v-subheader class="title secondary--text start-testings-header">
                        <span style="font-family: Roboto; font-size: 20px; line-height: 24px; letter-spacing: 0.02em; color: #1e1e1e;"><?=$this->settings['clustersTitle']?></span>
                    </v-subheader>
                    <div class="testings">
                        <?php echo $this->reportList($this->settings['clusters']);?>
                    </div>
                </v-list>
            </v-flex>
            <?php endif; ?>
        </v-layout>
    </v-card-text>
    <v-card-actions class="v-card__actions-btn">
        <v-btn id="test_start_button" color="warning" href="<?= $this->continueUrl ?>" target="_top" title="<?= $this->escape(_('Перейти к заполнению')) ?>" style="padding: 6px 25px; width: 125px">
            <span style="font-size: 16px; text-transform: capitalize"> <?= _('Начать') ?></span>
            <span style="color: #ffffff; font-size: 20px; margin-left: 10px">
                <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.589844 12.0075L5.16984 6.84375L0.589844 1.68L1.99984 0.09375L7.99984 6.84375L1.99984 13.5938L0.589844 12.0075Z" fill="white"/>
                </svg>
            </span>
        </v-btn>
        <v-btn text color="#1e1e1e" id="exit-btn" class="v-card__actions-btn__close" href="<?= $this->stopUrl ?>" target="_top" style="padding: 6px 25px; width: 120px; background: #ffffff!important; border: 1px solid #70889E; border-radius: 4px" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>">
            <span style="font-size: 16px; text-transform: capitalize"><?= _('Выйти') ?></span>
        </v-btn>
    </v-card-actions>
</v-card>

<?= $this->proctoringStudent($this->lessonId); ?>

<?php $this->inlineScript()->captureStart(); ?>

$(document).bind('ready.at-form-quest els:content-changed.at-form-quest', function () {
	$('.at-form-navpanel a.at-form-button').each(function () {
		$(this).button({ disabled: $(this).hasClass('ui-state-disabled') });
	});
})

<?php if($this->has_proctoring && $this->isEnduser) : ?>

$(document).ready(function() {
function check() {
    fetch("/lesson/index/check?SSID=<?= $this->SSID; ?>")
      .then(
        function(response) {
          if (response.status == 200) {
              response.json().then(function(data) {
                updateInterface(parseInt(data));
              });
          }
        }
      );
}
function updateInterface(accessOk)
{
    if(!accessOk) $('#test_start_button').addClass('v-btn--disabled');
    else $('#test_start_button').removeClass('v-btn--disabled');

    $('#comment_'+accessOk).show();
    $('#comment_'+(accessOk ? 0:1)).hide();
}

check();
setInterval(check, 5000);
updateInterface(<?= $this->passed_proctoring ?>)

});

<?php endif; ?>

$(document).delegate('.at-form a.at-form-stop', 'mousedown', function (event) {
    var target;
    if (target = $(this).attr('target')) {
        $(this)
            .data('target', target)
            .removeAttr('target');
    }
});
$(document).delegate('.at-form a.at-form-stop', 'click', function (event) {
    var $target = $(this)
      , message;

    event.preventDefault();

    elsHelpers.confirm(<?= Zend_Json::encode(_('Вы уверены, что вы хотите прервать процесс?')) ?>).done(function () {
        /**
            В Firefox при нажатии на кнопку выйти не корректно закрывается сокет-соединение.
            И пользователь остается в сети с пузырьками загрузки.
            При насильном удалении фрейма нет никаких проблем.
        **/
        var proctWindow = document.querySelector('#proctoringWindow iframe');
        if(proctWindow) proctWindow.remove();

        top.location.href = $target.attr('href');
    })
});

$('test_start_button').click(function() {
    $(this).addClass('v-btn--disabled');
});

document.querySelector('#exit-btn').onclick = (e) => {
    window.COMMON_DATA = {
        event_id: "close_window"
    }
}
<?php $this->inlineScript()->captureEnd(); ?>
