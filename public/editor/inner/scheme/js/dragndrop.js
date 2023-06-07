'use strict';
var DNDTYPE = {};
    DNDTYPE.feedback = true; // feedback, checkbox

var onDragging = false,    // флаг перетаскивания
    dragBox,               // перетаскиваемый элемент
    moveOffset = {},       // позиция курсора на элементе в момент старта перетаскивания
    oldOffset = {},        // последняя позиция элемента
    noDropFlag = false,
    dragListPadding = 15,
    fullMessageShowed = false;

var dragBoxMargin;          // тут запишится нужный отступ

Math.trunc = Math.trunc || function(x){return (x - x %  1)};

window.dndInit = function(){
    if(!$('.dnd-dragbox').length)
        return;

    onDragging = false,    // флаг перетаскивания
    dragBox,               // перетаскиваемый элемент
    moveOffset = {},       // позиция курсора на элементе в момент старта перетаскивания
    oldOffset = {},        // последняя позиция элемента
    noDropFlag = false;


    $('.dnd-dragbox').each(function(i){
        this.curPosition = {   // текущая позиция элемента
            left: 0,
            top: 0
        };

        $(this)
            .addClass('static fixed')
            .data('access', false)
            .css({transform:'', WebkitTransform:''})
            .css({marginLeft: dragBoxMargin +'px'});

        this._startOffset = {
            left: $(this).offset().left - parseInt($(this).parent().css('padding-left')),
            top: $(this).offset().top - parseInt($(this).parent().css('padding-top')),
        }
    });

    $('.dnd-dropbox').each(function(i){
        this._borders = {
            top: $(this).offset().top,
            right: $(this).offset().left + $(this).outerWidth(),
            bottom: $(this).offset().top + $(this).outerHeight(),
            left: $(this).offset().left
        };


        $(this).attr('data-index', i);

        if(!$(this).data('size') && $(this).data('size') != '0'){
            $(this).attr('data-size', 1)
        }
    });


    noDropFlag = false;
    $('.dnd-dropbox').each(function(){
        var dropBox = this;

        // Автораспределение драгбоксов по ширине
        var $dragBoxEl, dragBoxCount, dragBoxWidth, dragWrapWidth, dragWrapFreeSpace, dbCounter, dbOffset;

        $dragBoxEl = $(this).parents('.dnd').find('.dnd-dragbox');
        $dragBoxEl.parent().css({
            padding: dragListPadding + 'px',
            paddingBottom: 0
        });


        dragBoxCount = ($dragBoxEl.length > 7 ? Math.ceil($dragBoxEl.length/2) : $dragBoxEl.length);
        dragBoxWidth = $dragBoxEl.width();
        dragWrapWidth = $dragBoxEl.parent().width() - (dragListPadding);


        dragWrapFreeSpace = dragWrapWidth - (dragBoxWidth * dragBoxCount)
        dragBoxMargin = (dragWrapFreeSpace / dragBoxCount) - (parseInt($dragBoxEl.parent().css('padding-right')) / dragBoxCount);
        dragBoxMargin = Math.floor(dragBoxMargin * 10) / 10;

        dbCounter = 0;

        $dragBoxEl.each(function(i){
            $(this).css({
                marginLeft: dragBoxMargin +'px'
            });
            dbCounter = dbCounter >= dragBoxCount-1 ? 0 : dbCounter+1;
        });

        if(dragBoxMargin < 0){
            $dragBoxEl.parent().css({
                paddingTop: dragListPadding +'px',
                paddingLeft: (-dragBoxMargin/2 + dragListPadding*2) +'px',
                paddingRight: 0
            });
        }else{
            $dragBoxEl.parent().css({
                paddingTop: dragListPadding +'px',
                paddingLeft: dragListPadding +'px'
            })
        }



        this._borders = {
            top: $(this).offset().top,
            right: $(this).offset().left + $(this).outerWidth(),
            bottom: $(this).offset().top + $(this).outerHeight(),
            left: $(this).offset().left
        };

        var dropHoverRemoved = true,
            dragBoxSizeDefined = false,
            dragBoxOWidth, dragBoxOHeight;

        $(window).mousemove(function(event){
            var dragBoxCenter = {};

            if(dragBox){
                if(!dragBoxSizeDefined){
                    dragBoxOWidth = $(dragBox).outerWidth()/2,
                    dragBoxOHeight = $(dragBox).outerHeight()/2;
                    dragBoxSizeDefined = true;
                }
                dragBoxCenter = {
                    x: $(dragBox).offset().left + dragBoxOWidth,
                    y: $(dragBox).offset().top + dragBoxOHeight
                }
            }else{
                return;
            }

            if(
                (onDragging && dragBox) &&
                (dropBox._borders.left < dragBoxCenter.x && dropBox._borders.right > dragBoxCenter.x) &&   // X axis
                (dropBox._borders.top < dragBoxCenter.y && dropBox._borders.bottom > dragBoxCenter.y)       // Y axis
            ){
                if(dropHoverRemoved){
                    $(dropBox).addClass('ondrophover');
                    dropHoverRemoved = false;
                }
            }else{
                if(!dropHoverRemoved){
                    $(dropBox).removeClass('ondrophover');
                    dropHoverRemoved = true;
                }
            }
        });
    });
}

window.dndInit();
window.dndInit();
window.dndInit();

$(function(){
    for(let i=0; i<5; i++)
        window.dndInit();

    $(window).resize(function(){
        window.dndInit();
    }).mousedown(function(event){
        if(event.which == 1 && ($(event.target).is('.dnd-dragbox') || $(event.target).is('.dnd-dragbox span'))){

            onDragging = true;
            dragBox = $(event.target).is('.dnd-dragbox') ? event.target : $(event.target).parent()[0];
            oldOffset = {
                left: $(dragBox).offset().left - dragBox.curPosition.left,
                top: $(dragBox).offset().top - dragBox.curPosition.top
            };
            moveOffset = {
                left: event.pageX - $(dragBox).offset().left,
                top: event.pageY - $(dragBox).offset().top
            }

            $(dragBox).removeClass('static fixed').removeAttr('data-access').data('access', false);
        }
    }).mouseup(function(){
        if(onDragging){
            var dropBox = document.querySelectorAll('.dnd-dropbox.ondrophover'),
                dropSize = $('.dnd-dragbox[data-for="'+ ($(dropBox).data('index')) +'"]').length || 0,
                busyCount = $('.dnd-dragbox[data-access="'+ $(dropBox).data('index') +'"]').length || 0;

            if($(dropBox).length /*&& dropSize > busyCount*/){  // oversize block
                $(dragBox).attr('data-access', $(dropBox).data('index'));

                var ondropOffset = {
                    left: $('.dnd-dropbox.ondrophover').offset().left + ( ($('.dnd-dropbox.ondrophover').outerWidth() - $(dragBox).outerWidth()) / 2 ),
                    top: $('.dnd-dropbox.ondrophover').offset().top + ( ($('.dnd-dropbox.ondrophover').outerHeight() - $(dragBox).outerHeight()) / 2)
                }
                var transformCalc = (function(){
                    var retOut = {},
                        retOutOff = ['left', 'top'];

                    for(var i=0; i<retOutOff.length; i++){
                        retOut[retOutOff[i]] = ondropOffset[retOutOff[i]] - dragBox._startOffset[retOutOff[i]];
                        retOut[retOutOff[i]] -= parseInt($(dragBox).parent().css('padding-'+ retOutOff[i]));
                    }

                    return 'translate('+ retOut['left'] +'px, '+ retOut['top'] +'px)'
                })();

                $(dragBox).css({
                    WebkitTransform: transformCalc,
                    transform: transformCalc
                }).addClass('static');
            }else{
                $(dragBox).addClass('static fixed').removeAttr('data-access').css({transform:'', WebkitTransform:'', marginLeft: dragBoxMargin +'px'});
                moveOffset = oldOffset = dragBox.curPosition = {
                    left: 0,
                    top: 0
                }
            }

        }

        var msgShowed = false,
            fullDropCount = [],
            $dropBoxHandler;

        $('.dnd').each(function(){
            $(this).find('.dnd-dropbox').each(function(ind){
                var dropIndex = parseInt($(this).data('index')) || 0,
                    $dragBoxSibling = $(this).parents('.dnd').find('.dnd-dragbox[data-for="'+ dropIndex +'"]'),
                    dropSize = $dragBoxSibling.length || 0;


                var intToCheck = 0, truedrop = true;
                for(var i=0; i<dropSize; i++){
                    var accessNum = $dragBoxSibling.eq(i).attr('data-access');
                    if(accessNum == dropIndex){
                        intToCheck += 1/dropSize + 1/1e5;
                        fullDropCount[ind] = !!~~intToCheck;

                    }else if(accessNum != undefined && accessNum != dropIndex){
                        truedrop = false;
                        fullDropCount[ind] = false;
                    }
                }

                if(DNDTYPE.feedback){ // feedback
                    var dataFor = $(dragBox).data('for');
                    $(this).removeClass('false-drop true-drop');
                    $(this).find('.block-hint').removeAttr('style');

                    if(dataFor == dropIndex
                    && dataFor == $(dropBox).data('index')) {
                        $(this).addClass('true-drop');

                        if(!msgShowed
                        && $(dropBox).data('feedback') != undefined) {
                            var fbMsg = $(dropBox).data('feedback') || 'No message';

                            var $el_hint = $(this).find('.block-hint[data-hint="'+ ($(dragBox).data('hint')) +'"]')
                                .text();

                            if($el_hint) {
                                $(this).find('.block-hint[data-hint="'+ ($(dragBox).data('hint')) +'"]').css({
                                    visibility: 'visible',
                                    opacity: 1
                                });
                            }

                            doFeedBack(fbMsg, dropIndex);
                        }

                        msgShowed = true;
                    } else if(dataFor != undefined && dataFor != dropIndex) {
                        $(this).addClass('false-drop');
                    }

                } else if(DNDTYPE.checkbox) { // checkbox
                    $('input#var'+ dropIndex).prop('checked', !!~~intToCheck);
                }

            });

            $('.dnd-dropbox:not(.ondrophover)').each(function() {
                $(this).removeClass('false-drop true-drop');
                $(this).find('.block-hint').css({
                    visibility: 'hidden',
                    opacity: 0
                });
            })

            var finalDMessage = $(this).find('.dnd-workspace').data('finalmsg');
            if(fullDropCount.indexOf(false) > -1 || $(this).find('.dnd-dropbox').length != arrayRealLength(fullDropCount)){
                fullMessageShowed = false;
            }
            if(!fullMessageShowed && finalDMessage && fullDropCount.indexOf(false) < 0 && $(this).find('.dnd-dropbox').length == arrayRealLength(fullDropCount)){
                doFullFeedBack(finalDMessage);
                fullMessageShowed = true;
            }

        });

        onDragging = false;
    }).mousemove(function(event){
        if(onDragging && dragBox){
            $(dragBox).css({
                transform: 'translate('+
                    (event.pageX - moveOffset.left - oldOffset.left) +'px, '+
                    (event.pageY - moveOffset.top - oldOffset.top) +'px'+
                ')',
                WebkitTransform:  'translate('+
                    (event.pageX - moveOffset.left - oldOffset.left) +'px, '+
                    (event.pageY - moveOffset.top - oldOffset.top) +'px'+
                ')',
            });

            dragBox.curPosition = {
                left: event.pageX - moveOffset.left - oldOffset.left,
                top: event.pageY - moveOffset.top - oldOffset.top
            }

        }
    });

    function doFeedBack(msg, dropid){
        // console.log(msg, dropid);
    }

    function doFullFeedBack(msg){
        alert(msg);
    }

    function arrayRealLength(array){
        let output = 0;
        for(var i=0; i<array.length; i++){
            if(array[i])
                output++
        }
        return output;
    }
});
