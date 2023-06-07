<div class="search-form-advanced">
    <?php echo $this->form;?>
</div>

<?php if ($this->paginator): ?>
    <div class="els-grid patched" id="grid">
    <?php 
        $page = $this->paginator->getCurrentPageNumber()-1;
        $itemPerPage = Candidate_SearchController::ITEMS_PER_PAGE;
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
<?php elseif ($this->error): ?>
<div><?php echo $this->error;?></div>
<?php else: ?>
<?php $this->inlineScript()->captureStart();?>
    showAdvancedForm();
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>