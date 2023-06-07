<div class="diagnostics">
    <div class="diagnostics__subwrap">
        <div class="diagnostics__icon-col">
            <div class="diagnostics__globus"></div>
        </div>
        <div class="diagnostics__content-col">
            <?= $this->message ?>
        </div>
    </div>
    <div class="diagnostics__subwrap">
        <div class="diagnostics__content-col">
            <p class="diagnostics__current-browser">
                Ваш браузер: <?= $this->currentBrowserName ?> <?= $this->currentBrowserVersion ?>
            </p>
            <p class="diagnostics__description">
                Гарантирована работа в следующих версиях браузера:
            </p>
            <div class="diagnostics__browsers-list">
                <?php foreach ($this->browsers as $browserName => $browserInfo): ?>

                <div class="diagnostics__browser-item">
                    <div class="diagnostics__browser-icon diagnostics__browser-icon--<?= mb_strtolower($browserName) ?>"></div>
                    <p class="diagnostics__browser-description">
                        <?= $browserName ?>: <span class="diagnostics__browser-versions"><?= $browserInfo['min'] ?> – <?= $browserInfo['max'] ?></span>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="diagnostics__mediadevices-errors">
    </div>
    <div class="diagnostics__subwrap diagnostics__mediadevices">
        <div class="diagnostics__camera-wrapper">
            <div class="diagnostics__video-wrapper">
                <video id="videoDiagnostic" poster="/images/icons/camera-diagnostic.svg" style="object-fit: none;" width="350" height="300"></video>
            </div>
        </div>
        <div class="diagnostics__mic-wrapper">
            <div class="diagnostics__audio-wrapper">
                <p class="diagnostics__camera-status"><span class="diagnostics__blue-video"></span></p>
                <p class="diagnostics__mic-status"><span class="diagnostics__blue-mic"></span></p>
                <div class="diagnostic_audio">
                    <div class="diagnostic_audio__icon"></div>
                    <div class="diagnostic_audio_background"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

$(window).on('load',function(){
    const console = window.hm.core.Console;

    const videoElement = document.querySelector('#videoDiagnostic');

    const insertStreamInVideo = (stream) => {

        videoElement.srcObject = stream;

        videoElement.onloadedmetadata = () => {
            videoElement.play();
            videoElement.style = '';
        }

    }

    const setStatusText = (deviceName, errorName = 'ok') => {

        const deviceTexts = {
            'camera': {
                "NotFoundError" : 'Камера не подключена',
                "NotAllowedError" : "Доступ к камере не был предоставлен",
                "ok": "Камера работает"
            },
            'mic': {
                "NotFoundError" : 'Микрофон не подключен',
                "NotAllowedError" : "Доступ к микрофону не был предоставлен",
                "ok": "Микрофон работает"
            },
        }

        const statusElement = document.querySelector(`.diagnostics__${deviceName}-status`);
        const mod = errorName === 'ok' ? 'ok' : 'error';

        statusElement.classList.add(`diagnostics__${deviceName}-status--${mod}`);
        statusElement.innerHTML = `${statusElement.innerHTML}${deviceTexts[deviceName][errorName]}`;
    }

    navigator.mediaDevices.getUserMedia({video: true, audio: false})
        .then(stream => {
            insertStreamInVideo(stream);
            setStatusText('camera');
        })
        .catch(error => {
            setStatusText('camera', error.name);
        })

    navigator.mediaDevices.getUserMedia({video: false, audio: true})
        .then(stream => {
            playMicrophoneAudio(stream);
            setStatusText('mic');
        })
        .catch(error => {
            setStatusText('mic', error.name);
        })


    const playMicrophoneAudio = (stream) => {

        let bufferSize = 2048;
        let numberOfInputChannels = 1;
        let numberOfOutputChannels = 1;
        let AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioCtx = new AudioContext();
        
        let source = audioCtx.createMediaStreamSource(stream);
        let analyser = audioCtx.createAnalyser();
        let processor = audioCtx.createScriptProcessor(bufferSize, numberOfInputChannels, numberOfOutputChannels);

        
        source.connect(analyser);
        source.connect(processor);
        processor.connect(audioCtx.destination);

        processor.onaudioprocess = (e) => {
            let data = new Uint8Array(analyser.frequencyBinCount);
            analyser.getByteFrequencyData(data);
            let average = getAverageVolume(data);

            document.querySelector(".diagnostic_audio_background").setAttribute('style', `width: ${average}%`);
        }
    }
    const getAverageVolume = (array) => {
        let values = array.reduce((sum, value) => sum + value);
        return  Math.ceil(values / array.length);
    }


})


</script>


<style>
    .diagnostics {
        width: 100%;
        padding: 25px 34px 75px;
        font-size: 16px;
        line-height: 24px;
        min-height: 300px;
        box-sizing: border-box;
    }
    .diagnostics__mediadevices{
        justify-content: center;
        margin-top: 52px!important;
    }
    .diagnostics__mic-status,.diagnostics__camera-status{
        margin: 10px 0;
        display: flex;
        font-weight: 500;
    }
    .diagnostics__mic-status--error,.diagnostics__camera-status--error{
        color: red;
    }
    .diagnostics__mic-status--ok,.diagnostics__camera-status--ok{
        color: green;
    }
    .diagnostic_audio{
        position: relative;
        width: 350px;
        background: #5C5C5C;
        border-radius: 4px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        
    }
    .diagnostic_audio__icon{
        position: absolute;
        background: url('/images/icons/white-mic.svg') 50% no-repeat;
        width: 30px;
        height: 30px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 100;
    }
    .diagnostic_audio_background{
        position: relative;
        background-color:#65C173;
        transition: 0.05s;
        height: 46px;
        width: 0;
        border-radius: 4px;
    }
    .diagnostics__camera-wrapper{
        margin-right: 30px;
    }
    .diagnostics__blue-mic{
        background: url('/images/icons/blue-mic.svg') 50% no-repeat;
        display: block;
        width: 30px;
        height: 30px;
        margin-right: 20px;
    }
    .diagnostics__blue-video{
        background: url('/images/icons/blue-video.svg') 50% no-repeat;
        display: block;
        width: 30px;
        height: 30px;
        margin-right: 20px;
    }
    .diagnostics__audio-wrapper{
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .diagnostics__video-wrapper{
        background: #5C5C5C;
        border-radius: 6px;
    }
    .diagnostics__mic-wrapper{
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 350px;
    }
    .diagnostics__subwrap {
        display: flex;
    }
    .diagnostics__subwrap:not(:first-child) {
        margin-top: 15px;
    }
    .diagnostics__globus {
        content: '';
        width: 36px;
        height: 36px;
        display: block;
        background-image: url('/images/icons/expand_globe.svg');
        background-repeat: no-repeat;
        background-size: contain;
    }
    .diagnostics__browser-icon {
        content: '';
        width: 36px;
        height: 36px;
        display: block;
        background-repeat: no-repeat;
        background-size: contain;
        margin-bottom: 5px;
    }
    .diagnostics__browsers-list {
        display: flex;
        margin: auto;
        margin-top: 35px;
        width: 60%;
        justify-content: space-around;
    }
    .diagnostics__browser-icon--chrome,
    .diagnostics__current-browser-icon--chrome {
        background: url('/images/icons/browser_chrome.svg') 50% no-repeat;
    }
    .diagnostics__browser-icon--firefox,
    .diagnostics__current-browser-icon--firefox{
        background: url('/images/icons/browser_firefox.svg') 50% no-repeat;
    }
    .diagnostics__browser-icon--opera,
    .diagnostics__current-browser-icon--opera {
        background: url('/images/icons/browser_opera.svg') 50% no-repeat;
    }
    .diagnostics__browser-icon--edge,
    .diagnostics__current-browser-icon--edge{
        background-image: url('/images/icons/edge.svg');
    }
    .diagnostics__current-browser {
        font-weight: 700;
    }
    .diagnostics__current-browser-icon {
        content: '';
        width: 16px;
        height: 16px;
        display: block;
        background-repeat: no-repeat;
        background-size: contain;
    }
    .diagnostics__icon-col {
        display: flex;
        justify-content: flex-end;
        width: 36px;
        margin-right: 16px;
    }
    .diagnostics__content-col {
        display: flex;
        flex-direction: column;
        width: 100%;
        justify-content: center;
    }
    .diagnostics__browser-item {
        display: flex;
        color: #42526E;
        flex-direction: column;
        align-items: center;
    }
    .diagnostics__browser-versions {
        font-weight: 700;
    }
</style>