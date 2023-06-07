var IFRAME_ID = window.frameElement.id.split("iframe-").filter(x=>x)[0];
let isFirstLoad = true;

(function(){
	"use strict";

	$(function(){
		var editor = CodeMirror.fromTextArea($(".output")[0], {
			mode: "text/html",
			indentWithTab: true,
			tabSize: 4,
			lineNumbers: true,
			readOnly: false
		});

		var inputBlockTO;
		tinymce.init({
			selector:".workspace div",
			plugins: "paste",
			setup : function(ed){
				ed.on("change input undo redo keydown keypress keyup", function(e){

					clearTimeout(inputBlockTO);

					//Если через 0.15 секунды не будет дейсвий, контент обновляется
					inputBlockTO = setTimeout(function(){
						inputChange(editor, ed, true);
					}, 150);
				});
				ed.on("init", function(e){
					// const urlParams = new URLSearchParams(window.location.search);
					const html = window.parent.iframeMarkup[IFRAME_ID];
					var loadContent = html;/*localStorage.getItem(IFRAME_ID);*/
					loadContent && e.target.setContent(loadContent);
					isFirstLoad = true;
					$("#viewbox").load(function(){
						inputChange(editor, ed, true);
					});
					editor.on("changes", function() {
						inputChange(editor, ed, false);
					})
				});
			}
		});


		//Фикс высоты висивига
		$(window).resize(function(){
			resize();
		}).resize();

		$(window).resize();
		for(let i=0; i<3; i++)
			setTimeout(() => $(window).resize(), 100*i);

		//Выделение текста при фокусе инпута с координатами
		$(".coord-hint, .image-url").focus(function(){
			$(this).select();
		});

		//Инициализация дропа изображений
		imgDropInit();

		//Флаг отображения границ для scheme-obj
		$(".border-show input[type=\"checkbox\"]").change(function(){
			if($(this).is(":checked")){
				$("#viewbox").contents().find(".scheme-obj-point").addClass("outline");
			}else{
				$("#viewbox").contents().find(".scheme-obj-point").removeClass("outline");
			}
		});

		initCodeToggle();
	});

	function inputChange(editor, ed, status){
		$(".text-questions").html("").hide();

		if(status) {
			var codeContent = ed.getContent();
		} else {
			var codeContent = editor.getValue();
		}
		var fOut = formatScheme(codeContent),
			formattedContent = fOut.html || "",
			schemes = fOut.schemes.map(item => item.replace(/scheme-|test-/g, "")),
			glossary = fOut.glossary;
		

		/*localStorage.setItem(IFRAME_ID, codeContent);*/

		// поиск тестов, генерация файла
		if(fOut.questions){
			blobToDataURL(makeTextFile(fOut.questions), function(dataText){
				var dataTextArray = dataText.split(";");
				dataTextArray[0] = dataTextArray[0] + ";charset=utf-8";
				$(".text-questions").show().append(
					"<a href=\""+ dataTextArray.join(";") +"\" target=\"_blank\">Открыть файл с тестами</a>"
				);
			});

		}

		if(glossary.length){
			let glossaryText = glossary.join("\n")
				.replace(/&mdash;/g, "—")
				.replace(/&ndash;/g, "–")
				.replace(/&laquo;/g, "«")
				.replace(/&raquo;/g, "»")
				.replace(/<[^>]*>/g, "");

			blobToDataURL(makeTextFile(glossaryText), function(dataText){
				var dataTextArray = dataText.split(";");
				dataTextArray[0] = dataTextArray[0] + ";charset=utf-8";
				$(".text-questions").show().append(
					"<a href=\""+ dataTextArray.join(";") +"\" target=\"_blank\">Открыть файл с терминами</a>"
				);
			});
		}

		// автовыравнивание кода
		if(status) {
			editor.getDoc().setValue(formattedContent);
			for(var i=0; i<editor.lineCount(); i++){
				editor.indentLine(i);
			}
		}

		// если присутствует схема «scheme-obj»,
		// отображаем чекбокс для отображения границ объектов
		if(schemes.indexOf("obj") > -1){
			$(".border-show").addClass("showed");

			//отобрааем границы объектов
			if($(".border-show input[type=\"checkbox\"]").is(":checked")){
				formattedContent = formattedContent.replace(/scheme-obj-point/g, "scheme-obj-point outline");
			}
		}else{
			$(".border-show").removeClass("showed");
		}

		//вставка кода в блок с исходником
		$(".output").html(formattedContent);
		//вставка кода в фрейм
		$("#viewbox").contents().find("main").html(formattedContent);

		//если присутствует нужная схема, отображаем поле с координатами
		var coordSchemeList = ["map", "dragndrop", "dialog", "obj"];
		if(coordSchemeList.map(item => schemes.map(item_ins => item == item_ins ).indexOf(true) >-1 ).indexOf(true) > -1){
			$(".coord-hint").addClass("showed");
			//инициализация отображения координат
			showMapCoords(coordSchemeList);
		}else{
			$(".coord-hint").removeClass("showed");
		}

		// сохраняем изменения
		if (!isFirstLoad) {
			window.parent.postMessage({
				id: IFRAME_ID,
				compiled: formattedContent,
				html: codeContent
			},
			"*");
		} else {
			isFirstLoad = false;
		}

		//инициализируем внутренние функции схем
		$("#viewbox").ready(function(){
			if(schemes.indexOf("dragndrop") > -1){
				for(let j=0; j<3; j++){
					$("#viewbox")[0].contentWindow.dndInit();
				}
			}

			if(schemes.indexOf("dialog") > -1)
				$("#viewbox")[0].contentWindow.dialogInit();
		});

	}

	function resize() {
		setTimeout(function () {
			var max = $(".mce-tinymce").css("border", "none").parent().outerHeight();
			max += -$(".mce-menubar.mce-toolbar").outerHeight() - $(".mce-toolbar-grp").outerHeight() - 1;
			$(".mce-edit-area").height(max);
		}, 200);
	}
	resize();

	function showMapCoords(coordSchemeList){
		var $coordHint = $(".coord-hint");
		$("#viewbox").contents().find(coordSchemeList.map(item => ".scheme-" + item).join(", ")).on("mousemove mouseover", function(event){

			if(event.altKey){
				var coord = {
					x: (event.pageX - $(this).offset().left) / $(this).outerWidth(),
					y: (event.pageY - $(this).offset().top) / $(this).outerHeight()
				};
				for(let axis in coord){
					coord[axis] = (~~Math.max(coord[axis] * 100)) / 1;
				}

				$coordHint
					.val(coord.x +", "+ coord.y);
			}else{
				$coordHint.removeAttr("style");
			}
		});
	}

	var textFile = null;
	function makeTextFile(text){
		var data = new Blob([text], {encoding:"UTF-8", type: "text/plain"});

		return data;
	}

	function blobToDataURL(blob, callback) {
		var a = new FileReader();
		a.onload = function(e) {callback(e.target.result);};
		a.readAsDataURL(blob);
	}
})();

function initCodeToggle() {
	var opened = +localStorage.getItem(`codeopened${IFRAME_ID}`) || 0;
	_toggle(opened);

	$(".html-toggle").click(function() {
		opened = !opened;
		_toggle(opened);
	});

	function _toggle(opened) {
		var methodName = opened
			? "addClass"
			: "removeClass";

		$(".output-wrap, .html-toggle, .workspace")[methodName]("opened");
		localStorage.setItem(`codeopened${IFRAME_ID}`, +opened);

		$(window).resize();
	}
}
