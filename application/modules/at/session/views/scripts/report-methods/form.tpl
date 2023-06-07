<?php if (count($this->quests)): ?>
    <?php foreach ($this->quests as $quest):?>
        <?php $i = 0;?>
        <h2><?php echo $quest->name;?></h2>
        <?php if (count($this->questions[$quest->quest_id])): ?>
            <?php foreach ($this->questionsByClusters[$quest->quest_id] as $cluster => $questions):?>
                <?php if ($cluster !== HM_Quest_Cluster_ClusterModel::NONCLUSTERED): ?><h3><?php echo $cluster;?></h3><?php endif; ?>
                <?php foreach ($questions as $question):?>
                    <?php echo $this->reportList($this->lists['question-' . $question->question_id], HM_View_Helper_ReportList::CLASS_NORMAL);?>
                <?php endforeach;?>
            <?php endforeach;?>
        <?php endif; ?>
        <div class="pagebreak"></div>
    <?php endforeach;?>
<?php endif; ?>