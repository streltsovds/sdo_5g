<?php echo $this->compiledVueCss; ?>
<div class="v-list v-sheet v-sheet--tile theme--light">
    <div class="v-list-item theme--light">
        <div class="v-list-item__content">
            <div class="v-list-item__title title secondary--text">
                <h4><?=_('Описание проблемы')?></h4>
                <p><?=$this->request->problem_description?></p>
            </div
        </div>
    </div>
    <div class="v-list-item theme--light">
        <div class="v-list-item__content">
            <div class="v-list-item__title title secondary--text">
                <h4><?=_('Ожидаемый результат')?></h4>
                <p><?=$this->request->wanted_result?></p>
            </div
        </div>
    </div>
    <div class="v-list-item theme--light">
        <div class="v-list-item__content">
            <div class="v-list-item__title title secondary--text">
                <a href="<?=$this->viewPageUrl?>">
                    <?=_('Войти от имени пользователя и посмотреть страницу с ошибкой');?>
                </a>
            </div
        </div>
    </div>
</div>
