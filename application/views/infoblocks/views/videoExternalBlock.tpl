<?php if (count($this->videos)): ?>
<?php $video = array_shift($this->videos); ?>
<div id="video-embedded-container" style='text-align:center;'>
<?php if (!empty($video->embedded_code)): ?>
    <?php echo $video->embedded_code; ?>
<?php endif; ?>
</div>
<?php else: ?>
    <div align="center"><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif; ?>
<div class="more">
<span><?php echo _('Другие видео');?>:</span>
<?php if (count($this->videos)): ?>
<ul>
<?php foreach ($this->videos as $video): ?>
    <li><a href="<?php echo $this->baseUrl($this->url(array('module' => 'video', 'controller' => 'list', 'action' => 'get-embedded', 'videoblock_id' => $video->videoblock_id)))?>" data-videoblock="<?php echo $video->videoblock_id;?>"><?php echo $video->name; ?></a></li>
<?php endforeach;?>
</ul>
<?php endif;?>
</div>
<?php if ($this->showEditLink):?>
<div class="bottom-links">
    <a href="<?php echo $this->baseUrl($this->url(array('module' => 'video', 'controller' => 'list', 'action' => 'index')))?>"><?php echo _('Редактировать')?></a>
</div>
<?php endif;?>

<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function () {

    $(document).on('click', '.more a', function(e){
        var $url = $(this).attr('href');
        $.get($url).always(function () {
            
        }).done(function (data) {
            $('#video-embedded-container').html(data);
        }).fail(function () {
            $('#video-embedded-container').html('<?php echo _('Невозможно отобразить содержимое');?>');
        });
        
        e.preventDefault();
    });

});
<?php $this->inlineScript()->captureEnd(); ?>