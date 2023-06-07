<div class="hm-proctoring__window" id="proctoringWindow">
	<div id="expand" class="hm-proctoring__expand"></div>
	<div id="reload" class="hm-proctoring__reload"></div>
	<div id="hider" class="hm-proctoring__hider">
		<div id="fio" class="hm-proctoring__fio-field"></div>
		<div class="hm-proctoring__toolbar hm-proctoring__toolbar--closed">
			<svg class="hm-proctoring__icon hm-proctoring__icon--closed" version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 255" width="20" height="20">
				<path d="M0 64l128 127L255 64z"/>
			</svg>
			<span class="hm-proctoring__field"></span>
		</div>
		<div class="hm-proctoring__toolbar hm-proctoring__toolbar--opened">
			<svg class="hm-proctoring__icon hm-proctoring__icon--opened" version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 255" width="20" height="20">
				<path d="M0 64l128 127L255 64z"/>
			</svg>
			<span class="hm-proctoring__field"></span>
		</div>
	</div>
	<div style="height: calc(100% - 25px);">
		<iframe class="js-proctor-window" allow="microphone; camera; display-capture" style="width: 100%;height: 100%;background-color: lightgrey;" src=""></iframe>
	</div>
</div>

<script>
window.addEventListener('beforeunload', ()=>{
	document.querySelector('#proctoringWindow iframe').remove();
})
document.addEventListener("DOMContentLoaded", function () {
	window.isOpened = false;
	var hiderEl = document.getElementById('hider')
	var proctoringWindow = document.getElementById('proctoringWindow')
	proctoringWindow.style.height = "24px"
	var opener = hiderEl.querySelector('.hm-proctoring__toolbar--opened')
	var closer = hiderEl.querySelector('.hm-proctoring__toolbar--closed')
	var expandEl = document.getElementById('expand');
	var reloadEl = document.getElementById('reload');
	var iframe = document.querySelector('.hm-proctoring__window iframe');
	var isExpanded = false;

	expandEl.addEventListener('click', function () {
		if (isExpanded === false) {
			proctoringWindow.classList.add('hm-proctoring__window--isExpanded');
			isExpanded = true;
		} else {
			proctoringWindow.classList.remove('hm-proctoring__window--isExpanded');
			isExpanded = false;
		}
	})
	reloadEl.addEventListener('click', function () {
		iframe.src = iframe.src;
	})

	function openProctorWindow() {
		opener.style.display = 'block';
		expandEl.style.display = 'block';
		reloadEl.style.display = 'block';
		closer.style.display = 'none';
		proctoringWindow.style.height = "90vh";
		window.isOpened = true;
	}

	function closeProctorWindow() {
		opener.style.display = 'none';
		closer.style.display = 'block';
		expandEl.style.display = 'none';
		reloadEl.style.display = 'none';
		proctoringWindow.classList.remove('hm-proctoring__window--isExpanded');
		proctoringWindow.style.height = "30px";
		isExpanded = false;
		window.isOpened = false;
	}

	hiderEl.addEventListener('click', function () {
		window.isOpened ? closeProctorWindow() : openProctorWindow()
	})

	window.openProctorWindow = openProctorWindow;
	window.closeProctorWindow = closeProctorWindow;
});
</script>

<style>
.hm-proctoring__window {
	background-color: grey;
	position: fixed;
	display: block;
	bottom: 0;
	right: 0;
	width: 320px;
	height: 90vh;
	z-index: 1;
	background-color: #fff;
	border-color: #fff;
	color: rgba(0,0,0,0.87);
	border-top-left-radius: 13px;
	transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
	box-shadow: 0px 11px 15px -7px rgba(0,0,0,0.2), 0px 24px 38px 3px rgba(0,0,0,0.14), 0px 9px 46px 8px rgba(0,0,0,0.12);
	text-decoration: none;
	overflow: hidden;
}
.hm-proctoring__window.hm-proctoring__window--isExpanded {
	top: 1rem;
	bottom: 1rem;
	left: 1rem;
	right: 1rem;
	width: calc(100vw - 3rem) !important;
	height: calc(100vh - 2rem) !important;
	box-shadow: 0px 11px 15px -7px rgba(0,0,0,0.2), 0px 24px 38px 3px rgba(0,0,0,0.14), 0px 9px 46px 8px rgba(0,0,0,0.12) !important;
}
.hm-proctoring__expand {
	height: 25px;
	width: 25px;
	background-image: url(/images/fullscreen.svg);
	background-size: 17px;
	background-repeat: no-repeat;
	background-position: center;
	cursor: zoom-in;
	position: absolute;
	right: 5px;
    top: 6px;
	display: none;
}
.hm-proctoring__reload {
	height: 25px;
	width: 25px;
	background-image: url(/images/reload.svg);
	background-size: 17px;
	background-repeat: no-repeat;
	background-position: center;
	cursor: pointer;
	position: absolute;
    right: 32px;
    top: 6px;
	display: none;
}
.hm-proctoring__hider {
	height: 38px;
	background-color: #62bf6e;
	cursor: pointer;
	border-bottom: 1px solid grey;
	border-radius: 13px 0 0 0;
}
.hm-proctoring__fio-field {
	height: 25px;
	background: transparent;
	position: absolute;
	width: 228px;
	left: 35px;
	right: 25px;
	color: white;
	overflow: hidden;
	text-overflow: ellipsis;
	line-height: 25px;
  	font-size: 1rem;
	top:6px;
}
.hm-proctoring__toolbar--closed {
	display: block;
	height: 100%;
}
.hm-proctoring__toolbar--opened {
	display: none;
	height: 100%;
}
.hm-proctoring__icon {
    margin-left: 10px;
    margin-top: 10px;
    margin-right: 10px;
    fill: #f5f5f5;
    width: 14px;
}
.hm-proctoring__icon--closed {
	transform: rotate(180deg);
}
.hm-proctoring__field {
	color: white;
	font-size: 16px;
	text-transform: uppercase;
	line-height: 25px;
	font-weight: bold;
	vertical-align: bottom;
	margin-left: -2px;
}
</style>
