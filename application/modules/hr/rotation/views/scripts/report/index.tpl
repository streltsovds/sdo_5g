<?php //echo $this->headSwitcher(array('module' => 'rotation', 'controller' => 'report', 'action' => 'index', 'switcher' => 'report'), 'rotationCard');?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<div class="at-form-report">
    <div class="report-summary clearfix">
        <div class="left-wide-block">
            <?php echo $this->reportList($this->lists['general']);?>
        </div>
        <div class="right-photo-block">
            <?php if ($this->user->getPhoto()):?>
                <img src="<?php echo '/'.$this->user->getPhoto();?>" alt="<?php echo $this->escape($this->user->getName())?>" align="left"/>
            <?php else:?>
                <img src="<?php echo $this->baseUrl('images/people/nophoto.gif');?>" alt="<?php echo $this->escape($this->user->getName())?>" align="left"/>
            <?php endif;?>
        </div>
    </div>

    <div class="report-summary clearfix">
        <div class="left-block">
            <?php echo $this->progress;?>
        </div>
    </div>
</div>
<script>
    $( document ).ready(function () {
        $("a.pcard-link.lightbox").css({"float":"right", "margin-left":"10px", "margin-right":"0"});
    })
</script>