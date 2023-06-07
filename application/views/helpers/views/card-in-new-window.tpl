<?php
switch (get_class($this->item)) {
    case 'HM_Subject_SubjectModel':
        $class = 'card_content_subject';
        break;
    case 'HM_User_UserModel':
        $class = 'card_content_user';
        break;
    default:
        $class = 'card_content_default';
        break;
}
?>

<style>

    .card_content {
        float: left;
        padding: 10px;
        wiwth: 550px;
    }


    .card_content dt, .card_content dd {
        display: block;
        /*float: left;*/
        height: 50px;
    }

    .card_content dt {
        width: 180px;
    }

    .card_content dd {
        margin-left: 190px;
        margin-top: -52px;
        max-width: 635px;
        overflow: auto;
    }
</style>

<div class="card_content <?php echo $class?> clearfix">

<?php if ($this->item):?>

<?php if(method_exists($this->item, 'getNameCyr') && method_exists($this->item, 'getNameLat')):?>
    <div>
    <h6><?php echo $this->item->getNameCyr();?></h6>
    <?php if (strlen($this->item->getNameLat())):?>
        <h6><?php echo $this->item->getNameLat();?></h6>
    <?php endif;?>
    <hr/>
    </div>
<?php endif;?>

<dl>
<!--<colgroup width="50%"></colgroup>
<colgroup width="50%"></colgroup>-->

<?php foreach($this->fields as $key => $title):?>
<?php if (is_array($title)) {$tooltip = $title['tooltip']; $title = $title['title']; } else $tooltip = false;?>
    <?php $data = ( !is_array($this->item->$key) )? $this->item->$key: $this->item->$key; ?>
    <?php if ($data == '-' || empty($data)) continue; ?>
    <?php if (strtolower($key) == 'description'): // @todo: придумать более правильный критерий?>
    <dt>
		<div class="card_description">
			<strong><?php echo $this->escape($title)?>:</strong>
			<div><?php echo '$data';?></div>
		</div>
    </dt>
    <?php else : ?>
        <dt>
            <?php if ($tooltip):?>
                <?php echo $this->tooltip($tooltip); ?>
            <?php endif;?>
            <strong><?php echo $this->escape($title)?>:</strong>
        </dt>
        <dd>
            <?php if(is_array($this->item->$key)):?>
                <?php $urlParams = $this->item->$key; unset($urlParams['name']);?>
                <a href="<?php echo $this->baseUrl($this->url($urlParams, null, true))?>" target="_blank"><?php echo $data['name']; ?></a>
            <?php elseif (strpos($data, 'http://') === 0):?>
                <a href="<?php echo $data; ?>" target="_blank"><?php echo ($key == 'getDescripiton()') ? nl2br($data) : $data; ?></a>
            <?php else:?>
                <?php echo ($key == 'getDescripiton()') ? nl2br($data) : $data; ?>
            <?php endif;?>
        </dd>
    <?php endif;?>
<?php endforeach;?>
</dl>

<?php if ($this->info):?>
<?php foreach($this->info as $key => $data):?>
    <dl>

        <?php if ($data['department']):?>
            <dt><strong><?php echo _('Подразделение')?>:</strong></dt>
            <dd><?php echo $data['department']->name?></dd>
        <?php endif;?>

        <dt><strong><?php echo _('Должность')?>:</strong></dt>
            <dd><?php echo $data['post']->name?></dd>

        <?php if (count($data['classifiers'])):?>
            <dt><strong><?php echo _('Классификация'); // ВНИМАНИЕ!!! При мерже с билайном оставить там слово 'Функция'?>:</strong></dt>
            <dd>
            <?php foreach($data['classifiers'] as $key => $classifiers):?>
                <?php echo $classifiers[0]->name?>
            <?php endforeach;?>
            </dd>
        <?php endif;?>

    </table>
    <br>

<?php endforeach;?>
<?php endif;?>

<?php endif;?>
</dl>
</div>
