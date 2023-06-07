window.dialogInit = function(){
    $('.dialog-arrs a').click(function(e){
        var $replica = $(this).parent().parent().find('.scheme-dialog-replica.showed');

        for(var i=0; i<2; i++){
            if($(this).is('.darr-'+ (!i ? 'right':'left'))){
                var $nextItem = !i ? $replica.parent().next() : $replica.parent().prev();
                if((!i ? $nextItem.next().length : $nextItem.length)){
                    $(this).parent().find('a').addClass('showed');
                    $replica.removeClass('showed');

                    $nextItem.find('.scheme-dialog-replica').addClass('showed');
                    $('.darr-'+ (!i ? 'left':'right')).addClass('showed');

                    if((!i ? (!$nextItem.next().next().length) : (!$nextItem.prev().length))){
                        $(this).removeClass('showed');
                    }

                }
            }
        }

        e.preventDefault();
    });
}

function accordeon(el){
    $(el).parent().find('li').not(el).removeClass('showed');
    $(el).addClass('showed');
}

$(function(){
    window.dialogInit();

    $('.scheme-accordeon li').click(function(){
        accordeon(this);
    });
});
