<div class="hm-page-rate">
    <div class="hm-page-rate-header">
        <div class="hm-page-rate-header-rate<?php echo ($this->type == 'UNRATED' ? ' hm-page-rate-header-rate-unrated' : '') ?>">
            <span class="hm-page-rate-header-title">
                <span class="hm-page-rate-header-img"></span>
                <a href="#"><?php echo ($this->type == 'UNRATED')? $this->currentPollTitle : _('Рейтинг идей'); ?></a>
            </span>
            <?php if ($this->type == 'RATED'): ?>
                <span class="hm-page-rate-header-delimeter"></span>
                <span class="hm-page-rate-header-count"><?php echo $this->respondentsCount;?></span>
                <span class="hm-page-rate-header-delimeter"></span>
                <span class="hm-page-rate-header-stars">
                    <div class="hm-page-rate-header-stars-background">
                        <div class="hm-page-rate-header-stars-value" style="width: <?php echo intval($this->pageRank);?>%;"></div>
                    </div>
                    <div class="hm-page-rate-header-stars-mask"></div>
                </span>
                <span class="hm-page-rate-header-delimeter"></span>
                <span class="hm-page-rate-header-userrate"><?php echo $this->pageRankPosition ; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="hm-page-rate-content" id="hm-page-rate-content">
        <?php echo _('Загрузка вопроса');?>
    </div>
</div>
<?php $this->inlineScript()->captureStart(); ?>
    $('.hm-page-rate-header-title > a').on('click', function(e) {
        <?php if ($this->type == 'UNRATED'):?>
        e.preventDefault();
        $('.hm-page-rate-content').width(
            $('.hm-page-rate').outerWidth() - 56
        );
        $('.hm-page-rate-content').slideToggle(500);
        $('.hm-page-rate').toggleClass('hm-page-rate-opened');
        <?php else:?>
        document.location="<?php echo $this->url(array('module' => 'poll', 'controller' => 'result', 'action' => 'page-rank', 'quiz_id' => $this->currentPollId));?>";
        <?php endif;?>
    });

    function loadQuestion() {
        $.post('<?php echo $this->url(array('module' => 'poll', 'controller' => 'page', 'action' => 'get-question'));?>',{links: "<?php echo implode(',', $this->ids);?>"}, function(data) {
            $('#hm-page-rate-content').html(data);
        });
    }

    loadQuestion();
<?php $this->inlineScript()->captureEnd(); ?>