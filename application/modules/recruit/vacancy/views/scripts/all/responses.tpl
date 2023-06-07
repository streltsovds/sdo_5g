<style>
    .grid-filters-from{
        margin-bottom: 10px;
    }
    .grid-filters-from dd,
    .grid-filters-from dt{
        display: inline;
    }
    .grid-filters-from dd{
        margin-right: 5px;
    }
</style>    

<?php $this->headLink()->appendStylesheet('/css/content-modules/kbase.css');?>
<?php $this->headLink()->appendStylesheet('/css/content-modules/grid.css');?>
<?php $this->headScript()->appendFile('/js/content-modules/grid.js'); ?>
<?php $this->headScript()->appendFile('/js/lib/jquery/jquery.collapsorz_1.1.min.js'); ?>


<h1><?php echo _('Отклики на вакансию') ?></h1>
<form class="grid-filters-from" method="post">
    <?php echo $this->sourceFilter; ?>
    <?php echo $this->submit; ?>
</form>

<?php if(count($this->responses)):?>
<div class="els-grid patched" id="grid">

    <div style="margin: 10px;">
        <input type="checkbox" class="mass-checkbox-all" name="gridMassActionsAll" value="">
        <label for="gridMassActionsAll"><?php echo _('Отметить все')?></label>
    </div>

    <ol class="search-results" start="<?php echo $page * $itemPerPage + 1; // @todo: кажется оно depricated?>">
    <?php foreach($this->responses as $response):?>
        <?php
//        var_dump($response);
        if(!$response->resume){
            continue;
        }

        switch ($response->source) {
            case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                $name = $response->resume->last_name. ' '. $response->resume->first_name . ' ' . $response->resume->middle_name;
                $url = $response->resume->alternate_url;
                $title = $response->resume->title;
                $id = $response->id;
            break;
            case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                if($response->resume->lastname || $response->resume->firstname || $response->resume->middlename){
                    $name = $response->resume->lastname. ' '. $response->resume->firstname . ' ' . $response->resume->middlename;
                } else {
                    $name = _('Cоискатель') . ' #' . $response->resume->id;
                }
                $id = $response->resume->id;
                $url = $response->resume->link;
                $title = $response->resume->profession;
            break;
        }
        ?>

        <li class="material">
            <div class="title">
                <a href="<?php echo $url;?>" target="_blank"><?= $name?></a>
            </div>
            <div class="clearfix"></div>
            <div class="checkbox">
                <input type="checkbox" id="massCheckBox_grid" class="mass-checkbox" name="gridMassActions_grid" value="<?php echo $response->source . ':' . $id ?>" >
            </div>
            <div class="icon-wrapper">
                <a href="<?php echo $url;?>" class="pcard-link lightbox" title="Резюме на сайте" target="_blank">
                    <span class="icon-custom position-icon candidate candidate-<?php echo $response->source;?>" title="Карточка"></span>
                </a>
            </div>
            <div class="data-wrapper">
                <p><?= $title?></p>
                <p><?= _('Возраст:') . ' ' . $response->resume->age?></p>
            </div>
        </li>
        

    <?php endforeach; ?>
</ol>
<br>
<?php echo $this->listMassActions(array(
    'actions' => $this->actions,
    'action_title' => _('Для выбранных откликов'),
    'customFormElements' => $this->customFormElements,
));?>
<?php $this->inlineScript()->captureStart();?>
    $(function(){

        $('.mass-checkbox-all').on('change', function(){
            if ($(this).is(':checked')) {
                $('.mass-checkbox').attr('checked', 'checked');
            } else {
                $('.mass-checkbox').removeAttr('checked');
            }
        });

        $('#gridAction_grid').on('change', function(){
            if(this.value == '<?=$this->assignToOtherVacancyUrl?>'){
                $('#vacancy_id').prop("disabled", false);
                $('#vacancy_id').show();
            } else {
                $('#vacancy_id').prop("disabled", true);
                $('#vacancy_id').hide();
            }
        });
    });
<?php $this->inlineScript()->captureEnd();?>
</div>

<?php else:?>
    <p><?=_('Отклики отсутствуют')?></p>
<?php endif; ?>