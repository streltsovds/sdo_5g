$(function () {

    $("#study-group-filter").change(function () {
        var selectedOption = $("#study-group-filter").val();
        $.ajax({
            url: '/report/progress/index',
            type: 'POST',
            data: {
                group_name: selectedOption,
                from_date: $("input#from-datepicker").val(),
                to_date: $("input#to-datepicker").val(),
                report_type: $("input:checked").val()
            },
            dataType: 'json',
            success: function (data) {
                var names = [];
                var arrIndex = 0;
                $.each(data, function (index, value) {
                    names[arrIndex++] = index;
                });
                var divs = $("div.progress-report-item-container");
                $.each(divs, function () {
                    $(this).css({"display": "none"});
                    if (names.includes($(this).attr('id'))) {
                        $(this).css({"display": "block"});
                    }
                })
            }
        });
    });

    function reportProgressGroups() {

        var selectedOption = $("#courses-filter").val();
        $.ajax({
            url: '/report/progress/index',
            type: 'POST',
            data: {
                course_name: selectedOption,
                from_date: $("input#from-datepicker").val(),
                to_date: $("input#to-datepicker").val(),
                report_type: $("input:checked").val()
            },
            dataType: 'json',
            success: function (data) {
                var names = [];
                var arrIndex = 0;
                $.each(data, function (index, value) {
                    names[arrIndex++] = index;
                });
                var divs = $("div.progress-report-item-container");
                $.each(divs, function () {
                    $(this).css({"display": "none"});
                    if (names.includes($(this).attr('id'))) {
                        $(this).css({"display": "block"});
                    }
                })
            }
        });
    }

    window.reportProgressGroups = reportProgressGroups();
});
