<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/proctoring.css'), 'screen,print');
?>
<v-card class="hm-proctoring-start">
    <h1 class="hm-proctoring-start__title"><?php echo $this->title;?></h1>
    <div class="hm-proctoring-start__wrapper"><div class="hm-proctoring-start__body">
            <div style="
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                background: #faf3d8;
                border-radius: 4px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                width: calc(100% - 32px);
                margin: 16px;
                min-height: 56px;
                padding: 16px 0 16px 0;
            ">
                <div style="width: 56px; height: 100%; padding-left: 17px">
                    <v-icon color="#2D6BB1">
                        error
                    </v-icon>
                </div>
                    <?php if (is_array($this->messages) and count($this->messages)) : ?>
                        <?php foreach($this->messages as $id=>$message): ?>
                        <div class="hm-proctoring-start__comment" id='comment_<?= $id?>'><?php echo $message;?></div>
                        <?php endforeach; ?>
                    <?php endif;?>
            </div>
            <div class="hm-proctoring-start__report">
            <div class="report-summary clearfix">
                <div class="left-block">
                    <h2 class="hm-proctoring-start__subtitle"><?=_('Информация о занятии')?>:</h2>
                    <?php echo $this->reportList($this->info);?>
                </div>
            </div>
            </div>

            <div class="at-form-navpanel hm-proctoring-start__navpanel">
                <a href="<?= $this->continueUrl ?>" target="_top" title="<?= $this->escape(_('Перейти к занятию')) ?>" class="at-form-button at-form-advance hm-proctoring-start__button hm-proctoring-start__button_start">
                    <span style="font-size: 16px; text-transform: capitalize"> <?= _('Начать') ?></span>
                    <span style="color: #ffffff; font-size: 20px; margin-left: 10px">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.589844 12.0075L5.16984 6.84375L0.589844 1.68L1.99984 0.09375L7.99984 6.84375L1.99984 13.5938L0.589844 12.0075Z" fill="white"/>
                        </svg>
                    </span>
                </a>
                <a href="<?= $this->stopUrl ?>" target="_top" title="<?= $this->escape(_('Выйти без подтверждения')) ?>" class="at-form-button at-form-stop hm-proctoring-start__button hm-proctoring-start__button_stop"><?= _('Выйти') ?></a>
        </div>
    </div></div>

<?php $this->inlineScript()->captureStart(); ?>

$(document).bind('ready.at-form-quest els:content-changed.at-form-quest', function () {
	$('.at-form-navpanel a.at-form-button').each(function () {
		$(this).button({ disabled: $(this).hasClass('ui-state-disabled') });
	});
})

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
//    if(accessOk)     $('.at-form-advance').removeClass('ui-state-disabled');  
//    else                   $('.at-form-advance').addClass('ui-state-disabled');   
//    $('.at-form-advance').prop('disabled', !accessOk);
//    if(accessOk) $('.at-form-advance').removeAttr("disabled");
    $('.at-form-advance').button({ disabled: !accessOk });

    $('#comment_'+accessOk).show();
    $('#comment_'+(accessOk ? 0:1)).hide();
}

check();
setInterval(check, 5000);
updateInterface(<?= $this->passed_proctoring ?>)
});


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

<?php $this->inlineScript()->captureEnd(); ?>
</v-card>

<?= $this->proctoringStudent($this->lessonId); ?>