HM.define('recruit.VacancyCandidate', {
    config: {
        url: {
            candidateApply: '',
            candidateIgnore: '',
        }
    },
    __construct: function() {
        this._initEvent();
    },
    _initEvent: function() {
        $(_.bind(this._onReady, this));
    }, 
    _onReady: function() {
        var candidates = this.initCandidates(),
            me = this;
        var vacancy_id = this.getParameter($("#vacancy-candidates-box"), "vacancy");
        $('#hm-candidate-action-submit').on('click', function() {

            var action = $('#hm-action').val();

            if (action === 'null') {
                return;
            }

            var checkboxes = $('.hm-resume-checkbox:checked'),
                data = {};
            if (!checkboxes.length) {
                return;
            }

            checkboxes.each(function() {

                var candidateInfo = candidates[this.value];
                data[candidateInfo.resumeId] = {
                    resumeHash: candidateInfo.resumeHash,
                    response: candidateInfo.response
                };

            });

            function success(data) {
                if (data.state == 'ok') {
                    for (var x in data.ids) {
                        $('#hm-vacancy-' + data.ids[x]).remove();
                    }
                } else {
                    alert("Произошла ошибка, кандидаты не добавлены");
                }
            }
            
            url = me.url[action];
            if (typeof(url) == "undefined") {
                throw new Error("URL for sending candidate save request is invalid. Check actions selector and recruit.VacancyCandidate object initialization script");
            }

            $('#hm-action').attr('disabled', 'disabled');
            $('#hm-candidate-action-submit').attr('disabled', 'disabled');

            $.ajax({
                url: url,
                type: 'post',
                data: {
                    vacancy_id: vacancy_id,
                    resumes: data
                },
                success: success,
                complete: function() {
                    $('#hm-action').val('null');
                    $('#hm-action').removeAttr('disabled');
                    $('#hm-candidate-action-submit').removeAttr('disabled');
                }
            });

        });
    },
    initCandidates: function() {
        var result = new Array,
            me = this;
        $(".vacancy-candidate").each(function() {
            var idPropertyMatch = $(this).attr("id").match(/[^\d]+([\d]+)$/i);
            result[idPropertyMatch[1]] = {
                'resumeId': idPropertyMatch[1],
                'resumeHash': me.getParameter($(this), 'hash'),
                'response': me.getParameter($(this), 'response'),
            };
        });
        return result;
    },
    getParameter: function(element, paramName) {
        var classAttr = element.attr("class");
        var splittedResult = classAttr.split(" ");
        for (i in splittedResult) {
            var v = splittedResult[i];
            matches = v.match(/^([^\s]+)\-([^\-]+)$/);
            if (matches !== null) {
                if (matches[1] === paramName) {
                    return matches[2];
                }
            }
        }
    }

});