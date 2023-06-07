<?php if (is_array($this->data) && count($this->data)):?>
    <?php if (strlen($this->title)):?>
        <h3 class="report-chart-title"><?php echo $this->title?></h3>
    <?php endif;?>
    <div class="report-table">
    <table>
    <?php $i = 0;?>
    <?php foreach ($this->data as $num => $row): ?>
        <?php if (!$i): ?>
            <thead>
                <tr>
                    <?php if ($this->enumerate): ?>
                        <th><?php echo $this->enumerate;?></th>
                    <?php endif;?>
                    <?php foreach ($row as $cell): ?>
                        <th><?php echo $cell;?></th>
                    <?php endforeach;?>
                </tr>
            </thead>
        <?php else:?>
            <tbody>
                <tr>
                    <?php if ($this->enumerate): ?>
                        <td class="enumerate"><?php echo $i;?></td>
                    <?php endif;?>
                    <?php foreach ($row as $cell): ?>
                        <?php if (is_array($cell)): ?>
                            <td class="<?php echo $cell['class']?>"><?php echo ($cell['value'] === false) ? '-' : $cell['value'];?></td>
                        <?php else: ?>
                            <td><?php echo ($cell === false) ? '-' : $cell;?></td>
                        <?php endif;?>
                    <?php endforeach;?>
                </tr>
            </tbody>
        <?php endif;?>
        <?php $i++; ?>
    <?php endforeach;?>
    </table>
    <?php if(count($this->data) == 1): ?>
        <p class="report-no-value report-no-value-table"><?php echo _('Нет данных для отображения')?></p>
    <?php endif;?>
    </div>
<?php //else: ?>
<!--    <p class="report-no-value report-no-value-table">--><?php //echo _('Нет данных для отображения')?><!--</p>-->
<?php endif;?>