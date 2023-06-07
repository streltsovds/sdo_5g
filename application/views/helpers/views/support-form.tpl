<?php //Перенесено из application/modules/els/techsupport/views/scripts/ajax/get-form.tpl ?>
<div class="request-form" >
    <form id="request-form">
        <div class="request-form__theme-label">
            <label for="theme"><?=_('Тема')?><span class="request-form-required">&nbsp;*</span></label>
        </div>
        <div class="request-form__theme-form">
            <input type="text" name="theme" id="theme">
        </div>
        <div class="request-form__desc-label">
            <label for="problem_description"><?=_('Описание проблемы')?> <span><?=_('как работает сейчас')?></span></label>
        </div>
        <div class="request-form__desc-form">
            <textarea name="problem_description" id="problem_description" rows="3"></textarea>
        </div>
        <div class="request-form__result-label">
            <label for="wanted_result"><?=_('Ожидаемый результат')?> <span><?=_('как должно работать')?></span></label>
        </div>
        <div class="request-form__result-form">
            <textarea name="wanted_result" id="wanted_result" rows="3"></textarea>
        </div>
        <div class="request-form__button">
            <input type="button" id="submit-request-form" value="<?=_('Отправить')?>">
            <input type="button" id="cancel-request-form" value="<?=_('Закрыть')?>">
        </div>
    </form>
</div>
<div id="request-result"></div>