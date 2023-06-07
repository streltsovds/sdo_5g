<?php if ($this->paginator && $this->paginator->getTotalItemCount()): ?>
    <div class="els-grid patched" id="grid">
    <?php 
        $page = $this->paginator->getCurrentPageNumber()-1;
        $itemPerPage = $this->paginator->getDefaultItemCountPerPage();
        $i =0;
    ?>
    <ol class="search-results" start="<?php echo $page * $itemPerPage + 1; // @todo: кажется оно depricated?>">
    <?php    
        foreach($this->paginator as $key => $item) {
            echo $this->searchItem(
                $item['obj'], 
                $page * $itemPerPage + (++$i), 
                $this->words, 
                array('vacancy_id', 'switcher'), 
                true, // allow checkboxes
                in_array($item['attrs']['user_id'], $this->existingUserIds) ? _('Кандидат уже включен в сессию подбора') : false // disabled candidate 
            );
        }
    ?>
    </ol>
    <br>
    <?php echo $this->listMassActions(array(
        'pagination' => array($this->paginator, 'Sliding', '_search-controls-advanced.tpl', array('params' => $this->params)),
        'export' => array('formats' => array('excel'), 'params' => $this->params),
        'actions' => $this->actions,
    ));?>
    </div>    
<?php else: ?>
<div class="clearfix"></div>
<?php $url = $this->url(array('action' => 'advanced-form'))?>

<div style="padding-top: 20px;"><?php echo _("Не найден ни один кандидат, соответствующий критериям поиска, указанным в заявке на подбор.<br>Вы можете воспользоваться <a href='{$url}'>формой поиска</a> по произвольным критериям.")?></div>
<?php endif;?>