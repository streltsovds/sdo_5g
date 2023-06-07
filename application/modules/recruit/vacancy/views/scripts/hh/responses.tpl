<?php $this->headLink()->appendStylesheet('/css/content-modules/kbase.css');?>
<?php $this->headLink()->appendStylesheet('/css/content-modules/grid.css');?>
<?php $this->headScript()->appendFile('/js/content-modules/grid.js'); ?>
<?php $this->headScript()->appendFile('/js/lib/jquery/jquery.collapsorz_1.1.min.js'); ?>

<div class="els-grid patched" id="grid">
<ol class="search-results" start="<?php echo $page * $itemPerPage + 1; // @todo: кажется оно depricated?>">
    <?php foreach($this->responses as $response):?>
        <?php
//        var_dump($response);
        if(!$response->resume){
            continue;
        }
        ?>
        
        <li class="material">
            <div class="title">
                <a href="<?= $response->resume->alternate_url;?>"><?= $response->resume->last_name. ' '. $response->resume->first_name . ' ' . $response->resume->middle_name;?></a>
            </div>
            <div class="clearfix"></div>
            <div class="checkbox">
                <input type="checkbox" id="massCheckBox_grid" class="mass-checkbox" name="gridMassActions_grid" value="<?php echo $response->resume->id ?>" >
            </div>
            <div class="icon-wrapper"><?php 
                echo $this->cardLink(
                        $response->resume->alternate_url,
                        _('Карточка'),
                        'icon-custom',
                        'pcard',
                        'pcard',
                        'position-icon candidate'
                    );    
            ?>
            </div>
            <div class="data-wrapper">
                <p class="url"><a href="<?= $response->resume->alternate_url;?>"><?= $response->resume->alternate_url?></a></p>
                <p class="date"><?= _('Дата отклика:') . ' ' . date('d.m.Y m:s' ,strtotime($response->created_at))?></p>
            </div>
            <div class="clearfix"></div>
            <div class="description">
                <p><?= _('Пол:') . ' ' . $response->resume->gender->name?></p>                
                <p><?= _('Возраст:') . ' ' . $response->resume->age?></p>
            </div>
        </li>
        

    <?php endforeach; ?>
</ol>
<br>
<?php echo $this->listMassActions(array(
    'actions' => $this->actions,
));?>
</div>