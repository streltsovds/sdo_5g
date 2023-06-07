<!-- modal windows for accept and decline resume -->
<div id="accept-dialog<?php echo $this->user->MID; ?>" class="modal" title="Пригласить">
    <p>Вы действительно желаете пригласить данного кандидата на собеседование?</p><br>
    <label for="accept-comment<?php echo $this->user->MID; ?>">Пожалуйста, предложите удобное для Вас время:</label>
    <textarea tabindex="1" rows="5" cols="40" id="accept-comment<?php echo $this->user->MID; ?>" name="accept-comment<?php echo $this->user->MID; ?>"></textarea>
</div>
<div id="decline-dialog<?php echo $this->user->MID; ?>" class="modal" title="Отказать">
    <p>Вы действительно желаете отклонить данного кандидата?</p><br>
    <label for="decline-comment<?php echo $this->user->MID; ?>">Пожалуйста, прокомментируйте Ваше решение:</label>
    <textarea tabindex="1" rows="5" cols="40" id="decline-comment<?php echo $this->user->MID; ?>" name="decline-comment<?php echo $this->user->MID; ?>"></textarea>
</div>

<script>
    $(document).ready(function() {
        $('div#accept-dialog<?php echo $this->user->MID; ?>').dialog( "close" );
        $('div#accept-dialog<?php echo $this->user->MID; ?>').dialog({
            autoOpen: false,
            height: 320,
            width: 353,
            modal: true,
            open: function() {
                // clear textarea on open
                $("#accept-comment<?php echo $this->user->MID; ?>").val("")
            },
            buttons: {
                "Отменить": function() {
                    $('div#accept-dialog<?php echo $this->user->MID; ?>').dialog( "close" );
                },
                "Отправить комментарий": function() {
                    var comment = $("#accept-comment<?php echo $this->user->MID; ?>").val();
                    if ((comment !== "") && (comment.match(/(?!\s)./gi).length > 0)) {
                        $.ajax({
                            url: "/recruit/candidate/index/set-chief-comment",
                            data: {
                                "vacancy_candidate_id" : <?php echo $this->vacancyCandidate->vacancy_candidate_id; ?>,
                                "process_id" : <?php echo $this->processId; ?>,
                                "comment" : "Пригласить. " + $("#accept-comment<?php echo $this->user->MID; ?>").val()
                            },
                            dataType: 'json',
                            type: "post",
                            error: function(XMLHttpRequest, textStatus, errorThrown)  {
                                alert("Во время выполнения запроса произошла ошибка: " + errorThrown)
                            },
                            success: function(data){
                                $('div#accept-dialog<?php echo $this->user->MID; ?>').dialog( "close" );
                                $('.hm-report-buttons:first').html('<p style="font-weight: bold; float: right;">' + data + '</p>');
                                $('#button-accept-chief').remove();
                                $('#button-decline-chief').remove();
                            }
                        });
                    } else {
                        alert ("Поле комментария обязательно для заполнения!");
                    }
                }
            }
        });

        $('div#decline-dialog<?php echo $this->user->MID; ?>').dialog( "close" );
        $('div#decline-dialog<?php echo $this->user->MID; ?>').dialog({
            autoOpen: false,
            height: 320,
            width: 353,
            modal: true,
            open: function() {
                // clear textarea on open
                $("#decline-comment<?php echo $this->user->MID; ?>").val("")
            },
            buttons: {
                "Отменить": function() {
                    $('div#decline-dialog<?php echo $this->user->MID; ?>').dialog( "close" );
                },
                "Отправить комментарий": function() {
                    var comment = $("#decline-comment<?php echo $this->user->MID; ?>").val();
                    if ((comment !== "") && (comment.match(/(?!\s)./gi).length > 0)) {
                        $.ajax({
                            url: "/recruit/candidate/index/set-chief-comment",
                            data: {
                                "vacancy_candidate_id" : <?php echo $this->vacancyCandidate->vacancy_candidate_id; ?>,
                                "process_id" : <?php echo $this->processId; ?>,
                                "comment" : "Отказать. " + $("#decline-comment<?php echo $this->user->MID; ?>").val()
                            },
                            dataType: 'json',
                            type: "post",
                            error: function(XMLHttpRequest, textStatus, errorThrown)  {
                                alert("Во время выполнения запроса произошла ошибка: " + errorThrown)
                            },
                            success: function(data){
                                $('div#decline-dialog<?php echo $this->user->MID; ?>').dialog( "close" );
                                $('.hm-report-buttons:first').html('<p style="font-weight: bold; float: right;">' + data + '</p>');
                                $('#button-accept-chief').remove();
                                $('#button-decline-chief').remove();
                            }
                        });
                    } else {
                        alert ("Поле комментария обязательно для заполнения!");
                    }
                }
            }
        });

        $('input#button-accept-chief').click(function(){ $('div#accept-dialog<?php echo $this->user->MID; ?>').dialog('open'); });
        $('input#button-decline-chief').click(function(){ $('div#decline-dialog<?php echo $this->user->MID; ?>').dialog('open'); });
    });
</script>