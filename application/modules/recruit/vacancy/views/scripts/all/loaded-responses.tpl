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

<div class="els-grid patched" id="grid">
<ol class="search-results" >
    <?php foreach($this->responses as $response):?>
        
        <?php
            $resumeLink = $this->url(array(
                'module'       => 'candidate',
                'controller'   => 'index',
                'action'       => 'resume',
                'candidate_id' => $response['candidate_id']
            ), null, true);
            
        ?>
    
        <li class="material">
            <div class="title">
                <a href="<?= $resumeLink;?>"><?= $response['name']?></a>
            </div>
            <div class="clearfix"></div>
            <div class="checkbox">
                <input type="checkbox" id="massCheckBox_grid" class="mass-checkbox" name="gridMassActions_grid" value="<?php echo $response['source'] . ':' . $response['candidate_id'] ?>" >
            </div>
            <div class="icon-wrapper"><?php 
                echo $this->cardLink(
                        $resumeLink,
                        _('Карточка'),
                        'icon-custom',
                        'pcard',
                        'pcard',
                        'position-icon candidate'
                    );    
            ?>
            </div>
            <div class="data-wrapper">

                <?php
                    switch ($response['source']) {
                        case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                            $sourceName = 'HeadHunter';
                        break;
                        case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                            $sourceName = 'SuperJob';
                        break;
                    }
                ?>
                <p class="url">
                    Источник: <b><?= $sourceName; ?></b><br />
                    <a target="_blank" href="<?= $resumeLink;?>"><?= $resumeLink?></a>
                </p>
           </div>
            <div class="clearfix"></div>
            <div class="description">

                <?php
                switch ($response['source']) {
                    case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                        $genders = array(
                            'male' => _('Мужской'),
                            'female' => _('Женский'),
                        );
                        $gender = $genders[$response['hh_gender']];
                        $age    = $response['hh_age'];
                    break;
                    case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                        $gender = $response['superjob_gender'];
                        $age    = $response['superjob_age'];
                    break;
                }
                ?>
                <p><?= _('Пол:') . ' ' . $gender ?></p>
                <p><?= _('Возраст:') . ' ' . $age?></p>
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
</div>