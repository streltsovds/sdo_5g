<div class="report-chart">
<h3 class="report-chart-title"><?php echo $this->title?></h3>
<div class="report-chart-graph <?php if ($this->showtable == HM_View_Helper_ReportChart::TABLE_DISPLAY_INLINE): ?>inline<?php endif;?>">
    <div id="<?php echo $this->chartId;?>-container"></div>
</div>
<?php if ($this->showtable != HM_View_Helper_ReportChart::TABLE_DISPLAY_NONE): ?>
<div class="report-chart-table <?php if ($this->showtable == HM_View_Helper_ReportChart::TABLE_DISPLAY_INLINE): ?>inline<?php endif;?>">
<table>
<?php $i = 0;?>
<?php foreach ($this->data as $num => $row): ?>
    <tr>
    <?php foreach ($row as $cell): ?>
    <?php if (!$i): ?>
    <th><?php echo $cell;?></th>
    <?php elseif (!$this->multigraph && ($i == (count($this->data) - 1))): ?>
    <td style="font-weight: bold;"><?php echo ($cell === false) ? '-' : $cell;?></td>
    <?php else: ?>
    <td><?php echo ($cell === false) ? '-' : $cell;?></td>
    <?php endif;?>
    <?php endforeach;?>
</tr>
<?php $i++; ?>
<?php endforeach;?>
</table>
<?php if (!empty($this->footnote)): ?>
<div class="footnotes"><p>*<span><?php echo $this->footnote;?></span></p></div>
<?php endif;?>
</div>
<?php endif;?>
</div>

