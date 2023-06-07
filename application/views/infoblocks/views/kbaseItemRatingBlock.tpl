<div style="margin:10px;text-align: center;">
<?php
$kbase_assessment_url = '/kbase/assessment/index'
. '/resource_id/' . $this->kbase_assessment['resource_id']
. '/type/' . $this->kbase_assessment['type'];
?>
<!-- Виджет оценки ресурса -->
<?php $this->headScript()->appendFile($this->baseUrl('js/lib/jquery/jquery.rating-2.0.min.js')); ?>
<?php $this->headLink()->appendStylesheet( $this->baseUrl('css/jquery/jquery.rating.css') ); ?>
<div id="kbase_assessment" >
    <input type="hidden" name="val" value="<?php echo $this->kbase_assessment['value']['value']; ?>"/>
    <input type="hidden" name="votes" value="<?php echo $this->kbase_assessment['value']['count']; ?>"/>
</div>
<script>
    $(document).ready(function(){
        $('#kbase_assessment').rating({
            fx: 'full',
            image: '/images/jQuery/rating/stars.png',
            loader: '/images/jQuery/rating/ajax-loader.gif',
            url: '<?php echo $kbase_assessment_url ?>',
            callback: function(response){
                this.vote_success.fadeOut(10000);
            }
        });
    });
</script>
</div>
