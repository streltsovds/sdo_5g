(function ($) {
    /* таймаут нужен чтобы назначить <option> 
     * select'у чтобы успешно отправлялась форма
     * без таймаута этого не просходит, 
     * скорее всего изза событий jQ
     */
    function appendOption(option, that) {
        setTimeout(function () {
            that.element.append(option);
        }, 0);

    }

    $.widget("ui.treeselect", {
        options: {
            size: 5,
            remoteUrl: null,
            selected: 0,
            itemId: null,
            width: null,
            height: null,
            multiple: false
        },
        _selectItem: function (event, value) {
            var $currentTarget = $(event.currentTarget);
            $currentTarget.toggleClass('li_select');

            /* Проверка для случая когда идет редактирование заявки
             * и нужно, чтобы изначально был выбран пункт который существует
             */
            if (value == null && typeof event === 'number' && event !== 0) {
                $('li[data-value=' + event + ']').toggleClass('li_select')
                this.options.selected = event;
                var anotherOption = '<option value="' + event + '" selected>' + event + '</option>';
                appendOption(anotherOption, this)
            }

            if (value == null || value == 0) {
                this.options.selected = null;
                this.element.html('');
            } else {
                this.options.selected = value;

                if (!this.element.attr('multiple')) {
                    this.element.find('option').remove();
                    this.ul.find('.li_select').not($currentTarget).removeClass('li_select');
                }
                if ($currentTarget.hasClass('li_select')) {
                    var option = '<option value="' + value + '" selected>' + value + '</option>';
                    this.element.append(option);
                } else {
                    this.element.find('option[value="' + value + '"]').remove();
                }
            }
        },
        _create: function () {
            var that = this;
            this._id = _.uniqueId('treeselect');
            this.element.wrap('<div class="ui-treeselect ui-widget"></div>');
            var $treeselect = this.element.parent();
            this.element.parent().prepend("<div class=\"tree-select-title\"><span>" + this.element.closest("dd").prev("dt").html() + "</span></div><div class=\"tree-select-nselect\"><ul class=\"tree-select-ul\"></ul></div>");
            this.element.closest("dd").prev("dt").remove();
            this.ul = this.element.parent().find(".tree-select-ul");
            this.ul.disableSelection();
            this.element.parent().prepend('<div class="tree-select-control"><a class="tree-select-control-home"></a><a class="tree-select-control-up"></a></div>');
            this.control = this.element.parent().find(".tree-select-control-up");

            if (this.options.width !== null) {
                $treeselect.css('width', this.options.width);
            }
            if (this.options.height !== null) {
                $treeselect.find('.tree-select-nselect')
                    .css('height', 'auto')
                    .css('max-height', this.options.height);
            }

            this._loadData(this.options.itemId);

            this.ul.delegate('li', 'dblclick', function (event) {
                event.preventDefault();

                that._selectItem($(this).data('value'));
                if ($(this).children('a').length) {
                    that._loadData($(this).data('value'));
                }
            });

            this.ul.delegate('li', 'click', function (event) {
                event.preventDefault();

                if (that.options.selected == $(this).data('value')) {
                    that._selectItem(event, null);
                } else {
                    if (!that.options.onlyLeaves || !$(this).children('a').length) {
                        that._selectItem(event, $(this).data('value'));
                        $(that.element).trigger('change');
                    }
                }
            });

            this.element.parent().delegate('.tree-select-control a', 'click', function (event) {
                event.preventDefault();

                that._loadData($(this).data('value'));
            });
        },
        destroy: function () {
            this.container.remove();
            this.ul.enableSelection();
            $.Widget.prototype.destroy.apply(this, arguments);
        },
        _loadData: function (itemId) {
            var that = this,
                value = itemId != null ? itemId : 0;

            this.ul.html('<li> ' + $.ui.treeselect.locale.loading + '</li>');
            $.ajax(that.options.remoteUrl + "/item_id/" + value, {
                dataType: 'xml',
                global: false
            }).done(function (xml) {
                var htmlLi = [];

                if (xml && xml.documentElement) {
                    htmlLi = _.map(_.toArray(xml.documentElement.childNodes), function (n) {
                        var id = n.getAttribute('id'),
                            value = n.getAttribute('value');

                        if (n.getAttribute('leaf') == 'yes') {
                            return '<li id="' + that._id + id + '" data-value="' + id + '"><span>' + value + '</span></li>';
                        } else {
                            return '<li id="' + that._id + id + '" data-value="' + id + '"><a href="' + that.options.remoteUrl + "/item_id/" + id + '">' + value + '</a></li>';
                        }
                    });
                    that.control
                        .attr('href', that.options.remoteUrl + "/item_id/" + xml.documentElement.getAttribute('owner'))
                        .data('value', xml.documentElement.getAttribute('owner'));
                }
                that.ul.html(htmlLi.join(''));
                that._selectItem(that.options.selected);
            });
        }
    });

    $.extend($.ui.treeselect, {
        locale: {
            loading: "Загрузка..."
        }
    });

})(jQuery);