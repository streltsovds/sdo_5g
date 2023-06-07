<?php if ($this->gridAjaxRequest): ?>
    <div id="grid-ajax">
        <?php if($this->treeajax == 'true'): ?>
            <?php echo $this->Actions('orgstructure', array(), array('parent' => $this->orgId));?>
        <?php endif;?>
        <?php echo $this->grid?>
    </div>
<?php else: ?>
    <v-layout wrap>
        <v-flex sm12 md4>
            <v-card class="orgstructure-tree">
                <v-card-text>
                    <?php echo $this->vueRubricator(
                        $this->tree,
                        $this->url(array(
                            'module' => 'report',
                            'controller' => 'list',
                            'action' => 'get-tree-branch'
                        ), null, true),
                        $this->gridId,
                        $this->url(array('module' => 'report', 'controller' => 'index', 'action' => 'index', 'report_id' => ''), null, true)//'gridmod' => 'ajax', 'treeajax' => 'true', 
                    );?>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex sm12 md8>
            <div class="orgstructure-list">
                <div id="grid-ajax">
                    <?php echo $this->Actions('orgstructure', array(), array('parent' => $this->orgId));?>
                    <?php echo $this->grid?>
                    <?php echo $this->footnote();?>
                </div>
            </div>
        </v-flex>
    </v-layout>
<?php endif; ?>

    <?php
/*
        echo $this->uiDynaTree(
            'orgstructure-tree',
            $this->htmlTree($this->tree, 'htmlTree'),
            array(
                'remoteUrl' => $this->url(array(
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'get-tree-branch'
                )),
                'title' => _('Отчеты'),
                'onActivate' => 'function (dtnode) {
                    $("#grid-ajax").load("'.$this->serverUrl($this->url(array('module' => 'report', 'controller' => 'index', 'action' => 'index', 'report_id' => ''))).'"+dtnode.data.key);
                }',
                // block user interaction while loading child nodes
                'onClick' => 'function (dtnode, event) { if (dtnode.isLoading) { return false; } }',
                'onKeydown' => 'function (dtnode, event) { if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } }'
            )
        );
*/
    ?>
