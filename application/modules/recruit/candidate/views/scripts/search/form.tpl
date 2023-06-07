<?php// echo $this->headSwitcher(array('module' => 'candidate', 'controller' => 'search', 'action' => 'form', 'switcher' => 'form'));?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/kbase.css')); ?>
    <div class="recruit_left">
    <?php echo $this->partial('_search-form-simple.tpl', array('url' => $this->url(array('module' => 'candidate', 'controller' => 'search', 'action' => 'form')), 'query' => $this->query));?>
    </div>
    <div class="clearfix"></div>
<?php if (!$this->error && $this->paginator): ?>
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
        'pagination' => array($this->paginator, 'Sliding', '_search-controls.tpl', array('query' => $this->query)),
        'export' => array('formats' => array('excel'), 'params' => array('search_query' => $this->query)),
        'actions' => $this->actions,
    ));?>
    </div>    
<?php else: ?>
<div><?php echo $this->error;?></div>
<?php endif;?>