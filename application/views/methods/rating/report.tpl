<?php foreach ($this->totalResults as $result): ?>
    <div class="pairs-status pairs-status-<?php echo $result['status'];?>">
        <?php if ($result['status'] == HM_At_Evaluation_Results_ResultsModel::PAIRS_STATUS_FINISHED): ?>
        <img src="<?= $this->serverUrl('/images/content-modules/tracklog/row_cussess_ok.png'); ?>">
        <?php echo $result['rating'];?>: <strong><?php echo $result['user']->getName();?></strong> (<?php echo $result['ratio'];?>%)
        <?php else: ?>
        <img src="<?= $this->serverUrl('/images/content-modules/tracklog/row_cussess_no.png'); ?>">
        <strong><?php echo $result['user']->getName();?></strong>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
