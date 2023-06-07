<hm-yield-block url="/infoblock/yield/get-data/format/json"
                type='<?php echo ($this->type); ?>'
>
</hm-yield-block>
<hm-actions-download url="<?php echo $this->url(array(
    'module' => 'infoblock',
    'controller' => 'yield',
    'action' => 'get-data',
    'format' => 'csv'
)); ?>"/>
