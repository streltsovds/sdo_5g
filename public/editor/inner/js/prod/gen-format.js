var formatScheme = (input) => schemesFormat.runtime(input);
/**
* @return {
*     html: [HTML-код],
*     questions: [Тесты],
*     glossary: [Термины],
*     schemes: [Массив с названиями схем]
* }
*/

// Обработчики схем ============================================================
var schemesHandler = {
// Схема «Логотип» -------------------------------------------------------------
    logo: function($output, type) {
        $output.find('li').each(function(){
            var tContent = $(this).html().split('&gt;&gt;');
            $(this).html(`
                <div class="${type}-letter">${tContent[0]}</div>
                <div class="${type}-hint block-hint">${tContent[1]}</div>
            `);
        });

        return $output;
    },
// Схема «Карта» ---------------------------------------------------------------
    map: function($output, type) {
        var mapImg = $output.find('li:first-child').text().split('|'),
            mapImgSize = mapImg[1].split('x');

        $output.find('li:first-child').remove();
        $output.css({
            backgroundImage: 'url("'+ mapImg[0].trim() +'")',
            width: parseInt(mapImgSize[0]),
            height: parseInt(mapImgSize[1])
        });

        $output.find('li').each(function(){
            var itemText = $(this).html(),
                pointOrigin = [50, 50];
            if(itemText[0] == '[' && itemText.indexOf(']') > -1){
                pointOrigin = (itemText.split(']')[0]).split(',')
                    .map(x => parseInt(x.replace(/\[/, '')));

                pointOrigin.forEach((item, i) => {
                    // превращение процентов в пиксели
                    pointOrigin[i] = (pointOrigin[i] / 100) * mapImgSize[i];
                    // правка позиции
                    pointOrigin[i] += i ? -18 : 30;
                    // обратно в проценты, плюс знак
                    pointOrigin[i] = (
                        (pointOrigin[i] / mapImgSize[i]) * 100
                    ) + '%';
                });

                // удаление координат из текста элемента
                itemText = itemText.split(']');
                itemText.shift()
                itemText = itemText.join(']');
            }

            $(this).css({left: pointOrigin[0], top: pointOrigin[1]}).html(
                '\n<div class="'+ type +'-point"> </div>'+
                '\n<div class="'+ type +'-hint block-hint">'+
                    itemText.replace(/\&nbsp\;/, '').trim() +
                '</div>\n'
            )

        });

        return $output;
    },
// Схема «аккордеон» -----------------------------------------------------------
    accordeon: function($output, type) {
        var blockHeight;
        $output.find('li').each(function(i){
            if(i == 0 && $(this).text().indexOf('высота блока:') > -1){
                var blockFontSize = 15;
                blockHeight =
                    (parseInt($(this).text().replace(/[^0-9.]/gm, ''))
                    * (blockFontSize * 1.2)) /* line height */
                    + (10*2) /* Padding */ + 50 /* Title height */;
                $(this).remove();
            }
        });

        $output.find('li').each(function(i){
            $(this).height(blockHeight);
            var tContent = $(this).html().split('&gt;&gt;');
            $(this).attr('data-title', tContent[0]).html(
                '\n<div>'+ tContent[1] +'</div>\n'
            );
            if(i == 0){
                $(this).addClass('showed');
            }
        });
        $output.find('li').attr('onclick', 'accordeon(this);');

        return $output;
    },
// Схема «Drag & drop» ---------------------------------------------------------
    dragndrop: function($output, type, params) {
        var mapImg          = $output.find('li:first-child').text().split('|');
        var mapImgSize      = mapImg[1].split('x');
        var defaultObjSize  = (
            mapImg[2] && mapImg[2].indexOf('x') && mapImg[2].trim()
        );
        var bgTable         = false;
        var qOut            = '';
        var fontSize;

        mapImg.forEach(item => {
            if(/шрифт|font/i.test(item)){
                fontSize = parseInt(
                    item.replace(/[шрифт|font]:?\s?/gi, '').trim()
                );
            }
        });

        if(defaultObjSize){
            defaultObjSize = defaultObjSize.split('x');
            defaultObjSize = [
                defaultObjSize[0],
                (defaultObjSize[1] || defaultObjSize[0])
            ]
        }

        if(params && params.test){
            qOut +=
                (($output.prev().attr('start') || 1) +'. '+
                $output.prev().text().replace(/\n/g, '')) + ' | '+
                $output.find('li:first-child').text() +'\n';

            $output.find('li:not(:first)').each(function(){
                qOut += '+ '+ $(this).text() +'\n';
            });
        }

        $output.find('li:first-child').remove();
        $output.addClass('dnd-workspace').wrap('<div class="dnd" />');

        $output.find('li').each(function(){
            if($(this).text().indexOf('!!!') > -1){
                $(this).parent('ul').attr(
                    'data-finalmsg',
                    $(this).text().replace('!!!', '').trim()
                );
                $(this).remove();
            }
        });

        $output.css({
            width:  parseInt(mapImgSize[0]),
            height: parseInt(mapImgSize[1])
        });

        if(['table', 'таблица'].indexOf(mapImg[0].trim())
        && $output.parents('.dnd').prev().is('table')){
            $output.parents('.dnd').prepend(
                $output.parents('.dnd').prev().addClass('scheme-dnd-table')
                    .css({
                        width: parseInt(mapImgSize[0]),
                        height: parseInt(mapImgSize[1])
                    })
            );

            let $dndTableCells = $output.parents('.dnd').find('table tr');
            bgTable = parseInt(mapImgSize[1]) / $dndTableCells.length;
            $dndTableCells.css({
                height: bgTable +'px'
            });

        }else{
            $output.css({
                backgroundImage: 'url("'+ mapImg[0].trim() +'")'
            });
        }

        $output.after(
            '<div class="dnd-list cf" style="width:'+
            parseInt(mapImgSize[0]) +'px">'
        );


        var dropBoxCoordList = [], kcount = 0;
        $output.find('li').each(function(i){
            var itemText = $(this).html(),
                pointOrigin = ['50%', '50%'];

            if(itemText[0] == '[' && itemText.indexOf(']') > -1){
                pointOrigin = (itemText.split(']')[0])
                    .split(',')
                    .map(function(x){
                        return parseInt(x.replace(/\[/, ''));
                    });

                // добавление знака процента
                pointOrigin.forEach(function(item, i){
                    pointOrigin[i] += '%';
                });

                // удаление координат из текста элемента
                itemText = itemText.split(']');
                itemText.shift()
                itemText = itemText.join(']');
            }

            itemText = (function(){
                var $itemTextWrap = $('<div>');
                $itemTextWrap.append(itemText);
                $itemTextWrap.find('*').each(function(){
                    if(!$(this).text().trim()){
                        $(this).remove();
                    }
                });

                return $itemTextWrap.html();
            })();

            // Поиск элементов с похожими кооржинатами в списке координат
            var dataFor = kcount,
                dropBoxDefined = false,
                dataHint = 0;

            dropBoxCoordList.forEach(function(item, key){
                if(!dropBoxDefined && item[0] == pointOrigin[0]
                && item[1] == pointOrigin[1]) {
                    dataFor = key;
                    dropBoxDefined = true;
                    dataHint++;
                }
            });

            if(!dropBoxDefined)
                kcount++;

            // Добавляем координаты в список
            dropBoxCoordList.push(pointOrigin);

            var $_dragBox = $('<div>')
                .addClass('dnd-dragbox')
                .attr('unselectable', 'on')
                .attr('data-for', dataFor)
                .attr('data-hint', dataHint)
                .css({
                    height: (
                        bgTable ?
                            (bgTable-10)+'px' :
                            (defaultObjSize ? (defaultObjSize[1]-10)+'px' : '')
                    ),
                    width: (defaultObjSize ? (defaultObjSize[0]-10)+'px' : '' ),
                    fontSize: fontSize+'px'
                })
                .html(`
                    <span unselectable="on">
                        ${itemText.replace(/^(.*)\|(.*)+/, '$1').trim()}
                    </span>
                `)

            $(this).parent('ul').next('.dnd-list').append($_dragBox);

            if(!dropBoxDefined){
                var _twidth  = defaultObjSize ? defaultObjSize[0] +'px' : '';
                var _theight = (
                    bgTable ?
                        bgTable+'px' : (
                            defaultObjSize ? defaultObjSize[1] +'px' : ''
                        )
                );

                var _datafeedback = (
                    itemText.trim()
                        .trim()
                        .split('|')
                )[1] || '';
                _datafeedback = _datafeedback.replace(/(<([^>]+)>)/ig, '');
                $(this)
                    .replaceWith(
                        $('<div>')
                            .addClass('dnd-dropbox').css({
                                left:       pointOrigin[0],
                                top:        pointOrigin[1],
                                background: '#fff',
                                width:      _twidth,
                                height:     _theight,
                                lineHeight: (
                                    bgTable ?
                                        bgTable+'px' :
                                        (defaultObjSize ?
                                            defaultObjSize[1] +'px' :
                                            ''
                                        )
                                )
                            })
                            .attr(
                                'data-feedback',
                                _datafeedback
                            )
                            .attr('data-size', 10)
                            .html(
                                (
                                    (mapImg[2] && mapImg[2]
                                        .trim()
                                        .match(/без нумерации/ui
                                    )
                                    && mapImg[2]
                                        .trim()
                                        .match(/без нумерации/ui).length + 1)
                                    || (
                                        mapImg[3]
                                        && mapImg[3]
                                            .trim()
                                            .match(/без нумерации/ui)
                                        && mapImg[3]
                                            .trim()
                                            .match(/без нумерации/ui).length + 1
                                    )
                                )  ? '' : (`\n${i + 1}\n`)
                            )
                            .append(
                                '\n<div class="scheme-map-hint block-hint"'+
                                'data-hint="'+ dataHint +'">\n'+
                                    _datafeedback +
                                '\n</div>\n'
                            )
                    );
            }else{
                $(this).parent().find('.dnd-dropbox').eq(dataFor)
                    .append(
                        '\n<div class="scheme-map-hint block-hint" '+
                        'data-hint="'+ dataHint +'">\n'+
                            itemText.replace(/^(.*)\|/gm, '').trim() +
                        '\n</div>\n'
                    );
                $(this).remove();
            }

        });

        $output.replaceWith(function(){
            var $dnd = $('<div class="dnd-workspace">');
            $.each(this.attributes, function(i, attribute){
                $dnd.attr(
                    attribute.name,
                    attribute.value
                );
            });

            $dnd.html($(this).html());

            return $dnd;
        });


        if(params && params.test) {
            return {
                output: $output,
                tests: qOut || ''
            }
        }

        return $output;
    },
// Схема «Диалог» --------------------------------------------------------------
    dialog: function($output, type) {

        var mapImg = $output.find('li:first-child').text().split('|'),
            mapImgSize = mapImg[1].split('x');
        $output.find('li:first-child').remove();
        $output.css({
            backgroundImage: 'url("'+ mapImg[0].trim() +'")',
            width: parseInt(mapImgSize[0]),
            height: parseInt(mapImgSize[1])
        });

        $output.find('li').each(function(i){
            var itemText = $(this).html(),
                pointOrigin = [50, 50];

            if(itemText[0] == '[' && itemText.indexOf(']') > -1) {
                pointOrigin = itemText.split(']')[0]
                    .split(',')
                    .map(x => parseInt(x.replace(/\[/, '')));

                // добавление знака процента
                pointOrigin.forEach((item, i) => {
                    pointOrigin[i] += '%';
                });

                // удаление координат из текста элемента
                itemText = itemText.split(']');
                itemText.shift();
                itemText = itemText.join(']');
            }

            $(this).css({
                left: pointOrigin[0],
                top: pointOrigin[1],
                zIndex: 999 - i
            }).html(
                '\n<div class="'+ type +'-replica">'+
                    itemText.replace(/\&nbsp\;/, '').trim() +
                '</div>\n'
            );


            // определение положения
            if(parseInt(pointOrigin[0]) > 50)
                $(this).find('.'+ type +'-replica').addClass('right');

            if(!i)
                $(this).find('.'+ type +'-replica').addClass('showed');
        });

        // добавление кнопок управления диалогом
        if($output.find('li').length > 1) {
            $output.append(
                `<div class="dialog-arrs">
                    <a href="#" class="dialog-arr darr-left"></a>
                    <a href="#" class="dialog-arr darr-right"></a>
                </div>`)
            $output.find('.dialog-arrs .darr-right').addClass('showed');
        }

        return $output;
    },
// Схема «Объекты» -------------------------------------------------------------
    obj: function($output, type) {
        var mapImg = $output.find('li:first-child').text().split('|'),
            mapImgSize = mapImg[1].split('x'),
            defaultObjSize = (
                mapImg[2] && !mapImg[2].match(/круг/ui) && mapImg[2].trim()
            ),
            borderRounded = (
                defaultObjSize ?
                    (mapImg[3] && mapImg[3].match(/круг/ui)) :
                    (mapImg[2] && mapImg[2].match(/круг/ui))
            );

        $output.find('li:first-child').remove();
        $output.css({
            backgroundImage: 'url("'+ mapImg[0].trim() +'")',
            width: parseInt(mapImgSize[0]),
            height: parseInt(mapImgSize[1])
        });

        $output.find('li').each(function() {
            var itemText = $(this).html(),
                pointOrigin = [50, 50],
                pointSize;

            if(itemText[0] == '[' && itemText.indexOf(']') > -1){
                pointOrigin = ( itemText.split(']')[0] ).split(',').map(
                    x => parseInt(x.replace(/\[/, ''))
                );

                // добавление знака процента
                pointOrigin = pointOrigin.map(item => item += '%');

                // определяем размеры объекта
                pointSize = (
                    itemText.split(']')[2]
                    && itemText.split(']')[1].replace(/\[/g, '').split('x')
                );

                // удаление координат из текста элемента
                itemText = itemText.split(']');
                itemText.shift()
                if(pointSize)
                    itemText.shift()
                itemText = itemText.join(']');

                if(defaultObjSize && !pointSize)
                    pointSize = defaultObjSize.split('x');
            }

            var _styles = `${(pointSize ?
                `style="width:${pointSize[0]}px;`+
                    (pointSize[1] ?
                        `height:${pointSize[1]}` : `height:${pointSize[0]}`
                    ) +'px"' : '')}`;
            $(this).css({
                left: pointOrigin[0],
                top: pointOrigin[1]
            }).html(
                `\n<div class="${type}-point${
                    (borderRounded ? ' rounded' : '')
                }" ${_styles}> </div>`+
                '\n<div class="'+ type +'-hint block-hint">\n'+
                    itemText.replace(/\&nbsp\;/, ' ').trim() +
                '\n</div>\n'
            );

        });

        return $output;

    }
}
// Функции преобразования ======================================================
var schemesFormat = {
    types: Object.keys(schemesHandler),
// Запуск ----------------------------------------------------------------------
    runtime: function(input) {
        var $output,
            outputHtml,
            qOut = '', gOut = '',
            testHandler,
            tempOut;

        // предвраительная обработка
        input   = this.formatInfo(input);        // блоки «комментарий»: #{...}

        input   = this.formatUSB(input);         // блоки «флешка»: #{...}
        tempOut = this.terminsSearch(input);     // поиск терминов: &{...}
        input   = tempOut.output;                // находим и вырезаем термины
        if(tempOut.glossary.length)
            gOut = tempOut.glossary

        input = this.removeComm(input);          // удаление комментариев: {...}

        // DOM-обработка
        // создание обёртки для обработки как DOM-модели: <div>...</div>
        $output = this.textToDOM(input);
        // удаляем пустые <p>-элементы перед обработкой схем и тестов
        $output.find('p, li').each(function() {
            var thisText = $(this).text().trim();

            if(thisText == ' ' || thisText == ' ' || thisText == '&nbsp;'
            || !thisText || thisText == 'undefined')
                $(this).remove();

        });

         // перебираем списки для поиска схем или схематических тестов
        tempOut = this.schemeCheck($output);
        $output = tempOut.output;                // пишем схемы
        qOut   += tempOut.tests;                 // пишем тесты

        // ищем и заменяем айфреймы
        $output = this.replaceIframes($output);

        // обработка тестов
        testHandler = this.handleTests($output); // проверяем output на тесты
        if(testHandler.tests){                   // и если есть тесты,
            $output = testHandler.output;        // вырезаем их,
            qOut   += testHandler.tests;         // затем пишем их в переменную
        }

        // стилизуем таблицы
        $output.find('table:not(.scheme-dnd-table)')
            .wrap('<div class="tablestyle" />');
        // удаляем из ячеек таблиц p-элементы, созданные wysiwyg'ом
        $output.find('table th p, td p').each(function() {
            $(this).replaceWith(this.childNodes)
        });
        // добавляем ссылкам атрибут для откртытия в другой вкладке
        $output.find('a').attr('target', '_blank');

        // постформатирование всех элеметнов, подготовка к выводу
        outputHtml = this.postFormat($output.html())

        // Возвращаем данные
        return {
            html:      outputHtml,     // вывод html-кода
            questions: qOut || '',     // вывот тесты в виде текста
            glossary:  gOut || '',     // термины для глоссария
            schemes:   this.scList     // вывод массива со списком схем
        }
    },
// Удаление комментариев -------------------------------------------------------
    removeComm: function(input) {
        var output = input,
            comArray;

        // поиск комментариев в фигурных скобках, преобразование в массив
        comArray = output.match(/\{[\s\S]*?\}/gm);
        comArray && comArray.length && (() => {

            // удаление всего содержимого комментария,
            // кроме тегов, создание нового массива
            var comArrayMatched = comArray.map(
                (item, i) => (item.match(/<[^>]*>/gm) || []).join('')
            );

            // удаление комментариев, замена на присутствующие
            // в них теги (если есть)
            comArray.forEach((item, i) => {
                output = output.replace(item, comArrayMatched[i]);
            });
        })();

        return output;
    },
// Обработка блоков «#{}» ------------------------------------------------------
    formatInfo: function(input) {
        return input.replace(
            /#\{[\s]?([\s\S]*?)\}/gm,
            '<div class="block-comment">$1</div>'
        );
    },
// Обработка блоков ${} --------------------------------------------------------
    formatUSB: function(input) {
        return input.replace(
            /\$\{[\s]?([\s\S]*?)\}/gm,
            '<div class="block-fleshka">$1</div>'
        );
    },
// Поиск терминов --------------------------------------------------------------
    terminsSearch: function(input) {
        let output   = input.replace(/&amp;/g, '&');
        let glossary = output.match(/&{[\s\S]*?}/gm);

        return {
            output: output.replace(/&{[\s\S]*?}/gm, ''),
            glossary: glossary ? glossary.map(item =>
                item.replace(/&{([\s\S]*)?}/gm, '$1')
            ) : []
        }
    },
// Проверка на наличие схем ----------------------------------------------------
    schemeCheck: function(input) {
        var $output     = this.textToDOM(input);
        var schemeTypes = this.types;
        var tempOut;
        var qOut = '';
        var schREplace;

        // список схем для возврата
        this.scList = [];
        // прогоняем все найденые ul-списки
        $output.find('ul').each(function() {

            // если список содержит класс схемы
            var type = $(this).find('li:first-child').text().toLowerCase();
            if(schemeTypes.indexOf(type.replace(/scheme-/ig, '')) > -1
            || schemeTypes.indexOf(type.replace(/test-/ig, '')) > -1) {
                // поиск легенды
                $(this).find('li').each(function() {
                    if($(this).text().indexOf('???') > -1){
                        $(this).parent('ul')
                            .after(
                                '\n\r<div class="legend">\n\r'+
                                $(this).text().trim().replace(/\?\?\?/, '') +
                                '\n\r</div>\n\r'
                            );
                        $(this).remove();
                    }
                });

                schREplace = schemesFormat.schemeRepalce($(this));
                tempOut    = schREplace.output;
                qOut       += schREplace.tests;

                $(this).replaceWith(tempOut);

            }
        });

        return {
            output: $output,
            tests:  qOut || ''
        }
    },
// Заменяем найденные схемы ----------------------------------------------------
    schemeRepalce: function($output) {
        var type = $output.find('li:first-child').text().toLowerCase();
        var qOut = '';

        // добавляем схему в список для вывода
        this.scList.push(type.replace('scheme-', ''));

        // определяем таблицу
        $output.addClass(type);
        $output.find('li:first-child').remove();

        if(type.indexOf('logo') + 1)
            $output = schemesHandler.logo($output, type);

        else if(type.indexOf('map') + 1)
            $output = schemesHandler.map($output, type);

        else if(type.indexOf('accordeon') + 1)
            $output = schemesHandler.accordeon($output, type);

        else if(type.indexOf('dragndrop') + 1) {
            if(type.indexOf('scheme') + 1)
                $output = schemesHandler.dragndrop($output, type);
            else if(type.indexOf('test') + 1) {
                let dndOut = schemesHandler.dragndrop(
                    $output,
                    type,
                    {test: true}
                );
                $output    = dndOut.output;
                qOut       += dndOut.tests;
            }

        } else if(type.indexOf('dialog') + 1)
            $output = schemesHandler.dialog($output, type);

        else if(type.indexOf('obj') + 1)
            $output = schemesHandler.obj($output, type);

        return {
            output: $output,
            tests: qOut || ''
        }
    },
// Проверка на наличие тестов --------------------------------------------------
    handleTests: function($output) {
        $output.find('ol').each(function() {
            var answText;

            // Проверяем список на содержание теста
            // с одиночным или с множественным ответом
            if(true
                && $(this).next().is('p')
                && (answText = $(this).next().text())
                && (answText.match(/правильны(.*) ответ/ui))
                && $(this).prev().is('ol')
                && $(this).prev().find('li').length == 1
            ){
                var rightAnswer;
                var questIndex = $(this).prev('ol').attr('start') || 1;

                $(this).addClass('test-question');

                // одиночный ответ
                if(answText.match(/ный/mgui)){
                    rightAnswer = answText
                        .replace(/.*ный .*вет/mgui, '')
                        .replace(/:/mgui, '')
                        .trim();

                    // находим верный ответ, ставим перед ним звёздочку *
                    $(this).find('li').each(function() {
                        if($(this).text()[0].trim() == rightAnswer){
                            $(this).text(
                                '* '+ $(this).text().replace(/^[\S]\. /gmi, '')
                            )
                        }else{
                            $(this).text(
                                ' '+ $(this).text().replace(/^[\S]\. /gmi, '')
                            )
                        }
                    });

                // множественный ответ
                }else if(answText.match(/(ные|веты)/mgui)){
                    rightAnswer = (
                        answText.trim()
                            .replace(/.*ные .*веты/mgui, '')
                            .replace(/:/mgui, '')
                    ).split(',').map(item => item.trim());

                    // Находим верный ответ, ставим перед ним плюс `+`,
                    // перед неверным — минус `-`
                    $(this).find('li').each(function() {
                        var qPrefix = rightAnswer.indexOf(
                                $(this).text()[0].trim()
                            ) > -1 ? '+' : '-';
                        $(this).text(
                            qPrefix +' '+
                            $(this).text().replace(/^[\S]\. /gim, ''));
                    });
                }

                var qQuestion = $(this).prev();
                $(this).attr(
                    'data-quest',
                    (questIndex + '. ') + qQuestion.find('li').text()
                );
                $(this).prev().remove();
                $(this).next().remove();
            } else

            // проверяем список на наличие
            // сопоставления или класификации
            if(true
                && $(this).find('li').length == 1
                && $(this).next().is('ul')
                && (
                    $(this).next().find('li').text().indexOf('~') > -1
                    || $(this).next().find('li').text().indexOf('->') > -1
                    || $(this).next().find('li').text().indexOf('-&gt;') > -1
                )
            ){
                var questIndex = $(this).attr('start') || 1;

                $(this).next().find('li').each(function() {
                    $(this).text(' '+ $(this).text())
                });

                $(this).next()
                    .addClass('test-question')
                    .attr(
                        'data-quest',
                        (questIndex + '. ') + $(this).find('li').text()
                    );
                $(this).remove();
            } else

            // проверяем список на наличие
            // теста определения термина
            if(true
                && $(this).find('li').length == 1
                && $(this).text().indexOf('%%') != -1
                && $(this).next().is('p')
                && $(this).next().text().indexOf('%%') != -1
            ){
                var questIndex = $(this).attr('start') || 1;

                $(this)
                    .addClass('test-question')
                    .attr(
                        'data-quest',
                        (questIndex + '. ') + $(this).find('li').text()
                    )
                    .find('li').html($(this).next().html());

                $(this).next().remove();
            }
        });

        // постобработка тестов:
        // превращаем элементы в текст
        var qOut = '';
        $output.find('.test-question').each(function(i) {
            qOut += (i ? '\n' : '') + $(this).data('quest');

            $(this).find('li').each(function() {
                qOut += '\n' + $(this).text();
            });
            qOut += '\n';
            $(this).remove();
        });

        return {
            output: $output,
            tests: qOut
        }
    },
// Пост- форматирвоание --------------------------------------------------------
    postFormat: function(input) {
        return input
            // удаление пустых строк
            .replace(/^(?:[\t ]*(?:\r?\n|\r))+/gm, '')
            // фикс одиночных кавычек
            .replace(/&quot;/gm, "\'")
            // обработка переноса строки
            .replace(/\\n/gm, '<br>')
            // превращаем ссылки в изображения
            .replace(
                /^<p>(.*\.(?:png|jpg|jpeg|gif|svg|JPG|JPEG|PNG|GIF|SVG))?\s*<\/p>/gm,
                '<img src="$1" alt="" class="inserted-img">'
            )
            // превращаем ссылки в таблице в изображения
            .replace(
                /(<td[^>]*>)\s*(.*\.(?:png|jpg|jpeg|gif|svg|JPG|JPEG|PNG|GIF|SVG))?\s*(<\/td>)/g,
                '$1<img src="$2" alt="">$3'
            )
            // удаление пробелов и переноса строки из пустых элементов
            .replace(/\<([^/]*)\>[\s|\n]+\<\//g, '<$1></')
            // перенос новых объектов на новую строку
            .replace(/<\/(.*)><([^/](.*))>/g, '</$1>\n<$2>')
    },
// Превращение текстовых элеметов в ноды ---------------------------------------
    textToDOM: function(input) {
        // проверяем является ли инпут jQuery-объектом
        // и если нет, оборачиваем
        return (input instanceof $) ?
            input : $('<div />').append(input.replace(/&nbsp;/gm, ' '));
    },
// Обработчик фреймов ----------------------------------------------------------
    replaceIframes: function($input) {
        $input.find('p').each(function() {
            if($(this).text().indexOf('IFRAME') != 0)
                return;

            var frameAttrs = $(this).text().replace(/IFRAME/g, '').trim();
            var frameSrc   = frameAttrs.split('|')[0].trim();
            var frameSize  = (
                frameAttrs.split('|') || ['','600x500']
            )[1].split(/x|х/);

            $(this).replaceWith(
                '<iframe src="'+ frameSrc +'" frameborder="0" style="'+
                    'width: '+ parseInt(frameSize[0]) +'px; '+
                    'height: '+ parseInt(frameSize[1] || frameSize[0]) +'px'+
                '" />'
            );
        });

        return $input;
    }
}
