<?php $idPrefix = $this->id('auth'); ?>

<?php // TODO v-card-text не должен быть обёрнут в hm-login! ?>
<hm-login>
<v-card-text>
    <p class="hm-login__subheading subheading"><?= _('Для доступа в закрытый раздел системы необходимо ввести логин и пароль.')?></p>
    <?= $this->form?>
</v-card-text>
<?php if (!Zend_Registry::get('config')->offline): ?>
<v-divider></v-divider>
<v-card-actions style="flex-wrap: wrap !important;">
    <?php if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('regDeny') !== '1'): ?>
        <v-btn class="hm-login__button" text color="accent" href="<?= $this->baseUrl('user/reg/self')?>"><?= _('Зарегистрироваться')?></v-btn>
    <?php endif;?>
    <v-btn class="hm-login__button" text color="accent" href="<?= $this->baseUrl('remember')?>"><?= _('Восстановить пароль')?></v-btn>
</v-card-actions>
<?php endif; ?>
<div class="ajax-spinner-local"></div>
</hm-login>


<?php //$this->inlineScript()->captureStart()?>
<!--(function () {-->
<!--var formId = --><?//= HM_Json::encodeErrorSkip("{$idPrefix}-form"); ?><!--;-->
<!--function authorizeMe () {-->
<!--    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');-->
<!---->
<!--    var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();-->
<!---->
<!--    $.ajax(--><?php //= HM_Json::encodeErrorSkip( $this->baseUrl($this->url(array('module' => 'default', 'controller' => 'index', 'action' => 'authorization'))) ) ?><!--, {-->
<!--        type: 'POST',-->
<!--        global: false,-->
<!--        data: {-->
<!--            start_login: 1,-->
<!--            captcha: {-->
<!--                id: $('#' + formId + ' input[id="captcha-id"]').val(),-->
<!--                input: $('#'+ formId +' input[id="captcha-input"]').val()-->
<!--            },-->
<!--            login: $('#'+ formId +' input[id="login"]').val(),-->
<!--            password: $('#'+ formId +' input[id="password"]').val(),-->
<!--            remember: Number($('#'+ formId +' input[id="remember"]').prop('checked')),-->
<!--            systemInfo: hwDetect.getSystemInfo()-->
<!--        }-->
<!--    }).done(function (data) {-->
<!--        $('#password').val('');-->
<!--        _.defer(function () {-->
<!--            $('#' + formId).html(data);-->
<!--        });-->
<!--    }).fail(function () {-->
<!--        var $message = jQuery("<div>--><?//= _('Произошла ошибка. Попробуйте ещё раз'); ?><!--</div>").appendTo('#' + formId);-->
<!--        jQuery.ui.errorbox.clear($message);-->
<!--        $message.errorbox({level: 'error'});-->
<!--    }).always(function () {-->
<!--        $('#' + formId).closest('.ui-portlet').removeClass('ui-state-loading');-->
<!--        $('#' + formId)-->
<!--            .prop('disabled', false)-->
<!--            .find('input').prop('disabled', false);-->
<!--    });-->
<!--}-->
<!---->
<!--$(document.body).delegate('#' + formId + ' *[id="refresh"]', 'click', function (event) {-->
<!--    event.preventDefault();-->
<!---->
<!--    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');-->
<!--    var login = $('#' + formId + ' input[id="login"]').val();-->
<!--    $.ajax(--><?php //= HM_Json::encodeErrorSkip( $this->baseUrl($this->url(array('module' => 'default', 'controller' => 'index', 'action' => 'authorization'))) ) ?><!--, {-->
<!--        global: false-->
<!--    }).done(function (data) {-->
<!--        $('#' + formId + ' input[id="password"]').val('');-->
<!--        $('#' + formId).html(data);-->
<!--        $('#' + formId + ' input[id="login"]').val(login);-->
<!--    }).always(function () {-->
<!--        $('#' + formId).closest('.ui-portlet').removeClass('ui-state-loading');-->
<!--    });-->
<!--});-->
<!--$(document.body).delegate('#' + formId + ' form', 'submit', _.debounce(function (event) {-->
<!--    $('#' + formId)-->
<!--        .prop('disabled', true)-->
<!--        .find('input').prop('disabled', true);-->
<!---->
<!--    var $portletContent = $(this).closest('.ui-portlet-content');-->
<!--    if ($portletContent.length) {-->
<!--        $portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());-->
<!--    }-->
<!---->
<!--    authorizeMe();-->
<!--}, 50));-->
<!--$(document.body).delegate('#' + formId + ' form', 'submit', function(event) {-->
<!--    event.preventDefault();-->
<!--});-->
<!---->
<!--})();-->
<?php //$this->inlineScript()->captureEnd()?>
