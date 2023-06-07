<?php $this->headScript()
    ->prependFile( $this->baseUrl('js/api/scorm_1_3.js') )
    ->prependFile( $this->baseUrl('js/api/request.js') ); ?>

<?php $this->inlineScript()->captureStart(); ?>
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.SCORM_log = function () {
	SCORM_log.history = SCORM_log.history || [];   // store logs to an array for reference
	SCORM_log.history.push(arguments);
	if (this.console) {
		console.log( Array.prototype.slice.call(arguments) );
	}
};

window.CMIString200 = '^[\\u0000-\\uFFFF]{0,200}$';
window.CMIString250 = '^[\\u0000-\\uFFFF]{0,250}$';
window.CMIString1000 = '^[\\u0000-\\uFFFF]{0,1000}$';
window.CMIString4000 = '^[\\u0000-\\uFFFF]{0,4000}$';
window.CMIString64000 = '^[\\u0000-\\uFFFF]{0,64000}$';
window.CMILang = '^([a-zA-Z]{2,3}|i|x)(\-[a-zA-Z0-9\-]{2,8})?$|^$';
window.CMILangString250 = '^(\{lang=([a-zA-Z]{2,3}|i|x)(\-[a-zA-Z0-9\-]{2,8})?\})?([^\{].{0,250}$)?';
window.CMILangcr = '^((\{lang=([a-zA-Z]{2,3}|i|x)?(\-[a-zA-Z0-9\-]{2,8})?\}))(.*?)$';
window.CMILangString250cr = '^((\{lang=([a-zA-Z]{2,3}|i|x)?(\-[a-zA-Z0-9\-]{2,8})?\})?(.{0,250})?)?$';
window.CMILangString4000 = '^(\{lang=([a-zA-Z]{2,3}|i|x)(\-[a-zA-Z0-9\-]{2,8})?\})?([^\{].{0,4000}$)?';
window.CMITime = '^(19[7-9]{1}[0-9]{1}|20[0-2]{1}[0-9]{1}|203[0-8]{1})((-(0[1-9]{1}|1[0-2]{1}))((-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1}))(T([0-1]{1}[0-9]{1}|2[0-3]{1})((:[0-5]{1}[0-9]{1})((:[0-5]{1}[0-9]{1})((\\.[0-9]{1,2})?((Z|([+|-]([0-1]{1}[0-9]{1}|2[0-3]{1})))(:[0-5]{1}[0-9]{1})?)?)?)?)?)?)?)?$';
window.CMITimespan = '^P(\\d+Y)?(\\d+M)?(\\d+D)?(T(\\d+H)?(\\d+M)?(\\d+(\\.\\d{1,2})?S)?)?$';
window.CMIInteger = '^\\d+$';
window.CMISInteger = '^-?([0-9]+)$';
window.CMIDecimal = '^-?([0-9]{1,5})(\\.[0-9]{1,18})?$';
window.CMIIdentifier = '^\\S{0,250}[a-zA-Z0-9]$';
window.CMIShortIdentifier = '^[\\w\.]{1,250}$';
window.CMILongIdentifier = '^\\S{0,4000}[a-zA-Z0-9]$';
window.CMIFeedback = '^.*$'; // This must be redefined
window.CMIIndex = '[._](\\d+).';
window.CMIIndexStore = '.N(\\d+).';
// Vocabulary Data Type Definition
window.CMICStatus = '^completed$|^incomplete$|^not attempted$|^unknown$';
window.CMISStatus = '^passed$|^failed$|^unknown$';
window.CMIExit = '^time-out$|^suspend$|^logout$|^normal$|^$';
window.CMIType = '^true-false$|^choice$|^(long-)?fill-in$|^matching$|^performance$|^sequencing$|^likert$|^numeric$|^other$';
window.CMIResult = '^correct$|^incorrect$|^unanticipated$|^neutral$|^-?([0-9]{1,4})(\\.[0-9]{1,18})?$';
window.NAVEvent = '^previous$|^continue$|^exit$|^exitAll$|^abandon$|^abandonAll$|^suspendAll$|^{target=\\S{0,200}[a-zA-Z0-9]}choice$';
window.NAVBoolean = '^unknown$|^true$|^false$';
window.NAVTarget = '^previous$|^continue$|^choice.{target=\\S{0,200}[a-zA-Z0-9]}$'
// Children lists
window.cmi_children = '_version, comments_from_learner, comments_from_lms, completion_status, credit, entry, exit, interactions, launch_data, learner_id, learner_name, learner_preference, location, max_time_allowed, mode, objectives, progress_measure, scaled_passing_score, score, session_time, success_status, suspend_data, time_limit_action, total_time';
window.comments_children = 'comment, timestamp, location';
window.score_children = 'max, raw, scaled, min';
window.objectives_children = 'progress_measure, completion_status, success_status, description, score, id';
window.correct_responses_children = 'pattern';
window.student_data_children = 'mastery_score, max_time_allowed, time_limit_action';
window.student_preference_children = 'audio_level, audio_captioning, delivery_speed, language';
window.interactions_children = 'id, type, objectives, timestamp, correct_responses, weighting, learner_response, result, latency, description';
// Data ranges
window.scaled_range = '-1#1';
window.audio_range = '0#*';
window.speed_range = '0#*';
window.text_range = '-1#1';
window.progress_range = '0#1';
window.learner_response = {
    'true-false':{'format':'^true$|^false$', 'max':1, 'delimiter':'', 'unique':false},
    'choice':{'format':CMIShortIdentifier, 'max':36, 'delimiter':'[,]', 'unique':true},
    'fill-in':{'format':CMILangString250, 'max':10, 'delimiter':'[,]', 'unique':false},
    'long-fill-in':{'format':CMILangString4000, 'max':1, 'delimiter':'', 'unique':false},
    'matching':{'format':CMIShortIdentifier, 'format2':CMIShortIdentifier, 'max':36, 'delimiter':'[,]', 'delimiter2':'[.]', 'unique':false},
    'performance':{'format':'^$|'+CMIShortIdentifier, 'format2':CMIDecimal+'|^$|'+CMIShortIdentifier, 'max':250, 'delimiter':'[,]', 'delimiter2':'[.]', 'unique':false},
    'sequencing':{'format':CMIShortIdentifier, 'max':36, 'delimiter':'[,]', 'unique':false},
    'likert':{'format':CMIShortIdentifier, 'max':1, 'delimiter':'', 'unique':false},
    'numeric':{'format':CMIDecimal, 'max':1, 'delimiter':'', 'unique':false},
    'other':{'format':CMIString4000, 'max':1, 'delimiter':'', 'unique':false}
}

window.correct_responses = {
    'true-false':{'pre':'', 'max':1, 'delimiter':'', 'unique':false, 'duplicate':false,
                  'format':'^true$|^false$',
                  'limit':1},
    'choice':{'pre':'', 'max':36, 'delimiter':'[,]', 'unique':true, 'duplicate':false,
              'format':CMIShortIdentifier},
//        'fill-in':{'pre':'^(((\{case_matters=(true|false)\})(\{order_matters=(true|false)\})?)|((\{order_matters=(true|false)\})(\{case_matters=(true|false)\})?))(.*?)$',
    'fill-in':{'pre':'',
               'max':10, 'delimiter':'[,]', 'unique':false, 'duplicate':false,
               'format':CMILangString250cr},
    'long-fill-in':{'pre':'^(\{case_matters=(true|false)\})?', 'max':1, 'delimiter':'', 'unique':false, 'duplicate':true,
                    'format':CMILangString4000},
    'matching':{'pre':'', 'max':36, 'delimiter':'[,]', 'delimiter2':'[.]', 'unique':false, 'duplicate':false,
                'format':CMIShortIdentifier, 'format2':CMIShortIdentifier},
    'performance':{'pre':'^(\{order_matters=(true|false)\})?',
                   'max':250, 'delimiter':'[,]', 'delimiter2':'[.]', 'unique':false, 'duplicate':false,
                   'format':'^$|'+CMIShortIdentifier, 'format2':CMIDecimal+'|^$|'+CMIShortIdentifier},
    'sequencing':{'pre':'', 'max':36, 'delimiter':'[,]', 'unique':false, 'duplicate':false,
                  'format':CMIShortIdentifier},
    'likert':{'pre':'', 'max':1, 'delimiter':'', 'unique':false, 'duplicate':false,
              'format':CMIShortIdentifier,
              'limit':1},
    'numeric':{'pre':'', 'max':2, 'delimiter':'[:]', 'unique':false, 'duplicate':false,
               'format':CMIDecimal,
               'limit':1},
    'other':{'pre':'', 'max':1, 'delimiter':'', 'unique':false, 'duplicate':false,
             'format':CMIString4000,
             'limit':1}
}


// The SCORM 1.3 data model
window.elsScormDatamodel =  {
    'cmi._children':{'defaultvalue':cmi_children, 'mod':'r'},
    'cmi._version':{'defaultvalue':'1.0', 'mod':'r'},
    'cmi.comments_from_learner._children':{'defaultvalue':comments_children, 'mod':'r'},
    'cmi.comments_from_learner._count':{'mod':'r', 'defaultvalue':'0'},
    'cmi.comments_from_learner.n.comment':{'format':CMILangString4000, 'mod':'rw'},
    'cmi.comments_from_learner.n.location':{'format':CMIString250, 'mod':'rw'},
    'cmi.comments_from_learner.n.timestamp':{'format':CMITime, 'mod':'rw'},
    'cmi.comments_from_lms._children':{'defaultvalue':comments_children, 'mod':'r'},
    'cmi.comments_from_lms._count':{'mod':'r', 'defaultvalue':'0'},
    'cmi.comments_from_lms.n.comment':{'format':CMILangString4000, 'mod':'r'},
    'cmi.comments_from_lms.n.location':{'format':CMIString250, 'mod':'r'},
    'cmi.comments_from_lms.n.timestamp':{'format':CMITime, 'mod':'r'},
    'cmi.completion_status':{'defaultvalue':'<?php echo isset($this->userTrackData->{'cmi.completion_status'})?$this->userTrackData->{'cmi.completion_status'}:'unknown' ?>', 'format':CMICStatus, 'mod':'rw'},
    'cmi.completion_threshold':{'defaultvalue':<?php echo isset($this->userTrackData->threshold) ? HM_Json::encodeErrorSkip($this->userTrackData->threshold) : 'null' ?>, 'mod':'r'},
    'cmi.credit':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip(isset($this->userTrackData->credit) ? $this->userTrackData->credit : '') ?>, 'mod':'r'},
    'cmi.entry':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip($this->userTrackData->entry) ?>, 'mod':'r'},
    'cmi.exit':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip(isset($this->userTrackData->{'cmi.exit'}) ? $this->userTrackData->{'cmi.exit'} : '') ?>, 'format':CMIExit, 'mod':'w'},
    'cmi.interactions._children':{'defaultvalue':interactions_children, 'mod':'r'},
    'cmi.interactions._count':{'mod':'r', 'defaultvalue':'0'},
    'cmi.interactions.n.id':{'pattern':CMIIndex, 'format':CMILongIdentifier, 'mod':'rw'},
    'cmi.interactions.n.type':{'pattern':CMIIndex, 'format':CMIType, 'mod':'rw'},
    'cmi.interactions.n.objectives._count':{'pattern':CMIIndex, 'mod':'r', 'defaultvalue':'0'},
    'cmi.interactions.n.objectives.n.id':{'pattern':CMIIndex, 'format':CMILongIdentifier, 'mod':'rw'},
    'cmi.interactions.n.timestamp':{'pattern':CMIIndex, 'format':CMITime, 'mod':'rw'},
    'cmi.interactions.n.correct_responses._count':{'defaultvalue':'0', 'pattern':CMIIndex, 'mod':'r'},
    'cmi.interactions.n.correct_responses.n.pattern':{'pattern':CMIIndex, 'format':'CMIFeedback', 'mod':'rw'},
    'cmi.interactions.n.weighting':{'pattern':CMIIndex, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.interactions.n.learner_response':{'pattern':CMIIndex, 'format':'CMIFeedback', 'mod':'rw'},
    'cmi.interactions.n.result':{'pattern':CMIIndex, 'format':CMIResult, 'mod':'rw'},
    'cmi.interactions.n.latency':{'pattern':CMIIndex, 'format':CMITimespan, 'mod':'rw'},
    'cmi.interactions.n.description':{'pattern':CMIIndex, 'format':CMILangString250, 'mod':'rw'},
    'cmi.launch_data':{'defaultvalue':<?php echo isset($this->userTrackData->datafromlms) ? HM_Json::encodeErrorSkip($this->userTrackData->datafromlms) : 'null' ?>, 'mod':'r'},
    'cmi.learner_id':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip($this->userTrackData->student_id) ?>, 'mod':'r'},
    'cmi.learner_name':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip($this->userTrackData->student_name) ?>, 'mod':'r'},
    'cmi.learner_preference._children':{'defaultvalue':student_preference_children, 'mod':'r'},
    'cmi.learner_preference.audio_level':{'defaultvalue':'1', 'format':CMIDecimal, 'range':audio_range, 'mod':'rw'},
    'cmi.learner_preference.language':{'defaultvalue':'', 'format':CMILang, 'mod':'rw'},
    'cmi.learner_preference.delivery_speed':{'defaultvalue':'1', 'format':CMIDecimal, 'range':speed_range, 'mod':'rw'},
    'cmi.learner_preference.audio_captioning':{'defaultvalue':'0', 'format':CMISInteger, 'range':text_range, 'mod':'rw'},
    'cmi.location':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.location'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.location'}) : 'null' ?>, 'format':CMIString1000, 'mod':'rw'},
    'cmi.max_time_allowed':{'defaultvalue':<?php echo isset($this->userTrackData->maxtimeallowed) ? HM_Json::encodeErrorSkip($this->userTrackData->maxtimeallowed) :'null' ?>, 'mod':'r'},
    'cmi.mode':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip($this->userTrackData->mode) ?>, 'mod':'r'},
    'cmi.objectives._children':{'defaultvalue':objectives_children, 'mod':'r'},
    'cmi.objectives._count':{'mod':'r', 'defaultvalue':'0'},
    'cmi.objectives.n.id':{'pattern':CMIIndex, 'format':CMILongIdentifier, 'mod':'rw'},
    'cmi.objectives.n.score._children':{'defaultvalue':score_children, 'pattern':CMIIndex, 'mod':'r'},
    'cmi.objectives.n.score.scaled':{'defaultvalue':null, 'pattern':CMIIndex, 'format':CMIDecimal, 'range':scaled_range, 'mod':'rw'},
    'cmi.objectives.n.score.raw':{'defaultvalue':null, 'pattern':CMIIndex, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.objectives.n.score.min':{'defaultvalue':null, 'pattern':CMIIndex, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.objectives.n.score.max':{'defaultvalue':null, 'pattern':CMIIndex, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.objectives.n.success_status':{'defaultvalue':'unknown', 'pattern':CMIIndex, 'format':CMISStatus, 'mod':'rw'},
    'cmi.objectives.n.completion_status':{'defaultvalue':'unknown', 'pattern':CMIIndex, 'format':CMICStatus, 'mod':'rw'},
    'cmi.objectives.n.progress_measure':{'defaultvalue':null, 'format':CMIDecimal, 'range':progress_range, 'mod':'rw'},
    'cmi.objectives.n.description':{'pattern':CMIIndex, 'format':CMILangString250, 'mod':'rw'},
    'cmi.progress_measure':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.progess_measure'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.progress_measure'}) : 'null' ?>, 'format':CMIDecimal, 'range':progress_range, 'mod':'rw'},
    'cmi.scaled_passing_score':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.scaled_passing_score'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.scaled_passing_score'}) : 'null' ?>, 'format':CMIDecimal, 'range':scaled_range, 'mod':'r'},
    'cmi.score._children':{'defaultvalue':score_children, 'mod':'r'},
    'cmi.score.scaled':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.score.scaled'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.score.scaled'}) : 'null' ?>, 'format':CMIDecimal, 'range':scaled_range, 'mod':'rw'},
    'cmi.score.raw':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.score.raw'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.score.raw'}) : 'null' ?>, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.score.min':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.score.min'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.score.min'}) : 'null' ?>, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.score.max':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.score.max'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.score.max'}) : 'null' ?>, 'format':CMIDecimal, 'mod':'rw'},
    'cmi.session_time':{'format':CMITimespan, 'mod':'w', 'defaultvalue':'PT0H0M0S'},
    'cmi.success_status':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip(isset($this->userTrackData->{'cmi.success_status'}) ? $this->userTrackData->{'cmi.success_status'} : 'unknown') ?>, 'format':CMISStatus, 'mod':'rw'},
    'cmi.suspend_data':{'defaultvalue':<?php echo isset($this->userTrackData->{'cmi.suspend_data'}) ? HM_Json::encodeErrorSkip($this->userTrackData->{'cmi.suspend_data'}) : 'null' ?>, 'format':CMIString64000, 'mod':'rw'},
    'cmi.time_limit_action':{'defaultvalue':<?php echo isset($this->userTrackData->timelimitaction) ? HM_Json::encodeErrorSkip($this->userTrackData->timelimitaction) : 'null' ?>, 'mod':'r'},
    'cmi.total_time':{'defaultvalue':<?php echo HM_Json::encodeErrorSkip(isset($this->userTrackData->{'cmi.total_time'}) ? $this->userTrackData->{'cmi.total_time'} : 'PT0H0M0S') ?>, 'mod':'r'},
    'adl.nav.request':{'defaultvalue':'_none_', 'format':NAVEvent, 'mod':'rw'}
};
window.elsScormRequestUrl = "<?php echo $this->requestUrl?>";
window.elsScormDebug = Boolean('<?php echo $this->debug?>');
window.API_1484_11 = new SCORMapi1_3();


<?php echo $this->scormArray('scorm_13', $this->userTrackData, 'cmi.objectives', array('score'))?>
<?php echo $this->scormArray('scorm_13', $this->userTrackData, 'cmi.interactions', array('objectives', 'correct_responses'))?>
<?php echo $this->scormArray('scorm_13', $this->userTrackData, 'cmi.comments_from_learner', array())?>
<?php echo $this->scormArray('scorm_13', $this->userTrackData, 'cmi.comments_from_lms', array())?>


<?php $this->inlineScript()->captureEnd(); ?>



