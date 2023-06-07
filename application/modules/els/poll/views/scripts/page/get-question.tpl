<h1><?php if ($this->pollTitle) echo $this->pollTitle; ?></h1>
<?php if ($this->questionText) echo $this->questionText; ?>
<div style="padding: 10px 0 0 5px; line-height: 20px;">

    <?php
    if ( $this->question->qtype == HM_Question_QuestionModel::TYPE_FREE ):
    ?>
    <textarea name="answer" id="page_rate_free_answer_result" style="color: #0b0b0b; margin-top: 10px; border: 1px solid black; resize: none; width: 100%; box-sizing: border-box; height: 70px;"></textarea>
    <?php
    else:
    ?>
    <?php foreach($this->answers as $answer): ?>
    <input name="answers[]" type="<?php echo ($this->question->qtype == HM_Question_QuestionModel::TYPE_ONE) ? 'radio' : 'checkbox'?>" id="check_<?php echo $answer->answer_id?>" value="<?php echo $answer->answer_id?>" style="vertical-align: middle;"><label style="margin-left: 3px;" for="check_<?php echo $answer->answer_id?>"><?php echo $answer->answer_title?></label><br>
    <?php endforeach; ?>
    <?
    endif;
    ?>

    <button id='page_rate_submit' style="paading: "><?php echo _('Сохранить')?></button><!--<button id='page_rate_cancel'>Отменить</button>-->

    <script>
        var url = '<?php echo $this->url(array(
                'module'      => 'poll',
                'controller'  => 'page',
                'action'      => 'save-answer',
                'question_id' => $this->question->kod,
                'link_id'     => $this->linkID,
                'quiz_id'     => $this->pollID),null,true);?>';
        $('#page_rate_submit').on('click', function() {
           if ($('#hm-page-rate-content input:checkbox:checked').serialize()) {
                var params = $('#hm-page-rate-content input:checkbox:checked').serialize();
           } else if ($('#hm-page-rate-content input:radio:checked').serialize()) {
                var params = $('#hm-page-rate-content input:radio:checked').serialize();
            } else if ($('#page_rate_free_answer_result').val()) {
                var params = $('#page_rate_free_answer_result').serialize();
            } else {
                alert('<?php echo _('Ответьте на вопрос');?>');
                return;
            }

            $.post(url, params, function() {
                loadQuestion();
            });
        });


        $('#page_rate_cancel').on('click', function(){
            loadQuestion();
        });
    </script>
</div>