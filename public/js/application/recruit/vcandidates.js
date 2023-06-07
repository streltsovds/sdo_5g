$(function() {
    var candidates = initCandidates();
    var vacancy_id = getParameter($("#vacancy-candidates-box"), "vacancy");
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
                response: candidateInfo.response
            };

        });

        function success(response) {
            console.log(response);
            for (var vacancyId in data) {
                if (data.hasOwnProperty(vacancyId)) {
                    $('#hm-hh-vacancy-' + vacancyId).remove();
                }
            }
        }

        var url = action;

        if (url === 'null') {
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
});

function getParameter(element, paramName) {
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


function initCandidates() {
    var result = new Array;
    $(".vacancy-hh-candidate").each(function() {
        var idPropertyMatch = $(this).attr("id").match(/[^\d]+([\d]+)$/i);
        result[idPropertyMatch[1]] = {
            'resumeId' : idPropertyMatch[1],
            'resumeHash' : getParameter($(this), 'hash'),
            'response' : getParameter($(this), 'response'),
        };
    });
    return result;
}