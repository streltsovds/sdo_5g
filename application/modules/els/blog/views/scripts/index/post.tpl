<div class="blog-post">
    <div class="post-header">
        <div class="post-title">
			<span><?php echo $blogPost->title?></span>
	        <div class="post-controls">
	            <?php if($this->isModerator):?>
	            <a class="edit" href="<?php echo $this->url(array(
	                'module' => 'blog',
	                'controller' => 'index',
	                'action' => 'edit',
	                'subject' => $this->subjectName,
	                'subject_id' => $this->subjectId,
	                'blog_id' => $blogPost->id
	            ), null, true)?>"></a>
	            <a class="delete" href="<?php echo $this->url(array(
	                'module' => 'blog',
	                'controller' => 'index',
	                'action' => 'delete',
	                'subject' => $this->subjectName,
	                'subject_id' => $this->subjectId,
	                'blog_id' => $blogPost->id
	            ), null, true)?>"></a>
	            <?php endif;?>
	        </div>
		</div>
    </div>
    <div class="spacer"></div>
    <div class="post-info">
        <span class="post-info-avatar">
            <img src="<?php echo $blogPost->author_avatar?>"/>
        </span>

        <div class="post-author">
            <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName,
                'subject_id' => $this->subjectId,
                'filter' => 'author',
                'author' => $blogPost->created_by
            ), null, true)?>"><?php echo $blogPost->author?></a><br/>
            <span><?php echo date('d.m.Y, H:i', strtotime($blogPost->created))?></span>
        </div>
    </div>
    <div class="spacer"></div>
    <div class="post-body formatted-text">
        <?php if($this->isFullView):?>
        <?php echo stripslashes($blogPost->body);?>
        <?php else:?>
        <?php echo $blogPost->getCut();?>
        <?php endif;?>
    </div>

    <?php if ((!$this->isFullView) && $blogPost->fullViewEnabled()): ?>
    <div class="post-more">
        <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->subjectName,
                'subject_id' => $this->subjectId,
                'blog_id' => $blogPost->id
            ), null, true)?>"><?php echo _('Читать далее')?></a>
    </div>
    <?php endif;?>

    <div class="spacer"></div>
    <?php if(count($blogPost->tags) > 0):?>
    <div class="post-tags">
        <?php echo _('Метки')?>:
        <?php $i=1; foreach($blogPost->tags as $tag):?>
            <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName,
                'subject_id' => $this->subjectId,
                'filter' => 'tag',
                'tag' => $tag->body), null, true)?>"><?php echo $tag->body?></a><?php if($i != count($blogPost->tags)):?>,<?php endif; $i++;?>
        <?php endforeach;?>
    </div>
    <?php endif;?>
    <div class="hm-blog-like">
        <?php echo $this->like(HM_Like_LikeModel::ITEM_TYPE_BLOG, $blogPost->id, isset($this->likes[$blogPost->id]) ? $this->likes[$blogPost->id] : null); ?>
    </div>
    <div class="post-comments">
        <?php if($blogPost->comments_count > 0):?>
        <a class="comments" href="<?php echo $this->url(array(
            'module' => 'blog',
            'controller' => 'index',
            'action' => 'view',
            'subject' => $this->subjectName,
            'subject_id' => $this->subjectId,
            'blog_id' => $blogPost->id
            ), null, true)?>#comments"><?php echo _('Комментарии')?></a>
            <b>(<?php echo $blogPost->comments_count?>)</b>
        <?php endif;?>

        <?php if($this->isFullView):?>
            <ul class="comments hm-blog-comment">
                <?php foreach($blogPost->comments as $comment):?>
                <li class="comment" id="comment_<?php echo $comment->id?>">
                    <div class="comment-info">
                        <ul class="comment-author">
                            <li class="avatar"><img src="<?php echo $comment->author_avatar?>"/></li>
                            <li class="name"><?php echo $comment->author?>,</li>
                            <li class="date"><?php echo date('d.m.Y, H:i', strtotime($comment->created))?><?php if ($comment->updated) { echo ' ('._('отредактировано').' '.date('d.m.Y, H:i', strtotime($comment->updated)).')'; } ?></li>
                            <li class="bookmark"><a rel="bookmark" title="<?php echo _('Ссылка на комментарий')?>" href="#comment_<?php echo $comment->id?>">#</a></li>
                            <?php
                            if ($this->currentUserId == $comment->user_id || $this->isModerator):
                            ?>
                            <li data-comment_id="<?php echo $comment->id ?>" class="name hm-blog-comment-edit"><a style="margin-left: 5px; vertical-align: middle; display: inline-block; width: 11px; height: 11px; background: url(/images/blog/controls-edit.png) center no-repeat" href="#"></a></li>
                            <li data-comment_id="<?php echo $comment->id ?>" class="name hm-blog-comment-delete"><a style="margin-left: 5px; vertical-align: middle; display: inline-block; width: 11px; height: 11px; background: url(/images/blog/controls-delete.png) center no-repeat" href="#"></a></li>
                            <?php
                            endif;
                            ?>
                        </ul>
                    </div>
                    <div class="comment-message hm-blog-comment-message"><?php echo nl2br($comment->message)?></div>
                </li>
                <?php
                // пользователи могут редактировать свои сообщения
                if ($this->currentUserId == $comment->user_id || $this->isModerator):
                ?>
                    <script>
                        HM.create('hm.ui.InlineEditor', {
                            renderTo: '#comment_<?php echo $comment->id?> .hm-blog-comment-message',
                            saveUrl: '<?php echo  $this->url(array(
                               'module' => 'blog',
                               'controller' => 'index',
                               'action' => 'comment-edit',
                               'comment_id' => $comment->id
                            )) ?>'
                        });
                    </script>
                <?php
                endif;
                ?>
                <?php endforeach;?>
            </ul>
            <script>
                $('.hm-blog-comment-edit').on('click', function(e) {
                    e.preventDefault();
                    var comment_id = $(this).data('comment_id');
                    $('#comment_' + comment_id + ' .hm-ui-InlineEditor').focus();
                });
                $('.hm-blog-comment-delete').on('click', function(e) {
                    e.preventDefault();
                    
                    var comment_id = $(this).data('comment_id');
                    
                    elsHelpers.confirm(
                        HM._('Уверены, что хотите удалить это сообщение?'),
                        HM._('Подтверждение')
                    ).done(function() {
                        var url = '<?php echo $this->url(array(
                                            'module' => 'blog',
                                            'controller' => 'index',
                                            'action' => 'comment-delete'
                                        ), null, true); ?>';
                                
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: {
                                comment_id: comment_id
                            },
                            success: function() {
                                $('#comment_' + comment_id).remove();
                            }
                        });
                    });
                });
            </script>
            <div class="spacer"></div>
            <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:blog:index:comment')):?>
            <?php echo $this->form?>
            <?php endif; ?>
        <?php elseif($blogPost->comments_count == 0):?>
            <a href="<?php echo $this->url(array(
                    'module' => 'blog',
                    'controller' => 'index',
                    'action' => 'view',
                    'subject' => $this->subjectName,
                    'subject_id' => $this->subjectId,
                    'blog_id' => $blogPost->id
                ), null, true)?>#comments_form">
            <?php echo _('Оставить комментарий')?></a>
        <?php endif;?>
    </div>
    <hr/>
</div>
