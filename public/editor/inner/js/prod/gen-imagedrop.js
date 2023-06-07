function imgDropInit(){
    $('.af-imgupload > a').click(function(event){
        dragShow();
        event.preventDefault();
    });

    $(window).keydown(function(e){
        if(e.keyCode === 27){
            dragHide();
        }
    });

	var uploadDragStarted=0;
	var dragTimer;

    //отмена дефолтных событий
    ('dragover dragenter drop dragleave').split(' ').forEach(function(eventName){
        window.addEventListener(eventName, function(event){
            event.preventDefault();
        });
    });

    function containsFiles(e){
        if(!e||e.target&&(e.target.tagName=='IMG'||e.target.tagName=='A'))
            return false;
        if (e.dataTransfer.types){
            for(var i=0;i<e.dataTransfer.types.length;i++){
                if(e.dataTransfer.types[i]=="Files"){
                    return true;
                }
            }
        }else{
            return true;
        }
        return false;
    }

    addEvent(window, 'dragenter', function(e){
    	if(!uploadDragStarted){
    		uploadDragStarted = containsFiles(e) ? 1 : 0;
    	}
    	if(!uploadDragStarted){
    		return;
    	}
    	clearTimeout(dragTimer);

    	dragShow();
    });
    addEvent(window, 'dragover', fileDragLeaveOver);
    addEvent(window, 'dragleave', fileDragLeaveOver);
    function fileDragLeaveOver(e){
    	if(!uploadDragStarted){
    		return;
    	}

    	clearTimeout(dragTimer);
    	dragTimer = setTimeout(function() {
    		dragHide();
    	}, 100);
    	if(e.dataTransfer){
    		e.dataTransfer.dropEffect='copy';
    	}
    };
    addEvent(window, 'drop', function(e){
    	dragHide();
    	if(fileCheck(e.dataTransfer.files[0])){

    		var formData = new FormData();
    		formData.append('upload_files', e.dataTransfer.files[0]);
    		sendFormData(formData);
    	}
    });


    function addEvent(elem, types, handler){
    	if(!elem || elem.nodeType == 3 || elem.nodeType == 8) // 3 - Node.TEXT_NODE, 8 - Node.COMMENT_NODE
    		return;

    	if(elem.setInterval && elem != window)
    		elem = window;

    	handler.elem = elem;
    	each(types.split(/\s+/), function(index,type){
    		if(elem.addEventListener){
    			elem.addEventListener(type, handler, false)
    		}else if(elem.attachEvent){
    			elem.attachEvent('on'+ type, handler)
    		}
    	});

    	elem=null
    }

	function each(e,t){
		var n,r = 0,i = e.length;
		if(i === undefined){
			for(n in e)
				if(t.call(e[n],n,e[n]) === false) break
		}else{
			for(var s = e[0]; r < i && t.call(s,r,s) !== false; s=e[++r]){}
		}
		return e;
	}

    function fileCheck(files){
		if(!files || !window.FileReader) return false;
		var imgsize= files.size>999999 ? (Math.round(files.size/10485.76)/100).toFixed(2)+'МБ' : (Math.round(files.size/1024)+'КБ');
        if(!(files.type == 'image/jpeg'||files.type == 'image/png'||files.type == 'image/gif'||files.type == 'image/svg+xml')){
			alert('Доступные форматы изображений: jpg, png, gif, svg');
			return false;
		}else if(files.size > (2 * Math.pow(2, 20))){
			alert('Слишком большое изображение (Макс: 2 МБ)');
			return false;
		}else{
			return true;
		}
		return false;
	}


    var progressHideTO;
	function sendFormData(formData){
		$.ajax({
			url: 'img-process.php',
			type: 'POST',
			data: formData,

			success: function(data){
				if(data && data != 0){
                    $('.image-url').val(data);
				}
			},
			xhr: function(){
				myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){
					addEvent(myXhr.upload, 'progress', function(e){
                        $('.af-progress i').stop().css({
                            visibility: 'visible',
                            opacity: 1,
                            width: ((e.loaded/e.total) * 100) + '%'
                        });
					});
				}
				return myXhr;
			},
			cache: false,
			contentType: false,
			processData: false
		}).done(function(){
            $('.af-progress i').stop().animate({opacity: 0}, 500, function(){
                $('.af-progress i').width(0).css('visibility', 'hidden');
            });

		});
	}



	function dragShow(){
		$('.dropzone').css('visibility', 'visible');
	}

	function dragHide(){
		$('.dropzone').css('visibility', 'hidden');
	}
}
