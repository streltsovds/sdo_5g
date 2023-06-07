<?php foreach ($this->totalResults as $result): ?>
    <div class="indicator-status indicator-status-<?php echo $result['cluster_status'];?>">
        <?php if ($result['cluster_status'] == HM_Quest_Question_Result_ResultModel::CLUSTER_STATUS_FINISHED): ?>
        <img src="<?= $this->serverUrl('/images/content-modules/tracklog/row_cussess_ok.png'); ?>">
        <?php else: ?>
        <img src="<?= $this->serverUrl('/images/content-modules/tracklog/row_cussess_no.png'); ?>">
        <?php endif; ?>
        <?php echo $result['cluster_name'];?>
    </div>
<?php endforeach; ?>
