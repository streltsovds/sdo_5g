<?php $this->headScript()
    ->prependFile( $this->serverUrl('/js/api/scorm.js') )
    ->prependFile( $this->baseUrl('js/api/request.js') ); ?>

<?php
$utd = $this->userTrackData;

// Standard Data Type Definition
$CMIBlank      = '^$';
$CMIString255  = '^.{0,255}$';
//$CMIString4096 = '^[.|\\n|\\r]{0,4095}$';
$CMIString4096 = '^.{0,4096}$';
$CMITime       = '^([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})(\.[0-9]{1,2})?$';
$CMITimespan   = '^([0-9]{2,4}):([0-9]{2}):([0-9]{2})(\.[0-9]{1,2})?$';
$CMIInteger    = '^\\d+$';
$CMISInteger   = '^-?([0-9]+)$';
$CMIDecimal    = '^-?([0-9]{0,3})(\.[0-9]+)?$';
$CMIIdentifier = '^\\w{1,255}$';
$CMIFeedback   = $CMIString255; // This must be redefined
$CMIIndex      = '[._](\\d+)[.]';

// Vocabulary Data Type Definition
$CMIStatus  = '^passed$|^completed$|^failed$|^incomplete$|^browsed$|^not attempted$';
$CMIExit    = '^time-out$|^suspend$|^logout$|^$';
$CMIType    = '^true-false$|^choice$|^fill-in$|^matching$|^performance$|^sequencing$|^likert$|^numeric$';
$CMIResult  = '^correct$|^wrong$|^unanticipated$|^neutral$|^([0-9]{0,3})?(\.[0-9]{1,2})?$';
$NAVEvent   = '^previous$|^continue$';

// Children lists
$cmi_children                = 'core, suspend_data, launch_data, comments, objectives, student_data, student_preference, interactions';
$core_children               = 'student_id, student_name, lesson_location, credit, lesson_status, entry, score, total_time, lesson_mode, exit, session_time';
$score_children              = 'raw, min, max';
$objectives_children         = 'id, score, status';
$student_data_children       = 'mastery_score, max_time_allowed, time_limit_action';
$student_preference_children = 'audio, language, speed, text';
$interactions_children       = 'id, objectives, time, type, correct_responses, weighting, student_response, result, latency';

// Data ranges
$score_range     = array(0, 100);
$audio_range     = array(-1, 100);
$speed_range     = array(-100, 100);
$weighting_range = array(-100, 100);
$text_range      = array(-1, 1);
?>

<?php $this->inlineScript()->captureStart()?>
window.elsScormRequestUrl  = <?php echo HM_Json::encodeErrorSkip($this->requestUrl); ?>;
window.elsScormDebug       = <?php echo HM_Json::encodeErrorSkip($this->debug); ?>;
window.CMIIndex            = <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>;
window.score_children      = <?php echo HM_Json::encodeErrorSkip($score_children); ?>;
window.objectives_children = <?php echo HM_Json::encodeErrorSkip($objectives_children); ?>;
window.elsScormDatamodel  =  {
    'cmi._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($cmi_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi._version': {
        defaultvalue: '3.4',
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.core._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($core_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.core.student_id': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval($utd->student_id)); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.core.student_name': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval($utd->student_name)); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.core.lesson_location': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.core.lesson_location'}) ? $utd->{'cmi.core.lesson_location'} : '' )); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIString255); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.core.credit': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->credit )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.core.lesson_status': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.core.lesson_status'}) ? $utd->{'cmi.core.lesson_status'} : '' )); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIStatus); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.core.entry': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->entry )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.core.score._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($score_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.core.score.raw': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.core.score.raw'}) ? $utd->{'cmi.core.score.raw'} : '' )); ?>,
        format:       [<?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>, <?php echo HM_Json::encodeErrorSkip($CMIBlank); ?>],
        range:        <?php echo HM_Json::encodeErrorSkip($score_range); ?>,
        rangeF:       function (value) { return /^$/.test(value); },
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.core.score.max': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.core.score.max'}) ? $utd->{'cmi.core.score.max'} : '' )); ?>,
        format:       [<?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>, <?php echo HM_Json::encodeErrorSkip($CMIBlank); ?>],
        range:        <?php echo HM_Json::encodeErrorSkip($score_range); ?>,
        rangeF:       function (value) { return /^$/.test(value); },
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.core.score.min': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.core.score.min'}) ? $utd->{'cmi.core.score.min'} : '' )); ?>,
        format:       [<?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>, <?php echo HM_Json::encodeErrorSkip($CMIBlank); ?>],
        range:        <?php echo HM_Json::encodeErrorSkip($score_range); ?>,
        rangeF:       function (value) { return /^$/.test(value); },
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.core.total_time': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.core.total_time'}) ? $utd->{'cmi.core.total_time'} : '00:00:00' )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.core.lesson_mode': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->mode )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.core.exit': {
        defaultvalue: '',
        format:       <?php echo HM_Json::encodeErrorSkip($CMIExit); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.core.session_time': {
        format:       <?php echo HM_Json::encodeErrorSkip($CMITimespan); ?>,
        mod:          'w',
        defaultvalue: '00:00:00',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.suspend_data': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.suspend_data'}) ? $utd->{'cmi.suspend_data'} : '' )); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIString4096); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.launch_data': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->datafromlms )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.comments': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( isset($utd->{'cmi.comments'}) ? $utd->{'cmi.comments'} : '' )); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIString4096); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.comments_from_lms': {
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.objectives._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($objectives_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.objectives._count': {
        mod:          'r',
        defaultvalue: '0',
        writeerror:   '402'
    },
    'cmi.objectives.n.id': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIIdentifier); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.objectives.n.score._children': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.objectives.n.score.raw': {
        defaultvalue: '',
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       [<?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>, <?php echo HM_Json::encodeErrorSkip($CMIBlank); ?>],
        range:        <?php echo HM_Json::encodeErrorSkip($score_range); ?>,
        rangeF:       function (value) { return /^$/.test(value); },
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.objectives.n.score.min': {
        defaultvalue: '',
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       [<?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>, <?php echo HM_Json::encodeErrorSkip($CMIBlank); ?>],
        range:        <?php echo HM_Json::encodeErrorSkip($score_range); ?>,
        rangeF:       function (value) { return /^$/.test(value); },
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.objectives.n.score.max': {
        defaultvalue: '',
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       [<?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>, <?php echo HM_Json::encodeErrorSkip($CMIBlank); ?>],
        range:        <?php echo HM_Json::encodeErrorSkip($score_range); ?>,
        rangeF:       function (value) { return /^$/.test(value); },
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.objectives.n.status': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIStatus); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.student_data._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($student_data_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.student_data.mastery_score': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->masteryscore )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.student_data.max_time_allowed': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->maxtimeallowed )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.student_data.time_limit_action': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip(strval( $utd->timelimitaction )); ?>,
        mod:          'r',
        writeerror:   '403'
    },
    'cmi.student_preference._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($student_preference_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.student_preference.audio': {
        defaultvalue: '0',
        format:       <?php echo HM_Json::encodeErrorSkip($CMISInteger); ?>,
        range:        <?php echo HM_Json::encodeErrorSkip($audio_range); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.student_preference.language': {
        defaultvalue: '',
        format:       <?php echo HM_Json::encodeErrorSkip($CMIString255); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.student_preference.speed': {
        defaultvalue: '0',
        format:       <?php echo HM_Json::encodeErrorSkip($CMISInteger); ?>,
        range:        <?php echo HM_Json::encodeErrorSkip($speed_range); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.student_preference.text': {
        defaultvalue: '0',
        format:       <?php echo HM_Json::encodeErrorSkip($CMISInteger); ?>,
        range:        <?php echo HM_Json::encodeErrorSkip($text_range); ?>,
        mod:          'rw',
        writeerror:   '405'
    },
    'cmi.interactions._children': {
        defaultvalue: <?php echo HM_Json::encodeErrorSkip($interactions_children); ?>,
        mod:          'r',
        writeerror:   '402'
    },
    'cmi.interactions._count': {
        mod:          'r',
        defaultvalue: '0',
        writeerror:   '402'
    },
    'cmi.interactions.n.id': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIIdentifier); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.objectives._count': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        mod:          'r',
        defaultvalue: '0',
        writeerror:   '402'
    },
    'cmi.interactions.n.objectives.n.id': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIIdentifier); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.time': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMITime); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.type': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIType); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.correct_responses._count': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        mod:          'r',
        defaultvalue: '0',
        writeerror:   '402'
    },
    'cmi.interactions.n.correct_responses.n.pattern': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIFeedback); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.weighting': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIDecimal); ?>,
        range:        <?php echo HM_Json::encodeErrorSkip($weighting_range); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.student_response': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIFeedback); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.result': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMIResult); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'cmi.interactions.n.latency': {
        pattern:      <?php echo HM_Json::encodeErrorSkip($CMIIndex); ?>,
        format:       <?php echo HM_Json::encodeErrorSkip($CMITimespan); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    },
    'nav.event': {
        defaultvalue: '',
        format:       <?php echo HM_Json::encodeErrorSkip($NAVEvent); ?>,
        mod:          'w',
        readerror:    '404',
        writeerror:   '405'
    }
};

//var API = new SCORMapi1_2();
window.API = new SCORMapi1_2();

<?php echo $this->scormArray('scorm_12', $utd, 'cmi.objectives', array('score'))?>
<?php echo $this->scormArray('scorm_12', $utd, 'cmi.interactions', array('objectives', 'correct_responses'))?>

<?php $this->inlineScript()->captureEnd()?>