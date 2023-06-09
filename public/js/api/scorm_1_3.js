
function LogAPICall(func, nam, val, rc) {
    // drop call to GetLastError for the time being - it produces too much chatter
    if (func.match(/GetLastError/)) {
        return;
    }
    var s = func + '("' + nam + '"';
    if (val != null && ! (func.match(/GetValue|GetLastError/))) {
        s += ', "' + val + '"';
    }
    s += ')';
    if (func.match(/GetValue/)) {
        s += ' - ' + val;
    }
    s += ' => ' + String(rc);
    AppendToLog(s, rc);
}

//add an individual log entry
function AppendToLog(s, rc) {
    var sStyle;
    if (rc != 0) {
        sStyle = "error";
    }
    var now = new Date();
    now.setTime( now.getTime() );
    s = now.toGMTString() + ': ' + s;
    
	try {
		if (rc != 0) {
			console.debug('ERROR:', s);
		} else {
			console.debug(s);
		}
	} catch (error) {
	}
}

function underscore(str) {
    return str.replace(/\./g,"__");
}

//
// SCORM 1.3 API Implementation
//
function SCORMapi1_3() {
    // Standard Data Type Definition

    // language key has to be checked for language dependent strings
    var validLanguages = {'aa':'aa', 'ab':'ab', 'ae':'ae', 'af':'af', 'ak':'ak', 'am':'am', 'an':'an', 'ar':'ar', 'as':'as', 'av':'av', 'ay':'ay', 'az':'az',
                          'ba':'ba', 'be':'be', 'bg':'bg', 'bh':'bh', 'bi':'bi', 'bm':'bm', 'bn':'bn', 'bo':'bo', 'br':'br', 'bs':'bs',
                          'ca':'ca', 'ce':'ce', 'ch':'ch', 'co':'co', 'cr':'cr', 'cs':'cs', 'cu':'cu', 'cv':'cv', 'cy':'cy',
                          'da':'da', 'de':'de', 'dv':'dv', 'dz':'dz', 'ee':'ee', 'el':'el', 'en':'en', 'eo':'eo', 'es':'es', 'et':'et', 'eu':'eu',
                          'fa':'fa', 'ff':'ff', 'fi':'fi', 'fj':'fj', 'fo':'fo', 'fr':'fr', 'fy':'fy', 'ga':'ga', 'gd':'gd', 'gl':'gl', 'gn':'gn', 'gu':'gu', 'gv':'gv',
                          'ha':'ha', 'he':'he', 'hi':'hi', 'ho':'ho', 'hr':'hr', 'ht':'ht', 'hu':'hu', 'hy':'hy', 'hz':'hz',
                          'ia':'ia', 'id':'id', 'ie':'ie', 'ig':'ig', 'ii':'ii', 'ik':'ik', 'io':'io', 'is':'is', 'it':'it', 'iu':'iu',
                          'ja':'ja', 'jv':'jv', 'ka':'ka', 'kg':'kg', 'ki':'ki', 'kj':'kj', 'kk':'kk', 'kl':'kl', 'km':'km', 'kn':'kn', 'ko':'ko', 'kr':'kr', 'ks':'ks', 'ku':'ku', 'kv':'kv', 'kw':'kw', 'ky':'ky',
                          'la':'la', 'lb':'lb', 'lg':'lg', 'li':'li', 'ln':'ln', 'lo':'lo', 'lt':'lt', 'lu':'lu', 'lv':'lv',
                          'mg':'mg', 'mh':'mh', 'mi':'mi', 'mk':'mk', 'ml':'ml', 'mn':'mn', 'mo':'mo', 'mr':'mr', 'ms':'ms', 'mt':'mt', 'my':'my',
                          'na':'na', 'nb':'nb', 'nd':'nd', 'ne':'ne', 'ng':'ng', 'nl':'nl', 'nn':'nn', 'no':'no', 'nr':'nr', 'nv':'nv', 'ny':'ny',
                          'oc':'oc', 'oj':'oj', 'om':'om', 'or':'or', 'os':'os', 'pa':'pa', 'pi':'pi', 'pl':'pl', 'ps':'ps', 'pt':'pt',
                          'qu':'qu', 'rm':'rm', 'rn':'rn', 'ro':'ro', 'ru':'ru', 'rw':'rw',
                          'sa':'sa', 'sc':'sc', 'sd':'sd', 'se':'se', 'sg':'sg', 'sh':'sh', 'si':'si', 'sk':'sk', 'sl':'sl', 'sm':'sm', 'sn':'sn', 'so':'so', 'sq':'sq', 'sr':'sr', 'ss':'ss', 'st':'st', 'su':'su', 'sv':'sv', 'sw':'sw',
                          'ta':'ta', 'te':'te', 'tg':'tg', 'th':'th', 'ti':'ti', 'tk':'tk', 'tl':'tl', 'tn':'tn', 'to':'to', 'tr':'tr', 'ts':'ts', 'tt':'tt', 'tw':'tw', 'ty':'ty',
                          'ug':'ug', 'uk':'uk', 'ur':'ur', 'uz':'uz', 've':'ve', 'vi':'vi', 'vo':'vo',
                          'wa':'wa', 'wo':'wo', 'xh':'xh', 'yi':'yi', 'yo':'yo', 'za':'za', 'zh':'zh', 'zu':'zu',
                          'aar':'aar', 'abk':'abk', 'ave':'ave', 'afr':'afr', 'aka':'aka', 'amh':'amh', 'arg':'arg', 'ara':'ara', 'asm':'asm', 'ava':'ava', 'aym':'aym', 'aze':'aze',
                          'bak':'bak', 'bel':'bel', 'bul':'bul', 'bih':'bih', 'bis':'bis', 'bam':'bam', 'ben':'ben', 'tib':'tib', 'bod':'bod', 'bre':'bre', 'bos':'bos',
                          'cat':'cat', 'che':'che', 'cha':'cha', 'cos':'cos', 'cre':'cre', 'cze':'cze', 'ces':'ces', 'chu':'chu', 'chv':'chv', 'wel':'wel', 'cym':'cym',
                          'dan':'dan', 'ger':'ger', 'deu':'deu', 'div':'div', 'dzo':'dzo', 'ewe':'ewe', 'gre':'gre', 'ell':'ell', 'eng':'eng', 'epo':'epo', 'spa':'spa', 'est':'est', 'baq':'baq', 'eus':'eus', 'per':'per',
                          'fas':'fas', 'ful':'ful', 'fin':'fin', 'fij':'fij', 'fao':'fao', 'fre':'fre', 'fra':'fra', 'fry':'fry', 'gle':'gle', 'gla':'gla', 'glg':'glg', 'grn':'grn', 'guj':'guj', 'glv':'glv',
                          'hau':'hau', 'heb':'heb', 'hin':'hin', 'hmo':'hmo', 'hrv':'hrv', 'hat':'hat', 'hun':'hun', 'arm':'arm', 'hye':'hye', 'her':'her',
                          'ina':'ina', 'ind':'ind', 'ile':'ile', 'ibo':'ibo', 'iii':'iii', 'ipk':'ipk', 'ido':'ido', 'ice':'ice', 'isl':'isl', 'ita':'ita', 'iku':'iku',
                          'jpn':'jpn', 'jav':'jav', 'geo':'geo', 'kat':'kat', 'kon':'kon', 'kik':'kik', 'kua':'kua', 'kaz':'kaz', 'kal':'kal', 'khm':'khm', 'kan':'kan', 'kor':'kor', 'kau':'kau', 'kas':'kas', 'kur':'kur', 'kom':'kom', 'cor':'cor', 'kir':'kir',
                          'lat':'lat', 'ltz':'ltz', 'lug':'lug', 'lim':'lim', 'lin':'lin', 'lao':'lao', 'lit':'lit', 'lub':'lub', 'lav':'lav',
                          'mlg':'mlg', 'mah':'mah', 'mao':'mao', 'mri':'mri', 'mac':'mac', 'mkd':'mkd', 'mal':'mal', 'mon':'mon', 'mol':'mol', 'mar':'mar', 'may':'may', 'msa':'msa', 'mlt':'mlt', 'bur':'bur', 'mya':'mya',
                          'nau':'nau', 'nob':'nob', 'nde':'nde', 'nep':'nep', 'ndo':'ndo', 'dut':'dut', 'nld':'nld', 'nno':'nno', 'nor':'nor', 'nbl':'nbl', 'nav':'nav', 'nya':'nya',
                          'oci':'oci', 'oji':'oji', 'orm':'orm', 'ori':'ori', 'oss':'oss', 'pan':'pan', 'pli':'pli', 'pol':'pol', 'pus':'pus', 'por':'por', 'que':'que',
                          'roh':'roh', 'run':'run', 'rum':'rum', 'ron':'ron', 'rus':'rus', 'kin':'kin', 'san':'san', 'srd':'srd', 'snd':'snd', 'sme':'sme', 'sag':'sag', 'slo':'slo', 'sin':'sin', 'slk':'slk', 'slv':'slv', 'smo':'smo', 'sna':'sna', 'som':'som', 'alb':'alb', 'sqi':'sqi', 'srp':'srp', 'ssw':'ssw', 'sot':'sot', 'sun':'sun', 'swe':'swe', 'swa':'swa',
                          'tam':'tam', 'tel':'tel', 'tgk':'tgk', 'tha':'tha', 'tir':'tir', 'tuk':'tuk', 'tgl':'tgl', 'tsn':'tsn', 'ton':'ton', 'tur':'tur', 'tso':'tso', 'tat':'tat', 'twi':'twi', 'tah':'tah',
                          'uig':'uig', 'ukr':'ukr', 'urd':'urd', 'uzb':'uzb', 'ven':'ven', 'vie':'vie', 'vol':'vol', 'wln':'wln', 'wol':'wol', 'xho':'xho', 'yid':'yid', 'yor':'yor', 'zha':'zha', 'chi':'chi', 'zho':'zho', 'zul':'zul'};


    // The SCORM 1.3 data model    
    var datamodel =  elsScormDatamodel;
    
    //
    // Datamodel inizialization
    //
    if (typeof cmi == 'undefined') {
        cmi = {};
    }

    if (typeof comments_from_learner == 'undefined') {
        cmi.comments_from_learner = {};
        cmi.comments_from_learner._count = 0;
    }
    
    if (typeof comments_from_lms == 'undefined') {
        cmi.comments_from_lms = {};
        cmi.comments_from_lms._count = 0;
    }
        
    if (typeof cmi.objective == 'undefined') {
        cmi.objectives = {};
        cmi.objectives._count = 0;
    }
    
    if (typeof cmi.interactions == 'undefined') {
        cmi.interactions = {};
        cmi.interactions._count = 0;
    }  
    
    if (typeof cmi.learner_preference == 'undefined') {
        cmi.learner_preference = {};        
    }
    
    if (typeof cmi.score == 'undefined') {
        cmi.score = {};
    }

    // Navigation Object
    var adl = {};
        adl.nav = {};
        adl.nav.request_valid = [];

    for (element in datamodel) {
        if (element.match(/\.n\./) == null) {
            if ((typeof eval('datamodel["'+element+'"].defaultvalue')) != 'undefined') {
                eval(element+' = datamodel["'+element+'"].defaultvalue;');
            } else {
                eval(element+' = "";');
            }
        }
    }

    if (cmi.completion_status == '') {
        cmi.completion_status = 'not attempted';
    }

    //
    // API Methods definition
    //
    var Initialized = false;
    var Terminated = false;
    var diagnostic = "";

    function Initialize (param) {
        errorCode = "0";
        if (param == "") {
            if ((!Initialized) /*&& (!Terminated)*/) {//Бывает так, что родительское окно не перезагружается и тогда повторный запуск курса пашет без скорма!
                Initialized = true;
                Terminated = false;

                errorCode = "0";
                if (elsScormDebug) {
                    SCORM_log("Initialize", param, '', errorCode);
                }
                return "true";
            } else {
                if (Initialized) {
                    errorCode = "103";
                } else {
                    errorCode = "104";
                }
            }
        } else {
            errorCode = "201";
        }
        if (elsScormDebug) {
            SCORM_log("Initialize", param, '', errorCode);
        }
        return "false";
    }

    function Terminate (param) {
        errorCode = "0";
        if (param == "") {
            if ((Initialized) && (!Terminated)) {
                Initialized = false;
                Terminated = true;
                var result = StoreData(cmi,true);
                if (adl.nav.request != '_none_') {
                    switch (adl.nav.request) {
                        case 'continue':
                            //setTimeout('top.nextSCO();',500);
                        break;
                        case 'previous':
                            //setTimeout('top.prevSCO();',500);
                        break;
                        case 'choice':
                        break;
                        case 'exit':
                        break;
                        case 'exitAll':
                        break;
                        case 'abandon':
                        break;
                        case 'abandonAll':
                        break;
                    }
                } else {
/*
                    if (<?php echo $scorm->auto ?> == 1) {
                        setTimeout('top.nextSCO();',500);
                    }
*/
                }
                if (elsScormDebug) {
                    SCORM_log("Terminate", param, '', 0);
                }
                return "true";
            } else {
                if (Terminated) {
                    errorCode = "113";
                } else {
                    errorCode = "112";
                }
            }
        } else {
            errorCode = "201";
        }

        if (elsScormDebug) {
            SCORM_log("Terminate", param, '', errorCode);
        }

        return "false";
    }

    function GetValue (element) {
        errorCode = "0";
        diagnostic = "";
        if ((Initialized) && (!Terminated)) {
            if (element !="") {
                var expression = new RegExp(CMIIndex,'g');
                var elementmodel = String(element).replace(expression,'.n.');
                if ((typeof eval('datamodel["'+elementmodel+'"]')) != "undefined") {
                    if (eval('datamodel["'+elementmodel+'"].mod') != 'w') {

                        element = String(element).replace(/\.(\d+)\./, ".N$1.");
                        element = element.replace(/\.(\d+)\./, ".N$1.");

                        var elementIndexes = element.split('.');
                        var subelement = element.substr(0,3);
                        var i = 1;
                        while ((i < elementIndexes.length) && (typeof eval(subelement) != "undefined")) {
                            subelement += '.'+elementIndexes[i++];
                        }

                        if (subelement == element) {

                            if ((typeof eval(subelement) != "undefined") && (eval(subelement) != null)) {
                                errorCode = "0";

                                if (elsScormDebug) {
                                    SCORM_log("GetValue", element, eval(element), 0);
                                }
                                
                                return eval(element);
                            } else {
                                errorCode = "403";
                            }
                        } else {
                            errorCode = "301";
                        }
                    } else {
                        //errorCode = eval('datamodel["'+elementmodel+'"].readerror');
                        errorCode = "405";
                    }
                } else {
                    var childrenstr = '._children';
                    var countstr = '._count';
                    var parentmodel = '';
                    if (elementmodel.substr(elementmodel.length-childrenstr.length,elementmodel.length) == childrenstr) {
                        parentmodel = elementmodel.substr(0,elementmodel.length-childrenstr.length);
                        if ((typeof eval('datamodel["'+parentmodel+'"]')) != "undefined") {
                            errorCode = "301";
                            diagnostic = "Data Model Element Does Not Have Children";
                        } else {
                            errorCode = "401";
                        }
                    } else if (elementmodel.substr(elementmodel.length-countstr.length,elementmodel.length) == countstr) {
                        parentmodel = elementmodel.substr(0,elementmodel.length-countstr.length);
                        if ((typeof eval('datamodel["'+parentmodel+'"]')) != "undefined") {
                            errorCode = "301";
                            diagnostic = "Data Model Element Cannot Have Count";
                        } else {
                            errorCode = "401";
                        }
                    } else {
                        parentmodel = 'adl.nav.request_valid.';
                        if (element && (element.substr(0,parentmodel.length) == parentmodel)) {
                            if (element.substr(parentmodel.length).match(NAVTarget) == null) {
                                errorCode = "301";
                            } else {
                                if (adl.nav.request == element.substr(parentmodel.length)) {
                                    return "true";
                                } else if (adl.nav.request == '_none_') {
                                    return "unknown";
                                } else {
                                    return "false";
                                }
                            }
                        } else {
                            errorCode = "401";
                        }
                    }
                }
            } else {
                errorCode = "301";
            }
        } else {
            if (Terminated) {
                errorCode = "123";
            } else {
                errorCode = "122";
            }
        }

        if (elsScormDebug) {
            SCORM_log("GetValue", element, '', errorCode);
        }

        return "";
    }

    function SetValue (element,value) {
        errorCode = "0";
        diagnostic = "";
        if ((Initialized) && (!Terminated)) {
            if ((element != "") && (typeof element != 'undefined')) {
                var expression = new RegExp(CMIIndex,'g');
                var elementmodel = String(element).replace(expression,'.n.');
                if ((typeof eval('datamodel["'+elementmodel+'"]')) != "undefined") {
                    if (eval('datamodel["'+elementmodel+'"].mod') != 'r') {
                        if (eval('datamodel["'+elementmodel+'"].format') != 'CMIFeedback') {
                            expression = new RegExp(eval('datamodel["'+elementmodel+'"].format'));
                        } else {
                            // cmi.interactions.n.type depending format accept everything at this stage
                            expression = new RegExp(CMIFeedback);
                        }
                        value = value+'';
                        var matches = value.match(expression);
                        if ((matches != null) && ((matches.join('').length > 0) || (value.length == 0))) {
                            // Value match dataelement format

                            if (element != elementmodel) {
                                //This is a dynamic datamodel element

                                var elementIndexes = element.split('.');
                                var subelement = 'cmi';
                                var parentelement = 'cmi';
                                for (var i=1;(i < elementIndexes.length-1) && (errorCode=="0");i++) {
                                    var elementIndex = elementIndexes[i];
                                    if (elementIndexes[i+1].match(/^\d+$/)) {
                                        if ((parseInt(elementIndexes[i+1]) > 0) && (elementIndexes[i+1].charAt(0) == 0)) {
                                            // Index has a leading 0 (zero), this is not a number
                                            errorCode = "351";
                                        }
                                        parentelement = subelement+'.'+elementIndex;
                                        if ((typeof eval(parentelement) == "undefined") || (typeof eval(parentelement+'._count') == "undefined")) {
                                            errorCode="408";
                                        } else {
                                            if (elementIndexes[i+1] > eval(parentelement+'._count')) {
                                                errorCode = "351";
                                                diagnostic = "Data Model Element Collection Set Out Of Order";
                                            }
                                            subelement = subelement.concat('.'+elementIndex+'.N'+elementIndexes[i+1]);
                                            i++;

                                            if (((typeof eval(subelement)) == "undefined") && (i < elementIndexes.length-2)) {
                                                errorCode="408";
                                            }
                                        }
                                    } else {
                                        subelement = subelement.concat('.'+elementIndex);
                                    }
                                }

                                if (errorCode == "0") {
                                    // Till now it's a real datamodel element

                                    element = subelement.concat('.'+elementIndexes[elementIndexes.length-1]);

                                    if ((typeof eval(subelement)) == "undefined") {
                                        switch (elementmodel) {
                                            case 'cmi.objectives.n.id':
                                                if (!duplicatedID(element,parentelement,value)) {
                                                    if (elementIndexes[elementIndexes.length-2] == eval(parentelement+'._count')) {
                                                        eval(parentelement+'._count++;');
                                                        eval(subelement+' = new Object();');
                                                        var subobject = eval(subelement);
                                                        subobject.success_status = datamodel["cmi.objectives.n.success_status"].defaultvalue;
                                                        subobject.completion_status = datamodel["cmi.objectives.n.completion_status"].defaultvalue;
                                                        subobject.progress_measure = datamodel["cmi.objectives.n.progress_measure"].defaultvalue;
                                                        subobject.score = {};
                                                        subobject.score._children = score_children;
                                                        subobject.score.scaled = datamodel["cmi.objectives.n.score.scaled"].defaultvalue;
                                                        subobject.score.raw = datamodel["cmi.objectives.n.score.raw"].defaultvalue;
                                                        subobject.score.min = datamodel["cmi.objectives.n.score.min"].defaultvalue;
                                                        subobject.score.max = datamodel["cmi.objectives.n.score.max"].defaultvalue;
                                                    }
                                                } else {
                                                    errorCode="351";
                                                    diagnostic = "Data Model Element ID Already Exists";
                                                }
                                            break;
                                            case 'cmi.interactions.n.id':
                                                if (elementIndexes[elementIndexes.length-2] == eval(parentelement+'._count')) {
                                                    eval(parentelement+'._count++;');
                                                    eval(subelement+' = new Object();');
                                                    var subobject = eval(subelement);
                                                    subobject.objectives = {};
                                                    subobject.objectives._count = 0;
                                                }
                                            break;
                                            case 'cmi.interactions.n.objectives.n.id':
                                                if (typeof eval(parentelement) != "undefined") {
                                                    if (!duplicatedID(element,parentelement,value)) {
                                                        if (elementIndexes[elementIndexes.length-2] == eval(parentelement+'._count')) {
                                                            eval(parentelement+'._count++;');
                                                            eval(subelement+' = new Object();');
                                                        }
                                                    } else {
                                                        errorCode="351";
                                                        diagnostic = "Data Model Element ID Already Exists";
                                                    }
                                                } else {
                                                    errorCode="408";
                                                }
                                            break;
                                            case 'cmi.interactions.n.correct_responses.n.pattern':
                                                if (typeof eval(parentelement) != "undefined") {
                                                    // Use cmi.interactions.n.type value to check the right dataelement format
                                                    if (elementIndexes[elementIndexes.length-2] == eval(parentelement+'._count')) {
                                                        var interactiontype = eval(String(parentelement).replace('correct_responses','type'));
                                                        var interactioncount = eval(parentelement+'._count');
                                                        // trap duplicate values, which is not allowed for type choice
                                                        if (interactiontype == 'choice') {
                                                            for (var i=0; (i < interactioncount) && (errorCode=="0"); i++) {
                                                               if (eval(parentelement+'.N'+i+'.pattern') == value) {
                                                                   errorCode = "351";
                                                               }
                                                            }
                                                        }
                                                        if ((typeof correct_responses[interactiontype].limit == 'undefined') ||
                                                            (eval(parentelement+'._count') < correct_responses[interactiontype].limit)) {
                                                            var nodes = [];
                                                            if (correct_responses[interactiontype].delimiter != '') {
                                                                nodes = value.split(correct_responses[interactiontype].delimiter);
                                                            } else {
                                                                nodes[0] = value;
                                                            }
                                                            if (interactiontype == 'choice' && nodes.length == 1) {
																															// Strange :)
                                                              //alert('not enough choices: ' + element);
                                                            }
                                                            if ((nodes.length > 0) && (nodes.length <= correct_responses[interactiontype].max)) {
                                                                errorCode = CRcheckValueNodes (element, interactiontype, nodes, value, errorCode);
                                                            } else if (nodes.length > correct_responses[interactiontype].max) {
                                                                errorCode = "351";
                                                                diagnostic = "Data Model Element Pattern Too Long";
                                                            }
                                                            if ((errorCode == "0") && ((correct_responses[interactiontype].duplicate == false) ||
                                                               (!duplicatedPA(element,parentelement,value))) || (errorCode == "0" && value == "")) {
                                                               eval(parentelement+'._count++;');
                                                               eval(subelement+' = new Object();');
                                                            } else {
                                                                if (errorCode == "0") {
                                                                    errorCode="351";
                                                                    diagnostic = "Data Model Element Pattern Already Exists";
                                                                }
                                                            }
                                                        } else {
                                                            errorCode="351";
                                                            diagnostic = "Data Model Element Collection Limit Reached";
                                                        }
                                                    } else {
                                                        errorCode="351";
                                                        diagnostic = "Data Model Element Collection Set Out Of Order";
                                                    }
                                                } else {
                                                    errorCode="408";
                                                }
                                            break;
                                            default:
                                                if ((parentelement != 'cmi.objectives') && (parentelement != 'cmi.interactions') && (typeof eval(parentelement) != "undefined")) {
                                                    if (elementIndexes[elementIndexes.length-2] == eval(parentelement+'._count')) {
                                                        eval(parentelement+'._count++;');
                                                        eval(subelement+' = new Object();');
                                                    } else {
                                                        errorCode="351";
                                                        diagnostic = "Data Model Element Collection Set Out Of Order";
                                                    }
                                                } else {
                                                    errorCode="408";
                                                }
                                            break;
                                        }
                                    } else {
                                        switch (elementmodel) {
                                            case 'cmi.objectives.n.id':
                                                if (eval(element) != value) {
                                                    errorCode = "351";
                                                    diagnostic = "Write Once Violation";
                                                }
                                            break;
                                            case 'cmi.interactions.n.objectives.n.id':
                                                if (duplicatedID(element,parentelement,value)) {
                                                    errorCode = "351";
                                                    diagnostic = "Data Model Element ID Already Exists";
                                                }
                                            break;
                                            case 'cmi.interactions.n.type':
                                                var subobject = eval(subelement);
                                                subobject.correct_responses = {};
                                                subobject.correct_responses._count = 0;
                                            break;
                                            case 'cmi.interactions.n.learner_response':
                                                if (typeof eval(subelement+'.type') == "undefined") {
                                                    errorCode="408";
                                                } else {
                                                    // Use cmi.interactions.n.type value to check the right dataelement format
                                                    interactiontype = eval(subelement+'.type');
                                                    var nodes = [];
                                                    if (learner_response[interactiontype].delimiter != '') {
                                                        nodes = value.split(learner_response[interactiontype].delimiter);
                                                    } else {
                                                        nodes[0] = value;
                                                    }
                                                    if ((nodes.length > 0) && (nodes.length <= learner_response[interactiontype].max)) {
                                                        expression = new RegExp(learner_response[interactiontype].format);
                                                        for (var i=0; (i < nodes.length) && (errorCode=="0"); i++) {
                                                            if (typeof learner_response[interactiontype].delimiter2 != 'undefined') {
                                                                values = nodes[i].split(learner_response[interactiontype].delimiter2);
                                                                if (values.length == 2) {
                                                                    matches = values[0].match(expression);
                                                                    if (matches == null) {
                                                                        errorCode = "406";
                                                                    } else {
                                                                        var expression2 = new RegExp(learner_response[interactiontype].format2);
                                                                        matches = values[1].match(expression2);
                                                                        if (matches == null) {
                                                                            errorCode = "406";
                                                                        }
                                                                    }
                                                                } else {
                                                                    errorCode = "406";
                                                                }
                                                            } else {
                                                                matches = nodes[i].match(expression);
                                                                if (matches == null) {
                                                                    errorCode = "406";
                                                                } else {
                                                                    if ((nodes[i] != '') && (learner_response[interactiontype].unique)) {
                                                                        for (var j=0; (j<i) && (errorCode=="0"); j++) {
                                                                            if (nodes[i] == nodes[j]) {
                                                                                errorCode = "406";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else if (nodes.length > learner_response[interactiontype].max) {
                                                        errorCode = "351";
                                                        diagnostic = "Data Model Element Pattern Too Long";
                                                    }
                                                }
                                             break;
                                         case 'cmi.interactions.n.correct_responses.n.pattern':
	                                         subel= subelement.split('.');
                                             subel1= 'cmi.interactions.'+subel[2];

                                                if (typeof eval(subel1+'.type') == "undefined") {
                                                    errorCode="408";
                                                } else {
                                                    // Use cmi.interactions.n.type value to check the right //dataelement format
                                                    var interactiontype = eval(subel1+'.type');
                                                    var interactioncount = eval(parentelement+'._count');
                                                    // trap duplicate values, which is not allowed for type choice
                                                    if (interactiontype == 'choice') {
                                                        for (var i=0; (i < interactioncount) && (errorCode=="0"); i++) {
                                                           if (eval(parentelement+'.N'+i+'.pattern') == value) {
                                                               errorCode = "351";
                                                           }
                                                        }
                                                    }
                                                    var nodes = [];
                                                    if (correct_responses[interactiontype].delimiter != '') {
                                                        nodes = value.split(correct_responses[interactiontype].delimiter);
                                                    } else {
                                                        nodes[0] = value;
                                                    }

                                                    if ((nodes.length > 0) && (nodes.length <= correct_responses[interactiontype].max)) {
                                                        errorCode = CRcheckValueNodes (element, interactiontype, nodes, value, errorCode);
                                                    } else if (nodes.length > correct_responses[interactiontype].max) {
                                                        errorCode = "351";
                                                        diagnostic = "Data Model Element Pattern Too Long";
                                                    }
                                                }
                                             break;
                                        }
                                    }
                                }
                            }
                            //Store data
                            if (errorCode == "0") {

                                if ((typeof eval('datamodel["'+elementmodel+'"].range')) != "undefined") {
                                    range = eval('datamodel["'+elementmodel+'"].range');
                                    ranges = range.split('#');
                                    value = value*1.0;
                                    if (value >= ranges[0]) {
                                        if ((ranges[1] == '*') || (value <= ranges[1])) {
                                            eval(element+'=value;');
                                            errorCode = "0";
                                            if (elsScormDebug) {
                                                SCORM_log("SetValue", element, value, errorCode);
                                            }

                                            return "true";
                                        } else {
                                            errorCode = '407';
                                        }
                                    } else {
                                        errorCode = '407';
                                    }
                                } else {
                                    eval(element+'=value;');
                                    errorCode = "0";
                                    if (elsScormDebug) {
                                        SCORM_log("SetValue", element, value, errorCode);
                                    }

                                    return "true";
                                }
                            }
                        } else {
                            errorCode = "406";
                        }
                    } else {
                        errorCode = "404";
                    }
                } else {
                    errorCode = "401"
                }
            } else {
                errorCode = "351";
            }
        } else {
            if (Terminated) {
                errorCode = "133";
            } else {
                errorCode = "132";
            }
        }
        if (elsScormDebug) {
            SCORM_log("SetValue", element, value, errorCode);
        }

        return "false";
    }


    function CRremovePrefixes (node) {
        // check for prefixes lang, case, order
        // case and then order
        var seenOrder = false;
        var seenCase = false;
        var seenLang = false;
        var errorCode = "0";
        while (matches = node.match('^(\{(lang|case_matters|order_matters)=([^\}]+)\})')) {
            switch (matches[2]) {
                case 'lang':
                    // check for language prefix on each node
                    langmatches = node.match(CMILangcr);
                    if (langmatches != null) {
                        lang = langmatches[3];
                        // check that language string definition is valid
                        if (lang.length > 0 && lang != undefined) {
                            if (validLanguages[lang.toLowerCase()] == undefined) {
                                errorCode = "406";
                            }
                        }
                    }
                    seenLang = true;
                break;

                case 'case_matters':
                    // check for correct case answer
                    if (! seenLang && ! seenOrder && ! seenCase) {
                        if (matches[3] != 'true' && matches[3] != 'false') {
                            errorCode = "406";
                        }
                    }
                    seenCase = true;
                break;

                case 'order_matters':
                    // check for correct case answer
                    if (! seenCase && ! seenLang && ! seenOrder) {
                        if (matches[3] != 'true' && matches[3] != 'false') {
                            errorCode = "406";
                        }
                    }
                    seenOrder = true;
                break;

                default:
                break;
            }
            node = node.substr(matches[1].length);
        }
        return {'errorCode': errorCode, 'node': node};
    }


    function CRcheckValueNodes(element, interactiontype, nodes, value, errorCode) {
        expression = new RegExp(correct_responses[interactiontype].format);
        for (var i=0; (i < nodes.length) && (errorCode=="0"); i++) {
            if (interactiontype.match('^(fill-in|long-fill-in|matching|performance|sequencing)$')) {
                result = CRremovePrefixes(nodes[i]);
                errorCode = result.errorCode;
                nodes[i] = result.node;
            }

            // check for prefix on each node
            if (correct_responses[interactiontype].pre != '') {
                matches = nodes[i].match(correct_responses[interactiontype].pre);
                if (matches != null) {
                    nodes[i] = nodes[i].substr(matches[1].length);
                }
            }

            if (correct_responses[interactiontype].delimiter2 != undefined) {
                values = nodes[i].split(correct_responses[interactiontype].delimiter2);
                if (values.length == 2) {
                    matches = values[0].match(expression);
                    if (matches == null) {
                        errorCode = "406";
                    } else {
                        var expression2 = new RegExp(correct_responses[interactiontype].format2);
                        matches = values[1].match(expression2);
                        if (matches == null) {
                            errorCode = "406";
                        }
                    }
                } else {
                     errorCode = "406";
                }
            } else {
                matches = nodes[i].match(expression);
                //if ((matches == null) || (matches.join('').length == 0)) {
                if ((matches == null && value != "")||(matches == null && interactiontype=="true-false")){
                    errorCode = "406";
                } else {
                    // numeric range - left must be <= right
                    if (interactiontype == 'numeric' && nodes.length > 1) {
                        if (parseFloat(nodes[0]) > parseFloat(nodes[1])) {
                            errorCode = "406";
                        }
                    } else {
                        if ((nodes[i] != '') && (correct_responses[interactiontype].unique)) {
                            for (var j=0; (j < i) && (errorCode=="0"); j++) {
                                if (nodes[i] == nodes[j]) {
                                    errorCode = "406";
                                }
                            }
                        }
                    }
                }
            }
        } // end of for each nodes
        return errorCode;
    }


    function Commit (param) {
        errorCode = "0";
        if (param == "") {
            if ((Initialized) && (!Terminated)) {
                result = StoreData(cmi,false);
                if (elsScormDebug) {
                    SCORM_log("Commit", param, "", errorCode);
                }
                
                return "true";
            } else {
                if (Terminated) {
                    errorCode = "143";
                } else {
                    errorCode = "142";
                }
            }
        } else {
            errorCode = "201";
        }
        if (elsScormDebug) {
            SCORM_log("Commit", param, "", errorCode);
        }
        return "false";
    }

    function GetLastError () {
        if (elsScormDebug) {
            SCORM_log("GetLastError", "", "", errorCode);
        }

        return errorCode;
    }

    function GetErrorString (param) {
        if (param != "") {
            var errorString = "";
            switch(param) {
                case "0":
                    errorString = "No error";
                break;
                case "101":
                    errorString = "General exception";
                break;
                case "102":
                    errorString = "General Inizialization Failure";
                break;
                case "103":
                    errorString = "Already Initialized";
                break;
                case "104":
                    errorString = "Content Instance Terminated";
                break;
                case "111":
                    errorString = "General Termination Failure";
                break;
                case "112":
                    errorString = "Termination Before Inizialization";
                break;
                case "113":
                    errorString = "Termination After Termination";
                break;
                case "122":
                    errorString = "Retrieve Data Before Initialization";
                break;
                case "123":
                    errorString = "Retrieve Data After Termination";
                break;
                case "132":
                    errorString = "Store Data Before Inizialization";
                break;
                case "133":
                    errorString = "Store Data After Termination";
                break;
                case "142":
                    errorString = "Commit Before Inizialization";
                break;
                case "143":
                    errorString = "Commit After Termination";
                break;
                case "201":
                    errorString = "General Argument Error";
                break;
                case "301":
                    errorString = "General Get Failure";
                break;
                case "351":
                    errorString = "General Set Failure";
                break;
                case "391":
                    errorString = "General Commit Failure";
                break;
                case "401":
                    errorString = "Undefinited Data Model";
                break;
                case "402":
                    errorString = "Unimplemented Data Model Element";
                break;
                case "403":
                    errorString = "Data Model Element Value Not Initialized";
                break;
                case "404":
                    errorString = "Data Model Element Is Read Only";
                break;
                case "405":
                    errorString = "Data Model Element Is Write Only";
                break;
                case "406":
                    errorString = "Data Model Element Type Mismatch";
                break;
                case "407":
                    errorString = "Data Model Element Value Out Of Range";
                break;
                case "408":
                    errorString = "Data Model Dependency Not Established";
                break;
            }
            if (elsScormDebug) {
                SCORM_log("GetErrorString", param, errorString, 0);
            }

            return errorString;
        } else {
            if (elsScormDebug) {
                SCORM_log("GetErrorString", param, "not found", 0);
            }
            return "";
        }
    }

    function GetDiagnostic (param) {
        if (diagnostic != "") {
            if (elsScormDebug) {
                SCORM_log("GetDiagnostic", param, diagnostic, 0);
            }

            return diagnostic;
        }
        if (elsScormDebug) {
            SCORM_log("GetDiagnostic", param, diagnostic, 0);
        }
        return param;
    }

    function duplicatedID (element, parent, value) {
        var found = false;
        var elements = eval(parent+'._count');
        for (var n=0;(n < elements) && (!found);n++) {
            if ((parent+'.N'+n+'.id' != element) && (eval(parent+'.N'+n+'.id') == value)) {
                found = true;
            }
        }
        return found;
    }

    function duplicatedPA (element, parent, value) {
        var found = false;
        var elements = eval(parent+'._count');
        for (var n=0;(n < elements) && (!found);n++) {
            if ((parent+'.N'+n+'.pattern' != element) && (eval(parent+'.N'+n+'.pattern') == value)) {
                found = true;
            }
        }
        return found;
    }

    function getElementModel(element) {
        if (typeof datamodel[element] != "undefined") {
            return element;
        } else {
            var expression = new RegExp(CMIIndex,'g');
            var elementmodel = String(element).replace(expression,'.n.');
            if (typeof datamodel[elementmodel] != "undefined") {
                return elementmodel;
            }
        }
        return false;
    }

    function AddTime (first, second) {
        var timestring = 'P';
        var matchexpr = /^P((\d+)Y)?((\d+)M)?((\d+)D)?(T((\d+)H)?((\d+)M)?((\d+(\.\d{1,2})?)S)?)?$/;
        var firstarray = first.match(matchexpr);
        var secondarray = second.match(matchexpr);
        if ((firstarray != null) && (secondarray != null)) {
            var firstsecs=0;
            if(parseFloat(firstarray[13],10)>0){ firstsecs=parseFloat(firstarray[13],10); }
            var secondsecs=0;
            if(parseFloat(secondarray[13],10)>0){ secondsecs=parseFloat(secondarray[13],10); }
            var secs = firstsecs+secondsecs;  //Seconds
            var change = Math.floor(secs/60);
            secs = Math.round((secs-(change*60))*100)/100;
            var firstmins=0;
            if(parseInt(firstarray[11],10)>0){ firstmins=parseInt(firstarray[11],10); }
            var secondmins=0;
            if(parseInt(secondarray[11],10)>0){ secondmins=parseInt(secondarray[11],10); }
            var mins = firstmins+secondmins+change;   //Minutes
            change = Math.floor(mins / 60);
            mins = Math.round(mins-(change*60));
            var firsthours=0;
            if(parseInt(firstarray[9],10)>0){ firsthours=parseInt(firstarray[9],10); }
            var secondhours=0;
            if(parseInt(secondarray[9],10)>0){ secondhours=parseInt(secondarray[9],10); }
            var hours = firsthours+secondhours+change; //Hours
            change = Math.floor(hours/24);
            hours = Math.round(hours-(change*24));
            var firstdays=0;
            if(parseInt(firstarray[6],10)>0){ firstdays=parseInt(firstarray[6],10); }
            var seconddays=0;
            if(parseInt(secondarray[6],10)>0){ firstdays=parseInt(secondarray[6],10); }
            var days = Math.round(firstdays+seconddays+change); // Days
            var firstmonths=0;
            if(parseInt(firstarray[4],10)>0){ firstmonths=parseInt(firstarray[4],10); }
            var secondmonths=0;
            if(parseInt(secondarray[4],10)>0){ secondmonths=parseInt(secondarray[4],10); }
            var months = Math.round(firstmonths+secondmonths);
            var firstyears=0;
            if(parseInt(firstarray[2],10)>0){ firstyears=parseInt(firstarray[2],10); }
            var secondyears=0;
            if(parseInt(secondarray[2],10)>0){ secondyears=parseInt(secondarray[2],10); }
            var years = Math.round(firstyears+secondyears);
        }
        if (years > 0) {
            timestring += years + 'Y';
        }
        if (months > 0) {
            timestring += months + 'M';
        }
        if (days > 0) {
            timestring += days + 'D';
        }
        if ((hours > 0) || (mins > 0) || (secs > 0)) {
            timestring += 'T';
            if (hours > 0) {
                timestring += hours + 'H';
            }
            if (mins > 0) {
                timestring += mins + 'M';
            }
            if (secs > 0) {
                timestring += secs + 'S';
            }
        }
        return timestring;
    }

    function TotalTime() {
        var total_time = AddTime(cmi.total_time, cmi.session_time);
        return '&'+underscore('cmi.total_time')+'='+encodeURIComponent(total_time);
    }

    function CollectData(data,parent) {
        var datastring = '';
        for (property in data) {
            if (typeof data[property] == 'object') {
                datastring += CollectData(data[property],parent+'.'+property);
            } else {
                var element = parent+'.'+property;
                var expression = new RegExp(CMIIndexStore,'g');
                var elementmodel = String(element).replace(expression,'.n.');
                if ((typeof eval('datamodel["'+elementmodel+'"]')) != "undefined") {
                    if (eval('datamodel["'+elementmodel+'"].mod') != 'r') {
                        var elementstring = '&'+underscore(element)+'='+encodeURIComponent(data[property]);
                        if ((typeof eval('datamodel["'+elementmodel+'"].defaultvalue')) != "undefined") {
                            if (eval('datamodel["'+elementmodel+'"].defaultvalue') != data[property] || eval('typeof(datamodel["'+elementmodel+'"].defaultvalue)') != typeof(data[property])) {
                                datastring += elementstring;
                            }
                        } else {
                            datastring += elementstring;
                        }
                    }
                }
            }
        }
        return datastring;
    }

    function StoreData(data,storetotaltime) {
        var datastring = '';
        if (storetotaltime) {
            if (cmi.mode == 'normal') {
                if (cmi.credit == 'credit') {
                    if ((cmi.completion_threshold != null) && (cmi.progress_measure != null)) {
                        if (cmi.progress_measure >= cmi.completion_threshold) {
                            cmi.completion_status = 'completed';
                        } else {
                            cmi.completion_status = 'incomplete';
                        }
                    }
                    if ((cmi.scaled_passed_score != null) && (cmi.score.scaled != '')) {
                        if (cmi.score.scaled >= cmi.scaled_passed_score) {
                            cmi.success_status = 'passed';
                        } else {
                            cmi.success_status = 'failed';
                        }
                    }
                }
            }
            datastring += TotalTime();
        }
        datastring = CollectData(data,'cmi') + datastring;
        var element = 'adl.nav.request';
        var navrequest = eval(element) != datamodel[element].defaultvalue ? '&'+underscore(element)+'='+encodeURIComponent(eval(element)) : '';
        datastring += navrequest;

        var myRequest = NewHttpReq();
        var result = DoRequest(myRequest, elsScormRequestUrl, datastring);
        var results = String(result).split('\n');
        if ((results.length > 2) && (navrequest != '')) {
            //eval(results[2]);
        }
        errorCode = results[1];
        if (elsScormDebug) {
            SCORM_log("StoreData", data, datastring, errorCode);
        }

        return results[0];
    }

    this.Initialize = Initialize;
    this.Terminate = Terminate;
    this.GetValue = GetValue;
    this.SetValue = SetValue;
    this.Commit = Commit;
    this.GetLastError = GetLastError;
    this.GetErrorString = GetErrorString;
    this.GetDiagnostic = GetDiagnostic;
    this.version = '1.0';
}
