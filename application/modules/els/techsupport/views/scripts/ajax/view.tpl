<?
// По идее не используется, возврат данных происходит в JSON
// Либо будет использоваться при прямом открытии? ?>
<div class="request-view" >

    <div class="v-application hm-user-content">
        <h4><?=_('Описание проблемы')?></h4>
        <p><?=$this->request->problem_description?></p>
        <h4><?=_('Ожидаемый результат')?></h4>
        <p><?=$this->request->wanted_result?></p>
        <a href="<?=$this->viewPageUrl?>">
            <?=_('Войти от имени пользователя и посмотреть страницу с ошибкой');?>
        </a>
    </div>
</div>