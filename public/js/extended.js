(function ($, doc) {

    $(doc).bind('tabscreate', function (event) {
        var $target = $(event.target);
        if ($target.is('#tabs')) {
            $('> .ui-tabs-nav', $target)
                .after('<div class="error-box">')
                .after('<div class="ajax-spinner-local">')
                .nextAll('.ajax-spinner-local:first')
                .hide();
        }
    });
    $(doc).bind('accordioncreate', function (event) {
        var $this = $(event.target)
            , $panels
            , $active;
        if ($this.is('#page-context-accordion')) {
            $panels = $this.children('.ui-accordion-content');
            $active = $panels.find('li.active:first').closest('.ui-accordion-content');
            if ($active.length) {
                $this.accordion('activate', $panels.index($active))
            }
        }
    });
    function getCurrentTabPanel () {
        return $('#tabs > div.ui-tabs-panel:eq(' + ($('#tabs').tabs('option', 'selected') || 0) + ')');
    }

    function functionStringToOnClickAttribute (func) {
        var fstr = $.trim((func || '').toString() || '');
        return  $.trim(
            $.trim(
                fstr.replace(/^function\s*(onclick|anonymous|onmousedown|onmouseup)?\s*\([^)]*\)/i, '' )
            ).replace(/^{\s*(.*)\s*}$/m, '$1')
        );
    }
    function totalProhibiter (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        return false;
    }
    var commonAjaxOptionsForTabs = {
        dataType: 'html',
        global: false,
        beforeSend: function () {
            var ctp = getCurrentTabPanel()
                , ctpHeight = ctp.height()
                , ajaxSpinner = ctp.prevAll('.ajax-spinner-local:first');

            // TODO - absolute value, beeaaad
            if (40 > ctpHeight) {
                ctp.css({ height: 40 });
            }

            _.defer(function () {
                ajaxSpinner.show()
                    .css({ width: ctp.outerWidth(), height: ctp.outerHeight() })
                    .position({of: ctp});
            });

            ctp.bind('click mousedown mouseup keydown keyup keypress', totalProhibiter);
        },
        complete: function () {
            var ctp = getCurrentTabPanel()
                , ajaxSpinner = ctp.prevAll('.ajax-spinner-local:first');

            ctp
                .css('height', '')
                .unbind('click mousedown mouseup keydown keyup keypress', totalProhibiter);

            ajaxSpinner.hide();
        },
        success: function (msg) {
            getCurrentTabPanel().html(msg);
        },
        error: function () {
            // TODO error reaction!!!!!!!!!
        }
    };

    $(document).bind('tabscreate', function (event) {
        if ($(event.target).is('#tabs')) {
            $(event.target)
                .tabs('option', 'ajaxOptions', _.extend({}, commonAjaxOptionsForTabs, {
                    success: function () {}
                }))
                // TODO: use storage to retrieve selected tab
                .tabs('select', 0);
        }
    });

    window.__Unmanaged = {
        navigateInCurrentTab: function (url) {
            var context = null;
            if ($(this).is('button, input[type="button"], input[type="submit"]')) {
                this.disabled = true;
                context = this;
            }
            $.ajax(_.extend({}, commonAjaxOptionsForTabs, {
                url: url,
                error: function () {
                    if (context) { context.disabled = false; }
                }
            }));
        }
    };

// navigate to tabs with target _self inside current tab
    $(document).on('click', "a", function (event) {
        var $target = $(this)
            , origin = $(this).closest('*[data-origin]').attr('data-origin')
            , target
            , url;

        if (!$(origin ? '#'+origin : $target).closest('#tabs').length) {
            return;
        }
        if (event.isDefaultPrevented()) {
            return;
        }

        target = $.trim($target.attr('target') || '')
        url = $.trim($target.attr('href') || '')

        if (url && /^#.*?$/.test(url)) {
            return;
        }

        // TODO допилить список расширений файлов

        if (/(\.\w+)$/i.test(url)) {
            event.preventDefault();
            window.open(url);
            return;
        }

        if (!url || !target || target === '_self') {
            event.preventDefault();
        }
        if (!url || /^javascript:/i.test(url)) {
            return;
        }

        if (!target || target === '_self') {
            $.ajax(_.extend({}, commonAjaxOptionsForTabs, { url: url }));
        }
    });

    $(document)
    // Preprocess onclick events in buttons to eliminate (document|window).location.href :)
        .on('focus mouseover mousedown', '#tabs form button, #tabs form input[type="submit"]', function (event) {
            var $target = $(this)
                , re = /(document|window)\.location\.href\s?=\s?("[^"']*"|'[^"']*'|[^;\n]*)/ig
                , onclick
                , matches;

            if ($target.data('onclick-processed') === true) {
                return;
            }
            $target.data('onclick-processed', true);

            onclick = functionStringToOnClickAttribute(this.onclick);
            if (onclick) {
                matches = onclick.match(re);
                $target.data('can-navigate-outside', !!matches && !!matches.length);
                _.each(matches || [], function (match) {
                    var url = match.replace(/^(document|window)\.location\.href\s?=\s?/i, '');
                    onclick = onclick.replace(match, "__Unmanaged.navigateInCurrentTab.call(this, "+ url +")");
                });
                this.onclick = new Function(onclick);
            }
        })
        // don't trigger onSubmit with <button> or <input type="submit"> if it
        // can navigate outside with document.location.href
        .on('click', '#tabs form button, #tabs form input[type="submit"]', function (event) {
            if ($(this).is('button') || $(this).data('can-navigate-outside') === true) {
                event.preventDefault();
            }
        });

    $(document).on('submit', '#tabs form', function (event) {
        if (event.isDefaultPrevented()) {
            return;
        }
        event.preventDefault();
        $(this).ajaxSubmit(commonAjaxOptionsForTabs);
    });

})(this.jQuery, document);
