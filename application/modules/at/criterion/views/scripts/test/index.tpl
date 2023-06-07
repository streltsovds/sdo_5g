<?php if ($this->gridAjaxRequest): ?>
    <?php if ($this->gridmod):?>
        <?php echo $this->grid?>
    <?php else:?>
        <div id="criterion-test-grid">
<!--            --><?php //if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:criterion:test:new')) :?>
<!--                --><?php //echo $this->actions('criterion-test', array(), array('parent' => $this->parent));?>
<!--            --><?php //endif;?>
            <?php echo $this->grid?>
        </div>
    <?php endif;?>
<?php else:?>
    <?php $this->placeholder('columns')->captureStart('SET'); ?>
    <div class="subject-catalog-list">
        <div id="criterion-test-grid">
<!--            --><?php //if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:criterion:test:new')) :?>
<!--                --><?php //echo $this->actions('criterion-test', array(), array('parent' => $this->parent));?>
<!--            --><?php //endif;?>
            <?php echo $this->grid?>
        </div>
    </div>
    <?php $this->placeholder('columns')->captureEnd(); ?>

<!--    --><?php //$this->placeholder('columns')->captureStart(); ?>
<!--    <div class="subject-catalog-categories">-->
<!--        --><?php
//            echo $this->uiDynaTree(
//                'criteria',
//                $this->htmlTree($this->tree, 'htmlTree'),
//                array(
//                    'remoteUrl' => $this->url(array(
//                        'module' => 'criterion',
//                        'controller' => 'test',
//                        'action' => 'get-tree-branch'
//                    )),
//                    'title' => _('Квалификации'),
//                    'onActivate' => 'function (dtnode) {
//                        gridAjax("criterion-test-grid", "criterion/test/index/parent/"+dtnode.data.key);
//                    }',
//                    // block user interaction while loading child nodes
//                    'onClick' => 'function (dtnode, event) { if (dtnode.isLoading) { return false; } }',
//                    // @todo: что за цифры 37, 39..??
//                    'onKeydown' => 'function (dtnode, event) { if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } }'
//                )
//            );
//        ?>
<!--    </div>-->
<!--    --><?php //$this->placeholder('columns')->captureEnd(); ?>
    <?php echo $this->partial('_columns.tpl', array(
        'columns' => $this->placeholder('columns')->getArrayCopy(),
        'classes' => 'subject-catalog',
        'type' => 'px'
    )); ?>
<?php endif;?>