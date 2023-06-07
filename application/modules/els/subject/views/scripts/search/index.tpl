<v-card>
    <v-toolbar card>
        <?php echo $this->partial('_search-form-simple.tpl', array('query' => $this->query));?>
    </v-toolbar>
    <v-card-text>
        <?php if($this->error == false): ?>
            <?php
                $page = $this->paginator->getCurrentPageNumber()-1;
                $itemPerPage = $this->paginator->getDefaultItemCountPerPage();
                $i =0;
            ?>
            <ol class="search-results" start="<?php echo $page * $itemPerPage + 1; // @todo: кажется оно depricated?>">
            <?php
                foreach($this->paginator as $key => $item){
                    // здесь было много лишнего кода
                    if(isset($item['obj']) && $item['obj']) //#18208 - ссылка в индексе на документ есть, а его уже в базе нет
                    echo $this->searchItem($item['obj'], $page * $itemPerPage + (++$i), $this->words, array('search_query', 'page'));
                           }
            ?>
            </ol>
            <?php echo $this->listMassActions(array(
                'pagination' => array($this->paginator, 'Sliding', '_search-controls.tpl', array('query' => $this->query)),
                'export' => array('formats' => array('excel'), 'params' => array('search_query' => $this->query)),
            ));?>
        <?php else: ?>
            <v-alert type="warning" value="true" >
                <span class="body-2 black--text">
                    <?php echo $this->error;?>
                </span>
            </v-alert>
        <?php endif;?>
    </v-card-text>
</v-card>