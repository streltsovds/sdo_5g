<?php $this->inlineScript()->captureStart();?>
    $(function(){

        $('.at-form').addClass('at-form-with-additional');

        <?php if (!$this->attachment):?>
            $.get('<?php echo $this->url?>/blank/1', function(html){

                $('.at-form-additional').css('display', 'block');
                $('.at-form-additional').html(html);
            });
        <?php else: ?>
            $('.at-form-additional').css('display', 'block');
            $('.at-form-additional').html('<div class="at-form-report report-link-container"><a class="report-link" href="<?php echo $this->url?>"><?php echo _('Резюме кандидата');?></a></div>');
        <?php endif;?>
        });

<?php $this->inlineScript()->captureEnd();?>
