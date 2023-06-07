$(function(){
    $("span.form-score-binary.score_checkbox").on("click", function() {
        window.setTimeout(function() {
            $.ajax({
                url: "/at/kpi/user/get-user-progress"
            }).done(function( data ) {
                $('div.kpi__progress-bar').children().attr("aria-valuenow", data);
                $('div.ui-progressbar-value.ui-widget-header.ui-corner-left').css({'display': 'block', 'width': data+'%'});
            });
        }, 700)
    });

    $("input.hm_score_numeric").on("input", function() {
        window.setTimeout(function() {
            $.ajax({
                url: "/at/kpi/user/get-user-progress"
            }).done(function( data ) {
                $('div.kpi__progress-bar').children().attr("aria-valuenow", data);
                $('div.ui-progressbar-value.ui-widget-header.ui-corner-left').css({'display': 'block', 'width': data+'%'});
            });
        }, 700)
    });
});
