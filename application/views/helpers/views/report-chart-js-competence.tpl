<div class="report-chart">
    <h3 class="report-chart-title"><?php echo $this->chartOptions['title']; ?></h3>
    <div class="report-chart-graph <?php if ($this->tableOptions['showTable'] == HM_View_Helper_ReportChartJS::TABLE_DISPLAY_INLINE): ?>inline<?php endif;?>">
        <?php echo $this->chartJS($this->data, $this->graphs, $this->chartOptions);?>
    </div>
    <?php if ($this->tableOptions['showTable'] != HM_View_Helper_ReportChartJS::TABLE_DISPLAY_NONE): ?>
        <div class="report-chart-table <?php if ($this->tableOptions['showTable'] == HM_View_Helper_ReportChartJS::TABLE_DISPLAY_INLINE): ?>inline<?php endif;?>">
        <table id="competence-table">
            <thead>
                <tr>
                    <th><?php echo $this->tableOptions['dataTitle']?></th>
                    <?php foreach ($this->data as $datum): ?>
                        <?php if (!$this->tableOptions['hideData']): ?>
                            <th><?php echo $datum['title'];?></th>
                        <?php endif; ?>
                        <?php if ($this->tableOptions['procentColumn']) : ?>
                            <th>
                                <?php if ($this->tableOptions['procentColumnName']): ?>
                                    <?php echo $this->tableOptions['procentColumnName'];?> (%)
                                <?php else: ?>
                                    <?php echo $datum['title'];?> (%)
                                <?php endif;?>
                            </th>
                        <?php endif;?>
                    <?php endforeach;?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b><?php echo _('Плановое значение'); ?></b></td>
                    <?php foreach ($this->data as $num => $row): ?>
                        <?php foreach ($row as $key => $cell): ?>
                            <?php if ($key !== 'title'): ?>
                                <td><b><?php echo ($cell === false) ? '-' : $cell;?></b></td>
                            <?php endif;?>
                            <?php if ($this->tableOptions['procentColumn'] && ($key !== 'title')) : ?>
                                <td><b><?php echo ($cell === false) ? '-' : $this->tableOptions['totalValue'] ? round($cell/$this->tableOptions['totalValue']*100) . '%' : '-';?></b></td>
                            <?php endif;?>
                        <?php endforeach;?>
                    <?php endforeach;?>
                </tr>
            </tbody>
        </table>
        <?php if (!empty($this->tableOptions['footnote'])): ?>
            <div class="footnotes"><p>*<span><?php echo $this->tableOptions['footnote'];?></span></p></div>
        <?php endif;?>
        </div>
    <?php endif;?>
</div>