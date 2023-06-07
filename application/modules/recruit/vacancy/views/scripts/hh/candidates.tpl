<style type="text/css">
    .hm-hh-candidate {
        padding: 10px;
        margin: 2px 2px 2px 20px;
        font-family: Arial, Verdana, Helvetica, sans-serif;
        font-size: 14px;
        border-top: 1px solid #dfdfdf;
    }
    
    .hm-hh-candidate-checkbox {
        width: 20px;
        float: left;
    }

    .hm-hh-candidate-photo {
        width: 120px;
        float: left;
    }
    
    .hm-hh-candidate-photo-blank {
        background: url(http://i.hh.ru/css/ambient/blocks/output/photo.png) 2px 8px no-repeat;
        height: 90px;
    }

    .hm-hh-candidate .output__photo img {
        max-height: 100%;
        max-width: 100%;
        -moz-border-radius: 10px;
        -webkit-border-radius: 10px;
        -khtml-border-radius: 10px;
        border-radius: 10px;
    }
    
    .hm-hh-candidate-description {
        margin-left: 120px;
    }

    .hm-hh-candidate .output__info {
        white-space: nowrap;
        text-align: right;
        float: right;
        width: 180px;
        padding-top: 1px;
        margin-right: 20px;
    }

    .hm-hh-candidate .output__name {
        font-weight: bold;
        line-height: 22px;
    }

    .hm-hh-candidate .output__main {
        margin-right: 35%;
        line-height: 20px;
    }

    .hm-hh-candidate .output__compensation, .hm-hh-candidate .output__nocompensation, .hm-hh-candidate .output__age {
        font-weight: bold;
        white-space: nowrap;
        display: inline-block;
        text-align: right;
    }

    .hm-hh-candidate .output__compensation {
        float: left;
    }

    .hm-hh-candidate .output__title {
        font-family: verdana,arial,sans-serif;
        color: #666;
        font-size: 11px;
        padding: 6px 0 1px;
        line-height: 14px;
        margin-top: 4px;
    }

    .hm-hh-candidate .output__indent {
        padding: 0 0 2px 20px;
    }

    .hm-hh-candidate .g-switcher {
        border-bottom: 1px dotted #076cc7;
        text-decoration: none;
        color: #076cc7;
        cursor: pointer;
    }

    .hm-hh-candidate .g-switcher-000, .m-switcher_000 {
        border-bottom-color: #000;
        color: #000;
    }

    .hm-hh-candidate .output__indent .output__small {
        font-family: verdana,arial,sans-serif;
        font-size: 11px;
        color: #999;
        text-transform: lowercase;
    }

    .hm-hh-candidate .output__lastexp {
        font-size: 12px;
        padding: 2px 0 6px;
        line-height: 18px;
    }

    .hm-hh-candidate .g-expandable {
        display: none;
    }

    .hm-hh-candidate .b-v-responses-list-phone {
        white-space: nowrap;
        color: #444;
        margin-right: 10px;
    }

    .hm-hh-candidate .output__indent .b-v-responses-list-phone, .hm-hh-candidate .output__indent .b-v-responses-list-phoneblock {
        font-weight: normal;
        color: #000;
        line-height: 20px;
    }
</style>
<?php
    if (!$this->hh_vacancy_id) {
        ?>
        Ещё не создана вакансия на hh.ru
        <?php
        return;
    }
    
    if (empty($this->candidates)) {
        ?>
        Ещё нет ни одного кандидата на вакансию.
        <?php
        return;
    }
    
    $jsArray = array();
    
    foreach ($this->candidates as $key => $candidate) {
        echo '<div id="hm-hh-vacancy-'.$key.'">'.
                 '<div class="hm-hh-candidate-checkbox"><input class="hm-hh-resume-checkbox" type="checkbox" value="'.$key.'"></div>'.
                 '<div class="hm-hh-candidate">'.
                     $candidate['description'].
                 '</div>'.
             '</div>';

        $jsArray[$key] = array(
            'resumeId'   => $candidate['resumeId'],
            'resumeHash' => $candidate['resumeHash'],
            'response'   => $candidate['response']
        );
    }

?>
<div>
    <select id="hm-hh-action">
        <option value="null">Выберите действие</option>
        <option value="invite">Включить в список кандидатов</option>
        <option value="ignore">Исключить из результатов поиска</option>
    </select>
    <button id="hm-hh-action-submit">ОК</button>
</div>
<script>
    (function() {

        $('.g-switcher').on('click', function(event) {
            $(this).parent().find('.g-expandable').toggle();
            event.preventDefault();
        });

        var candidates = <?php echo json_encode($jsArray); ?>,
            vacancy_id = <?php echo $this->vacancy_id; ?>;
        
        $('#hm-hh-action-submit').on('click', function() {
            
            var action = $('#hm-hh-action').val();
            
            if (action === 'null') {
                return;
            }
                        
            var checkboxes = $('.hm-hh-resume-checkbox:checked'),
                data = {};

            if (!checkboxes.length) {
                return;
            }
            
            checkboxes.each(function() {

                var candidateInfo = candidates[this.value];
                data[candidateInfo.resumeId] = {
                    resumeHash: candidateInfo.resumeHash,
                    response:   candidateInfo.response
                };
                
            });

            function success () {
                for (var vacancyId in data) {
                    if (data.hasOwnProperty(vacancyId)) {
                        $('#hm-hh-vacancy-' + vacancyId).remove();
                    }
                }
            }
            
            var url;
            
            if (action === 'ignore') {
                url = '/recruit/vacancy/hh/ignore-resumes';
            } else if (action === 'invite') {
                url = '/recruit/vacancy/hh/invite-resumes';
            } else {
                return;
            }

            $('#hm-hh-action').attr('disabled', 'disabled');
            $('#hm-hh-action-submit').attr('disabled', 'disabled');
            
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    vacancy_id: vacancy_id,
                    resumes: data
                },
                success: success,
                complete: function() {
                    $('#hm-hh-action').val('null');
                    $('#hm-hh-action').removeAttr('disabled');
                    $('#hm-hh-action-submit').removeAttr('disabled');
                }
            });
            
        });
        
        
    })();
</script>