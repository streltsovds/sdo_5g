function getInitialCoursesList() {
    $.ajax({
        url: "/tc/application/ajax/get-initial-courses-list",
        data: {"type" : $("select#category").val()},
        type: "post",
        success: function (result) {
            $("select#subject_id").html(result)
        }
    })
}

function onCostItemChange(value) {
    if (value == 3 || value == 4) {
        $('#subject_id').attr('disabled', true);
        $('#subject_id').css('opacity', 0.6);


        $('#event_name').attr('disabled', false);
        $('#event_name').css('opacity', 1);
        $('#price').attr('disabled', false);
        $('#price').css('opacity', 1);
    }
    if (value == 1 || value == 2 || value == 5) {
        $('#subject_id').attr('disabled', false);
        $('#subject_id').css('opacity', 1);

        $('#event_name').attr('disabled', true);
        $('#event_name').css('opacity', 0.6);
        $('#price').attr('disabled', true);
        $('#price').css('opacity', 0.6);
    }
}

function addPeriodOptions(def, year, sessionId, applicationId, controller) {
    hm.core.Console.log(controller);
    var url = '/tc/'+controller+'/list/get-periods/session_id/'+sessionId;
    if (applicationId > 0) url += '/application_id/'+applicationId;
    $('select#period option').remove();
    if (def === true) {
        $.ajax({
            url: url,
            method: 'post',
            data: {'category' : $('select#category').val()},
            success: function(data) {
                $('select#period').html(data);
            }
        });
    } else {
        var months = {
            "01":"Январь",
            "02":"Февраль",
            "03":"Март",
            "04":"Апрель",
            "05":"Май",
            "06":"Июнь",
            "07":"Июль",
            "08":"Август",
            "09":"Сентябрь",
            "10":"Октябрь",
            "11":"Ноябрь",
            "12":"Декабрь"
        };
        $('select#period').html('')
        for (var i = 1; i <= 12; i++) {
            if (i<10) i = '0'+i;
            var date = year+'-'+i+'-01';
            $('select#period').append( '<option value="'+date+'">'+months[i]+' '+year+'</option>' );
        }
    }
}

$( document ).ready(function () {
    var category = $("select#category").val();
    if (category == 1) {
        $('fieldset#fieldset-typegroup').hide();
    } else {
        $('fieldset#fieldset-typegroup').show();
    }
});