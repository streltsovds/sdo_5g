<div class="report-chart">
    <h3 class="report-chart-title"><?php echo $this->chartOptions['title']; ?></h3>
    <div class="report-chart-graph <?php if ($this->tableOptions['showTable'] == HM_View_Helper_ReportChartJS::TABLE_DISPLAY_INLINE): ?>inline<?php endif;?>">
        <?php echo $this->chartJS($this->data, $this->graphs, $this->chartOptions);?>
    </div>
    <?php if ($this->tableOptions['showTable'] != HM_View_Helper_ReportChartJS::TABLE_DISPLAY_NONE): ?>
        <div class="report-chart-table <?php if ($this->tableOptions['showTable'] == HM_View_Helper_ReportChartJS::TABLE_DISPLAY_INLINE): ?>inline<?php endif;?>">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo $this->tableOptions['dataTitle']?></th>
                    <?php foreach ($this->graphs as $graph): ?>
                        <?php if (!$this->tableOptions['hideData']): ?>
                            <th><?php echo $graph['legend'];?></th>
                        <?php endif; ?>
                        <?php if ($this->tableOptions['procentColumn']) : ?>
                            <th>
                                <?php if ($this->tableOptions['procentColumnName']): ?>
                                    <?php echo $this->tableOptions['procentColumnName'];?> (%)
                                <?php else: ?>
                                    <?php echo $graph['legend'];?> (%)
                                <?php endif;?>
                            </th>
                        <?php endif;?>
                    <?php endforeach;?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->data as $num => $row): ?>
                    <tr>
                        <td><?php echo $num + 1;?></td>
                        <?php foreach ($row as $key => $cell): ?>
                            <?php if (!$this->tableOptions['hideData'] || ($key == 'title')): ?>
                                <td><?php echo ($cell === false) ? '-' : $cell;?></td>
                            <?php endif;?>
                            <?php if ($this->tableOptions['procentColumn'] && ($key !== 'title')) : ?>
                                <td><?php echo ($cell === false) ? '-' : $this->tableOptions['totalValue'] ? round($cell/$this->tableOptions['totalValue']*100) . '%' : '-';?></td>
                            <?php endif;?>
                        <?php endforeach;?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <?php if (!empty($this->tableOptions['footnote'])): ?>
            <div class="footnotes"><p>*<span><?php echo $this->tableOptions['footnote'];?></span></p></div>
        <?php endif;?>
        </div>
    <?php endif;?>
</div>