<div class="at-form-report">
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
    <div class="report-summary clearfix">
        <div class="left-block">
            <?php $cardData = $this->cards['Fulltime1']; ?>
            <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($cardData['fields']);?>
        </div>
    </div>
    <!--div class="report-summary clearfix">
        <div class="left-block ">
            <?php $cardData = $this->cards['Fulltime2']; ?>
            <h2>Иконка<?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <img src="<?=$this->icon;?>"><br><br>
            <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($cardData['fields']);?>
        </div>
    </div-->
    <div class="report-summary clearfix">
        <div class="left-block">
            <?php $cardData = $this->cards['Fulltime3']; ?>
            <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($cardData['fields']);?>

            <?php $cardData = $this->cards['Fulltime4']; ?>
            <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($cardData['fields']);?>
        </div>
    </div>
    <div class="report-summary clearfix">
        <div class="left-block ">
            <?php $cardData = $this->cards['classifiers']; ?>
            <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($cardData['fields']);?>
        </div>
    </div>
    <?php $cardData = $this->cards['Fulltime_teachers']; ?>
    <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
    <div class="clearfix">
        <?php echo $this->reportTable($cardData['fields']);?>
    </div>

</div>
