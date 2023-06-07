<?php foreach ($this->totalResults as $criterionId => $result): ?>
    <div class="indicator-status indicator-status-<?php echo $result['indicators_status'];?>">
        <?php if ($result['indicators_status'] == HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_FINISHED): ?>
        <img src="<?= $this->serverUrl('/images/content-modules/tracklog/row_cussess_ok.png'); ?>">
        <?php else: ?>
        <img src="<?= $this->serverUrl('/images/content-modules/tracklog/row_cussess_no.png'); ?>">
        <?php endif; ?>
        <?php echo $result['criterion'];?>: <strong><?php echo $result['value'];?></strong>
    </div>
<?php endforeach; ?>
