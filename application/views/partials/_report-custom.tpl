<?php /* !!! DEPRECATED TPL, use hm-print-btn !!!*/ ?>
<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function(){
	
	$('#button-print').click(function(){
	    var url = '<?php echo $this->url?>';
	    var name = 'report';
	    var options = [ 'location=no', 'menubar=yes', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',');
	    window.open(url, name, options);
	});

setTimeout(function() {
    $('svg').find("> g > g[cursor='pointer']").remove();
}, 1000);
<?php if ($this->print):?>
setTimeout(_.bind(window.print, window), 2000);
<?php endif;?>


});
<?php $this->inlineScript()->captureEnd(); ?>
