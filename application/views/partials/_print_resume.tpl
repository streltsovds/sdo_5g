<?php /* !!! DEPRECATED TPL, use hm-print-btn hm-resume-btn !!!*/ ?>
<script>
    $(document).ready(function(){

        $('#button-print').click(function(){
            var url = '<?php echo $this->print_url?>';
            var name = 'report';
            var options = [ 'location=no', 'menubar=yes', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',');
            window.open(url, name, options);
        });

        $('#button-decline').click(function(){
            var url = '<?php echo $this->decline_url?>';

            $('#button-decline').prop('disabled', true);
            $.get(url, function(){
                $('#button-decline').css('display', 'none');
            });
        });

        setTimeout(function() {
            $('svg').find("> g > g[cursor='pointer']").remove();
        }, 1000);
        <?php if ($this->print):?>
        setTimeout(_.bind(window.print, window), 2000);
        <?php endif;?>

    });
</script>
