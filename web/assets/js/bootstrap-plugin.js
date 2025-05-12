!function($) {

    function UTCDate() {
        return new Date(Date.UTC.apply(Date, arguments));
    }

    function UTCToday() {
        var today = new Date();
        return UTCDate(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate(), today.getUTCHours(), today.getUTCMinutes(), today.getUTCSeconds(), 0);
    }

    // Picker object

    var Datetimepicker = function(element, options) {
        var that = this;

        this.element = $(element);

        // add container for single page application
        // when page switch the datetimepicker div will be removed also.
        this.container = options.container || 'body';

        this.language = options.language || this.element.data('date-language') || "en";
        this.language = this.language in dates ? this.language : "en";
        this.isRTL = dates[this.language].rtl || false;
        this.formatType = options.formatType || this.element.data('format-type') || 'standard';
        this.format = DPGlobal.parseFormat(options.format || this.element.data('date-format') || dates[this.language].format || DPGlobal.getDefaultFormat(this.formatType, 'input'), this.formatType);
        this.isInline = false;
        this.isVisible = false;
        this.isInput = this.element.is('input');
        this.fontAwesome = options.fontAwesome || this.element.data('font-awesome') || false;

        this.bootcssVer = options.bootcssVer || (this.isInput ? (this.element.is('.form-control') ? 3 : 2) : (this.bootcssVer = this.element.is('.input-group') ? 3 : 2));

        this.component = this.element.is('.date') ? (this.bootcssVer == 3 ? this.element.find('.input-group-addon .glyphicon-th, .input-group-addon .glyphicon-time, .input-group-addon .glyphicon-calendar, .input-group-addon .glyphicon-calendar .fa-calendar .fa-clock-o').parent() : this.element.find('.add-on .icon-th, .add-on .icon-time, .add-on .icon-calendar .fa-calendar .fa-clock-o').parent()) : false;
        this.componentReset = this.element.is('.date') ? (this.bootcssVer == 3 ? this.element.find('.input-group-addon .glyphicon-remove .fa-times').parent() : this.element.find('.add-on .icon-remove .fa-times').parent()) : false;
        this.hasInput = this.component && this.element.find('input').length;
        if (this.component && this.component.length === 0) {
            this.component = false;
        }
        this.linkField = options.linkField || this.element.data('link-field') || false;
        this.linkFormat = DPGlobal.parseFormat(options.linkFormat || this.element.data('link-format') || DPGlobal.getDefaultFormat(this.formatType, 'link'), this.formatType);
        this.minuteStep = options.minuteStep || this.element.data('minute-step') || 5;
        this.pickerPosition = options.pickerPosition || this.element.data('picker-position') || 'bottom-right';
        this.showMeridian = options.showMeridian || this.element.data('show-meridian') || false;
        this.initialDate = options.initialDate || new Date();

        this.icons = {
            leftArrow: this.fontAwesome ? 'fa-arrow-left' : (this.bootcssVer === 3 ? 'glyphicon-arrow-left' : 'icon-arrow-left'),
            rightArrow: this.fontAwesome ? 'fa-arrow-right' : (this.bootcssVer === 3 ? 'glyphicon-arrow-right' : 'icon-arrow-right')
        };
        this.icontype = this.fontAwesome ? 'fa' : 'glyphicon';

        this._attachEvents();

        this.formatViewType = "datetime";
        if ('formatViewType' in options) {
            this.formatViewType = options.formatViewType;
        } else if ('formatViewType' in this.element.data()) {
            this.formatViewType = this.element.data('formatViewType');
        }

        this.minView = 0;
        if ('minView' in options) {
            this.minView = options.minView;
        } else if ('minView' in this.element.data()) {
            this.minView = this.element.data('min-view');
        }
        this.minView = DPGlobal.convertViewMode(this.minView);

        this.maxView = DPGlobal.modes.length - 1;
        if ('maxView' in options) {
            this.maxView = options.maxView;
        } else if ('maxView' in this.element.data()) {
            this.maxView = this.element.data('max-view');
        }
        this.maxView = DPGlobal.convertViewMode(this.maxView);

        this.wheelViewModeNavigation = false;
        if ('wheelViewModeNavigation' in options) {
            this.wheelViewModeNavigation = options.wheelViewModeNavigation;
        } else if ('wheelViewModeNavigation' in this.element.data()) {
            this.wheelViewModeNavigation = this.element.data('view-mode-wheel-navigation');
        }

        this.wheelViewModeNavigationInverseDirection = false;

        if ('wheelViewModeNavigationInverseDirection' in options) {
            this.wheelViewModeNavigationInverseDirection = options.wheelViewModeNavigationInverseDirection;
        } else if ('wheelViewModeNavigationInverseDirection' in this.element.data()) {
            this.wheelViewModeNavigationInverseDirection = this.element.data('view-mode-wheel-navigation-inverse-dir');
        }

        this.wheelViewModeNavigationDelay = 100;
        if ('wheelViewModeNavigationDelay' in options) {
            this.wheelViewModeNavigationDelay = options.wheelViewModeNavigationDelay;
        } else if ('wheelViewModeNavigationDelay' in this.element.data()) {
            this.wheelViewModeNavigationDelay = this.element.data('view-mode-wheel-navigation-delay');
        }

        this.startViewMode = 2;
        if ('startView' in options) {
            this.startViewMode = options.startView;
        } else if ('startView' in this.element.data()) {
            this.startViewMode = this.element.data('start-view');
        }
        this.startViewMode = DPGlobal.convertViewMode(this.startViewMode);
        this.viewMode = this.startViewMode;

        this.viewSelect = this.minView;
        if ('viewSelect' in options) {
            this.viewSelect = options.viewSelect;
        } else if ('viewSelect' in this.element.data()) {
            this.viewSelect = this.element.data('view-select');
        }
        this.viewSelect = DPGlobal.convertViewMode(this.viewSelect);

        this.forceParse = true;
        if ('forceParse' in options) {
            this.forceParse = options.forceParse;
        } else if ('dateForceParse' in this.element.data()) {
            this.forceParse = this.element.data('date-force-parse');
        }
        var template = this.bootcssVer === 3 ? DPGlobal.templateV3 : DPGlobal.template;
        while (template.indexOf('{iconType}') !== -1) {
            template = template.replace('{iconType}', this.icontype);
        }
        while (template.indexOf('{leftArrow}') !== -1) {
            template = template.replace('{leftArrow}', this.icons.leftArrow);
        }
        while (template.indexOf('{rightArrow}') !== -1) {
            template = template.replace('{rightArrow}', this.icons.rightArrow);
        }
        this.picker = $(template)
                .appendTo(this.isInline ? this.element : this.container) // 'body')
                .on({
                    click: $.proxy(this.click, this),
                    mousedown: $.proxy(this.mousedown, this)
                });

        if (this.wheelViewModeNavigation) {
            if ($.fn.mousewheel) {
                this.picker.on({mousewheel: $.proxy(this.mousewheel, this)});
            } else {
                console.log("Mouse Wheel event is not supported. Please include the jQuery Mouse Wheel plugin before enabling this option");
            }
        }

        if (this.isInline) {
            this.picker.addClass('datetimepicker-inline');
        } else {
            this.picker.addClass('datetimepicker-dropdown-' + this.pickerPosition + ' dropdown-menu');
        }
        if (this.isRTL) {
            this.picker.addClass('datetimepicker-rtl');
            var selector = this.bootcssVer === 3 ? '.prev span, .next span' : '.prev i, .next i';
            this.picker.find(selector).toggleClass(this.icons.leftArrow + ' ' + this.icons.rightArrow);
        }
        $(document).on('mousedown', function(e) {
            // Clicked outside the datetimepicker, hide it
            if ($(e.target).closest('.datetimepicker').length === 0) {
                that.hide();
            }
        });

        this.autoclose = false;
        if ('autoclose' in options) {
            this.autoclose = options.autoclose;
        } else if ('dateAutoclose' in this.element.data()) {
            this.autoclose = this.element.data('date-autoclose');
        }

        this.keyboardNavigation = true;
        if ('keyboardNavigation' in options) {
            this.keyboardNavigation = options.keyboardNavigation;
        } else if ('dateKeyboardNavigation' in this.element.data()) {
            this.keyboardNavigation = this.element.data('date-keyboard-navigation');
        }

        this.todayBtn = (options.todayBtn || this.element.data('date-today-btn') || false);
        this.todayHighlight = (options.todayHighlight || this.element.data('date-today-highlight') || false);

        this.weekStart = ((options.weekStart || this.element.data('date-weekstart') || dates[this.language].weekStart || 0) % 7);
        this.weekEnd = ((this.weekStart + 6) % 7);
        this.startDate = -Infinity;
        this.endDate = Infinity;
        this.daysOfWeekDisabled = [];
        this.setStartDate(options.startDate || this.element.data('date-startdate'));
        this.setEndDate(options.endDate || this.element.data('date-enddate'));
        this.setDaysOfWeekDisabled(options.daysOfWeekDisabled || this.element.data('date-days-of-week-disabled'));
        this.setMinutesDisabled(options.minutesDisabled || this.element.data('date-minute-disabled'));
        this.setHoursDisabled(options.hoursDisabled || this.element.data('date-hour-disabled'));
        this.fillDow();
        this.fillMonths();
        this.update();
        this.showMode();

        if (this.isInline) {
            this.show();
        }
    };

    Datetimepicker.prototype = {
        constructor: Datetimepicker,
        _events: [],
        _attachEvents: function() {
            this._detachEvents();
            if (this.isInput) { // single input
                this._events = [
                    [this.element, {
                            focus: $.proxy(this.show, this),
                            keyup: $.proxy(this.update, this),
                            keydown: $.proxy(this.keydown, this)
                        }]
                ];
            }
            else if (this.component && this.hasInput) { // component: input + button
                this._events = [
                    // For components that are not readonly, allow keyboard nav
                    [this.element.find('input'), {
                            focus: $.proxy(this.show, this),
                            keyup: $.proxy(this.update, this),
                            keydown: $.proxy(this.keydown, this)
                        }],
                    [this.component, {
                            click: $.proxy(this.show, this)
                        }]
                ];
                if (this.componentReset) {
                    this._events.push([
                        this.componentReset,
                        {click: $.proxy(this.reset, this)}
                    ]);
                }
            }
            else if (this.element.is('div')) {  // inline datetimepicker
                this.isInline = true;
            }
            else {
                this._events = [
                    [this.element, {
                            click: $.proxy(this.show, this)
                        }]
                ];
            }
            for (var i = 0, el, ev; i < this._events.length; i++) {
                el = this._events[i][0];
                ev = this._events[i][1];
                el.on(ev);
            }
        },
        _detachEvents: function() {
            for (var i = 0, el, ev; i < this._events.length; i++) {
                el = this._events[i][0];
                ev = this._events[i][1];
                el.off(ev);
            }
            this._events = [];
        },
        show: function(e) {
            this.picker.show();
            this.height = this.component ? this.component.outerHeight() : this.element.outerHeight();
            if (this.forceParse) {
                this.update();
            }
            this.place();
            $(window).on('resize', $.proxy(this.place, this));
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            this.isVisible = true;
            this.element.trigger({
                type: 'show',
                date: this.date
            });
        },
        hide: function(e) {
            if (!this.isVisible)
                return;
            if (this.isInline)
                return;
            this.picker.hide();
            $(window).off('resize', this.place);
            this.viewMode = this.startViewMode;
            this.showMode();
            if (!this.isInput) {
                $(document).off('mousedown', this.hide);
            }

            if (
                    this.forceParse &&
                    (
                            this.isInput && this.element.val() ||
                            this.hasInput && this.element.find('input').val()
                            )
                    )
                this.setValue();
            this.isVisible = false;
            this.element.trigger({
                type: 'hide',
                date: this.date
            });
        },
        remove: function() {
            this._detachEvents();
            this.picker.remove();
            delete this.picker;
            delete this.element.data().datetimepicker;
        },
        getDate: function() {
            var d = this.getUTCDate();
            return new Date(d.getTime() + (d.getTimezoneOffset() * 60000));
        },
        getUTCDate: function() {
            return this.date;
        },
        setDate: function(d) {
            this.setUTCDate(new Date(d.getTime() - (d.getTimezoneOffset() * 60000)));
        },
        setUTCDate: function(d) {
            if (d >= this.startDate && d <= this.endDate) {
                this.date = d;
                this.setValue();
                this.viewDate = this.date;
                this.fill();
            } else {
                this.element.trigger({
                    type: 'outOfRange',
                    date: d,
                    startDate: this.startDate,
                    endDate: this.endDate
                });
            }
        },
        setFormat: function(format) {
            this.format = DPGlobal.parseFormat(format, this.formatType);
            var element;
            if (this.isInput) {
                element = this.element;
            } else if (this.component) {
                element = this.element.find('input');
            }
            if (element && element.val()) {
                this.setValue();
            }
        },
        setValue: function() {
            var formatted = this.getFormattedDate();
            if (!this.isInput) {
                if (this.component) {
                    this.element.find('input').val(formatted);
                }
                this.element.data('date', formatted);
            } else {
                this.element.val(formatted);
            }
            if (this.linkField) {
                $('#' + this.linkField).val(this.getFormattedDate(this.linkFormat));
            }
        },
        getFormattedDate: function(format) {
            if (format == undefined)
                format = this.format;
            return DPGlobal.formatDate(this.date, format, this.language, this.formatType);
        },
        setStartDate: function(startDate) {
            this.startDate = startDate || -Infinity;
            if (this.startDate !== -Infinity) {
                this.startDate = DPGlobal.parseDate(this.startDate, this.format, this.language, this.formatType);
            }
            this.update();
            this.updateNavArrows();
        },
        setEndDate: function(endDate) {
            this.endDate = endDate || Infinity;
            if (this.endDate !== Infinity) {
                this.endDate = DPGlobal.parseDate(this.endDate, this.format, this.language, this.formatType);
            }
            this.update();
            this.updateNavArrows();
        },
        setDaysOfWeekDisabled: function(daysOfWeekDisabled) {
            this.daysOfWeekDisabled = daysOfWeekDisabled || [];
            if (!$.isArray(this.daysOfWeekDisabled)) {
                this.daysOfWeekDisabled = this.daysOfWeekDisabled.split(/,\s*/);
            }
            this.daysOfWeekDisabled = $.map(this.daysOfWeekDisabled, function(d) {
                return parseInt(d, 10);
            });
            this.update();
            this.updateNavArrows();
        },
        setMinutesDisabled: function(minutesDisabled) {
            this.minutesDisabled = minutesDisabled || [];
            if (!$.isArray(this.minutesDisabled)) {
                this.minutesDisabled = this.minutesDisabled.split(/,\s*/);
            }
            this.minutesDisabled = $.map(this.minutesDisabled, function(d) {
                return parseInt(d, 10);
            });
            this.update();
            this.updateNavArrows();
        },
        setHoursDisabled: function(hoursDisabled) {
            this.hoursDisabled = hoursDisabled || [];
            if (!$.isArray(this.hoursDisabled)) {
                this.hoursDisabled = this.hoursDisabled.split(/,\s*/);
            }
            this.hoursDisabled = $.map(this.hoursDisabled, function(d) {
                return parseInt(d, 10);
            });
            this.update();
            this.updateNavArrows();
        },
        place: function() {
            if (this.isInline)
                return;

            var index_highest = 0;
            $('div').each(function() {
                var index_current = parseInt($(this).css("zIndex"), 10);
                if (index_current > index_highest) {
                    index_highest = index_current;
                }
            });
            var zIndex = index_highest + 10;

            var offset, top, left, containerOffset;
            if (this.container instanceof $) {
                containerOffset = this.container.offset();
            } else {
                containerOffset = $(this.container).offset();
            }

            if (this.component) {
                offset = this.component.offset();
                left = offset.left;
                if (this.pickerPosition == 'bottom-left' || this.pickerPosition == 'top-left') {
                    left += this.component.outerWidth() - this.picker.outerWidth();
                }
            } else {
                offset = this.element.offset();
                left = offset.left;
            }

            if (left + 220 > document.body.clientWidth) {
                left = document.body.clientWidth - 220;
            }

            if (this.pickerPosition == 'top-left' || this.pickerPosition == 'top-right') {
                top = offset.top - this.picker.outerHeight();
            } else {
                top = offset.top + this.height;
            }

            top = top - containerOffset.top;
            left = left - containerOffset.left;

            this.picker.css({
                top: top,
                left: left,
                zIndex: zIndex
            });
        },
        update: function() {
            var date, fromArgs = false;
            if (arguments && arguments.length && (typeof arguments[0] === 'string' || arguments[0] instanceof Date)) {
                date = arguments[0];
                fromArgs = true;
            } else {
                date = (this.isInput ? this.element.val() : this.element.find('input').val()) || this.element.data('date') || this.initialDate;
                if (typeof date == 'string' || date instanceof String) {
                    date = date.replace(/^\s+|\s+$/g, '');
                }
            }

            if (!date) {
                date = new Date();
                fromArgs = false;
            }

            this.date = DPGlobal.parseDate(date, this.format, this.language, this.formatType);

            if (fromArgs)
                this.setValue();

            if (this.date < this.startDate) {
                this.viewDate = new Date(this.startDate);
            } else if (this.date > this.endDate) {
                this.viewDate = new Date(this.endDate);
            } else {
                this.viewDate = new Date(this.date);
            }
            this.fill();
        },
        fillDow: function() {
            var dowCnt = this.weekStart,
                    html = '<tr>';
            while (dowCnt < this.weekStart + 7) {
                html += '<th class="dow">' + dates[this.language].daysMin[(dowCnt++) % 7] + '</th>';
            }
            html += '</tr>';
            this.picker.find('.datetimepicker-days thead').append(html);
        },
        fillMonths: function() {
            var html = '',
                    i = 0;
            while (i < 12) {
                html += '<span class="month">' + dates[this.language].monthsShort[i++] + '</span>';
            }
            this.picker.find('.datetimepicker-months td').html(html);
        },
        fill: function() {
            if (this.date == null || this.viewDate == null) {
                return;
            }
            var d = new Date(this.viewDate),
                    year = d.getUTCFullYear(),
                    month = d.getUTCMonth(),
                    dayMonth = d.getUTCDate(),
                    hours = d.getUTCHours(),
                    minutes = d.getUTCMinutes(),
                    startYear = this.startDate !== -Infinity ? this.startDate.getUTCFullYear() : -Infinity,
                    startMonth = this.startDate !== -Infinity ? this.startDate.getUTCMonth() + 1 : -Infinity,
                    endYear = this.endDate !== Infinity ? this.endDate.getUTCFullYear() : Infinity,
                    endMonth = this.endDate !== Infinity ? this.endDate.getUTCMonth() + 1 : Infinity,
                    currentDate = (new UTCDate(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate())).valueOf(),
                    today = new Date();
            this.picker.find('.datetimepicker-days thead th:eq(1)')
                    .text(dates[this.language].months[month] + ' ' + year);
            if (this.formatViewType == "time") {
                var formatted = this.getFormattedDate();
                this.picker.find('.datetimepicker-hours thead th:eq(1)').text(formatted);
                this.picker.find('.datetimepicker-minutes thead th:eq(1)').text(formatted);
            } else {
                this.picker.find('.datetimepicker-hours thead th:eq(1)')
                        .text(dayMonth + ' ' + dates[this.language].months[month] + ' ' + year);
                this.picker.find('.datetimepicker-minutes thead th:eq(1)')
                        .text(dayMonth + ' ' + dates[this.language].months[month] + ' ' + year);
            }
            this.picker.find('tfoot th.today')
                    .text(dates[this.language].today)
                    .toggle(this.todayBtn !== false);
            this.updateNavArrows();
            this.fillMonths();
            /*var prevMonth = UTCDate(year, month, 0,0,0,0,0);
             prevMonth.setUTCDate(prevMonth.getDate() - (prevMonth.getUTCDay() - this.weekStart + 7)%7);*/
            var prevMonth = UTCDate(year, month - 1, 28, 0, 0, 0, 0),
                    day = DPGlobal.getDaysInMonth(prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
            prevMonth.setUTCDate(day);
            prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);
            var nextMonth = new Date(prevMonth);
            nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
            nextMonth = nextMonth.valueOf();
            var html = [];
            var clsName;
            while (prevMonth.valueOf() < nextMonth) {
                if (prevMonth.getUTCDay() == this.weekStart) {
                    html.push('<tr>');
                }
                clsName = '';
                if (prevMonth.getUTCFullYear() < year || (prevMonth.getUTCFullYear() == year && prevMonth.getUTCMonth() < month)) {
                    clsName += ' old';
                } else if (prevMonth.getUTCFullYear() > year || (prevMonth.getUTCFullYear() == year && prevMonth.getUTCMonth() > month)) {
                    clsName += ' new';
                }
                // Compare internal UTC date with local today, not UTC today
                if (this.todayHighlight &&
                        prevMonth.getUTCFullYear() == today.getFullYear() &&
                        prevMonth.getUTCMonth() == today.getMonth() &&
                        prevMonth.getUTCDate() == today.getDate()) {
                    clsName += ' today';
                }
                if (prevMonth.valueOf() == currentDate) {
                    clsName += ' active';
                }
                if ((prevMonth.valueOf() + 86400000) <= this.startDate || prevMonth.valueOf() > this.endDate ||
                        $.inArray(prevMonth.getUTCDay(), this.daysOfWeekDisabled) !== -1) {
                    clsName += ' disabled';
                }
                html.push('<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>');
                if (prevMonth.getUTCDay() == this.weekEnd) {
                    html.push('</tr>');
                }
                prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
            }
            this.picker.find('.datetimepicker-days tbody').empty().append(html.join(''));

            html = [];
            var txt = '', meridian = '', meridianOld = '';
            var hoursDisabled = this.hoursDisabled || [];
            for (var i = 0; i < 24; i++) {
                if (hoursDisabled.indexOf(i) !== -1)
                    continue;
                var actual = UTCDate(year, month, dayMonth, i);
                clsName = '';
                // We want the previous hour for the startDate
                if ((actual.valueOf() + 3600000) <= this.startDate || actual.valueOf() > this.endDate) {
                    clsName += ' disabled';
                } else if (hours == i) {
                    clsName += ' active';
                }
                if (this.showMeridian && dates[this.language].meridiem.length == 2) {
                    meridian = (i < 12 ? dates[this.language].meridiem[0] : dates[this.language].meridiem[1]);
                    if (meridian != meridianOld) {
                        if (meridianOld != '') {
                            html.push('</fieldset>');
                        }
                        html.push('<fieldset class="hour"><legend>' + meridian.toUpperCase() + '</legend>');
                    }
                    meridianOld = meridian;
                    txt = i % 12 == 0 ? i : i % 12;
                    html.push('<span class="hour' + clsName + ' hour_' + (i < 12 ? 'am' : 'pm') + '">' + txt + '</span>');
                    if (i == 23) {
                        html.push('</fieldset>');
                    }
                } else {
                    txt = i + ':00';
                    html.push('<span class="hour' + clsName + '">' + txt + '</span>');
                }
            }
            this.picker.find('.datetimepicker-hours td').html(html.join(''));

            html = [];
            txt = '', meridian = '', meridianOld = '';
            var minutesDisabled = this.minutesDisabled || [];
            for (var i = 0; i < 60; i += this.minuteStep) {
                if (minutesDisabled.indexOf(i) !== -1)
                    continue;
                var actual = UTCDate(year, month, dayMonth, hours, i, 0);
                clsName = '';
                if (actual.valueOf() < this.startDate || actual.valueOf() > this.endDate) {
                    clsName += ' disabled';
                } else if (Math.floor(minutes / this.minuteStep) == Math.floor(i / this.minuteStep)) {
                    clsName += ' active';
                }
                if (this.showMeridian && dates[this.language].meridiem.length == 2) {
                    meridian = (hours < 12 ? dates[this.language].meridiem[0] : dates[this.language].meridiem[1]);
                    if (meridian != meridianOld) {
                        if (meridianOld != '') {
                            html.push('</fieldset>');
                        }
                        html.push('<fieldset class="minute"><legend>' + meridian.toUpperCase() + '</legend>');
                    }
                    meridianOld = meridian;
                    txt = hours % 12 == 0 ? hours : hours % 12;
                    //html.push('<span class="minute'+clsName+' minute_'+(hours<12?'am':'pm')+'">'+txt+'</span>');
                    html.push('<span class="minute' + clsName + '">' + txt + ':' + (i < 10 ? '0' + i : i) + '</span>');
                    if (i == 59) {
                        html.push('</fieldset>');
                    }
                } else {
                    txt = i + ':00';
                    //html.push('<span class="hour'+clsName+'">'+txt+'</span>');
                    html.push('<span class="minute' + clsName + '">' + hours + ':' + (i < 10 ? '0' + i : i) + '</span>');
                }
            }
            this.picker.find('.datetimepicker-minutes td').html(html.join(''));

            var currentYear = this.date.getUTCFullYear();
            var months = this.picker.find('.datetimepicker-months')
                    .find('th:eq(1)')
                    .text(year)
                    .end()
                    .find('span').removeClass('active');
            if (currentYear == year) {
                // getUTCMonths() returns 0 based, and we need to select the next one
                months.eq(this.date.getUTCMonth() + 2).addClass('active');
            }
            if (year < startYear || year > endYear) {
                months.addClass('disabled');
            }
            if (year == startYear) {
                months.slice(0, startMonth + 1).addClass('disabled');
            }
            if (year == endYear) {
                months.slice(endMonth).addClass('disabled');
            }

            html = '';
            year = parseInt(year / 10, 10) * 10;
            var yearCont = this.picker.find('.datetimepicker-years')
                    .find('th:eq(1)')
                    .text(year + '-' + (year + 9))
                    .end()
                    .find('td');
            year -= 1;
            for (var i = -1; i < 11; i++) {
                html += '<span class="year' + (i == -1 || i == 10 ? ' old' : '') + (currentYear == year ? ' active' : '') + (year < startYear || year > endYear ? ' disabled' : '') + '">' + year + '</span>';
                year += 1;
            }
            yearCont.html(html);
            this.place();
        },
        updateNavArrows: function() {
            var d = new Date(this.viewDate),
                    year = d.getUTCFullYear(),
                    month = d.getUTCMonth(),
                    day = d.getUTCDate(),
                    hour = d.getUTCHours();
            switch (this.viewMode) {
                case 0:
                    if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()
                            && month <= this.startDate.getUTCMonth()
                            && day <= this.startDate.getUTCDate()
                            && hour <= this.startDate.getUTCHours()) {
                        this.picker.find('.prev').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.prev').css({visibility: 'visible'});
                    }
                    if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()
                            && month >= this.endDate.getUTCMonth()
                            && day >= this.endDate.getUTCDate()
                            && hour >= this.endDate.getUTCHours()) {
                        this.picker.find('.next').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.next').css({visibility: 'visible'});
                    }
                    break;
                case 1:
                    if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()
                            && month <= this.startDate.getUTCMonth()
                            && day <= this.startDate.getUTCDate()) {
                        this.picker.find('.prev').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.prev').css({visibility: 'visible'});
                    }
                    if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()
                            && month >= this.endDate.getUTCMonth()
                            && day >= this.endDate.getUTCDate()) {
                        this.picker.find('.next').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.next').css({visibility: 'visible'});
                    }
                    break;
                case 2:
                    if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()
                            && month <= this.startDate.getUTCMonth()) {
                        this.picker.find('.prev').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.prev').css({visibility: 'visible'});
                    }
                    if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()
                            && month >= this.endDate.getUTCMonth()) {
                        this.picker.find('.next').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.next').css({visibility: 'visible'});
                    }
                    break;
                case 3:
                case 4:
                    if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()) {
                        this.picker.find('.prev').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.prev').css({visibility: 'visible'});
                    }
                    if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()) {
                        this.picker.find('.next').css({visibility: 'hidden'});
                    } else {
                        this.picker.find('.next').css({visibility: 'visible'});
                    }
                    break;
            }
        },
        mousewheel: function(e) {

            e.preventDefault();
            e.stopPropagation();

            if (this.wheelPause) {
                return;
            }

            this.wheelPause = true;

            var originalEvent = e.originalEvent;

            var delta = originalEvent.wheelDelta;

            var mode = delta > 0 ? 1 : (delta === 0) ? 0 : -1;

            if (this.wheelViewModeNavigationInverseDirection) {
                mode = -mode;
            }

            this.showMode(mode);

            setTimeout($.proxy(function() {

                this.wheelPause = false

            }, this), this.wheelViewModeNavigationDelay);

        },
        click: function(e) {
            e.stopPropagation();
            e.preventDefault();
            var target = $(e.target).closest('span, td, th, legend');
            if (target.is('.' + this.icontype)) {
                target = $(target).parent().closest('span, td, th, legend');
            }
            if (target.length == 1) {
                if (target.is('.disabled')) {
                    this.element.trigger({
                        type: 'outOfRange',
                        date: this.viewDate,
                        startDate: this.startDate,
                        endDate: this.endDate
                    });
                    return;
                }
                switch (target[0].nodeName.toLowerCase()) {
                    case 'th':
                        switch (target[0].className) {
                            case 'switch':
                                this.showMode(1);
                                break;
                            case 'prev':
                            case 'next':
                                var dir = DPGlobal.modes[this.viewMode].navStep * (target[0].className == 'prev' ? -1 : 1);
                                switch (this.viewMode) {
                                    case 0:
                                        this.viewDate = this.moveHour(this.viewDate, dir);
                                        break;
                                    case 1:
                                        this.viewDate = this.moveDate(this.viewDate, dir);
                                        break;
                                    case 2:
                                        this.viewDate = this.moveMonth(this.viewDate, dir);
                                        break;
                                    case 3:
                                    case 4:
                                        this.viewDate = this.moveYear(this.viewDate, dir);
                                        break;
                                }
                                this.fill();
                                this.element.trigger({
                                    type: target[0].className + ':' + this.convertViewModeText(this.viewMode),
                                    date: this.viewDate,
                                    startDate: this.startDate,
                                    endDate: this.endDate
                                });
                                break;
                            case 'today':
                                var date = new Date();
                                date = UTCDate(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), 0);

                                // Respect startDate and endDate.
                                if (date < this.startDate)
                                    date = this.startDate;
                                else if (date > this.endDate)
                                    date = this.endDate;

                                this.viewMode = this.startViewMode;
                                this.showMode(0);
                                this._setDate(date);
                                this.fill();
                                if (this.autoclose) {
                                    this.hide();
                                }
                                break;
                        }
                        break;
                    case 'span':
                        if (!target.is('.disabled')) {
                            var year = this.viewDate.getUTCFullYear(),
                                    month = this.viewDate.getUTCMonth(),
                                    day = this.viewDate.getUTCDate(),
                                    hours = this.viewDate.getUTCHours(),
                                    minutes = this.viewDate.getUTCMinutes(),
                                    seconds = this.viewDate.getUTCSeconds();

                            if (target.is('.month')) {
                                this.viewDate.setUTCDate(1);
                                month = target.parent().find('span').index(target);
                                day = this.viewDate.getUTCDate();
                                this.viewDate.setUTCMonth(month);
                                this.element.trigger({
                                    type: 'changeMonth',
                                    date: this.viewDate
                                });
                                if (this.viewSelect >= 3) {
                                    this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                                }
                            } else if (target.is('.year')) {
                                this.viewDate.setUTCDate(1);
                                year = parseInt(target.text(), 10) || 0;
                                this.viewDate.setUTCFullYear(year);
                                this.element.trigger({
                                    type: 'changeYear',
                                    date: this.viewDate
                                });
                                if (this.viewSelect >= 4) {
                                    this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                                }
                            } else if (target.is('.hour')) {
                                hours = parseInt(target.text(), 10) || 0;
                                if (target.hasClass('hour_am') || target.hasClass('hour_pm')) {
                                    if (hours == 12 && target.hasClass('hour_am')) {
                                        hours = 0;
                                    } else if (hours != 12 && target.hasClass('hour_pm')) {
                                        hours += 12;
                                    }
                                }
                                this.viewDate.setUTCHours(hours);
                                this.element.trigger({
                                    type: 'changeHour',
                                    date: this.viewDate
                                });
                                if (this.viewSelect >= 1) {
                                    this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                                }
                            } else if (target.is('.minute')) {
                                minutes = parseInt(target.text().substr(target.text().indexOf(':') + 1), 10) || 0;
                                this.viewDate.setUTCMinutes(minutes);
                                this.element.trigger({
                                    type: 'changeMinute',
                                    date: this.viewDate
                                });
                                if (this.viewSelect >= 0) {
                                    this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                                }
                            }
                            if (this.viewMode != 0) {
                                var oldViewMode = this.viewMode;
                                this.showMode(-1);
                                this.fill();
                                if (oldViewMode == this.viewMode && this.autoclose) {
                                    this.hide();
                                }
                            } else {
                                this.fill();
                                if (this.autoclose) {
                                    this.hide();
                                }
                            }
                        }
                        break;
                    case 'td':
                        if (target.is('.day') && !target.is('.disabled')) {
                            var day = parseInt(target.text(), 10) || 1;
                            var year = this.viewDate.getUTCFullYear(),
                                    month = this.viewDate.getUTCMonth(),
                                    hours = this.viewDate.getUTCHours(),
                                    minutes = this.viewDate.getUTCMinutes(),
                                    seconds = this.viewDate.getUTCSeconds();
                            if (target.is('.old')) {
                                if (month === 0) {
                                    month = 11;
                                    year -= 1;
                                } else {
                                    month -= 1;
                                }
                            } else if (target.is('.new')) {
                                if (month == 11) {
                                    month = 0;
                                    year += 1;
                                } else {
                                    month += 1;
                                }
                            }
                            this.viewDate.setUTCFullYear(year);
                            this.viewDate.setUTCMonth(month, day);
                            this.element.trigger({
                                type: 'changeDay',
                                date: this.viewDate
                            });
                            if (this.viewSelect >= 2) {
                                this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
                            }
                        }
                        var oldViewMode = this.viewMode;
                        this.showMode(-1);
                        this.fill();
                        if (oldViewMode == this.viewMode && this.autoclose) {
                            this.hide();
                        }
                        break;
                }
            }
        },
        _setDate: function(date, which) {
            if (!which || which == 'date')
                this.date = date;
            if (!which || which == 'view')
                this.viewDate = date;
            this.fill();
            this.setValue();
            var element;
            if (this.isInput) {
                element = this.element;
            } else if (this.component) {
                element = this.element.find('input');
            }
            if (element) {
                element.change();
                if (this.autoclose && (!which || which == 'date')) {
                    //this.hide();
                }
            }
            this.element.trigger({
                type: 'changeDate',
                date: this.date
            });
        },
        moveMinute: function(date, dir) {
            if (!dir)
                return date;
            var new_date = new Date(date.valueOf());
            //dir = dir > 0 ? 1 : -1;
            new_date.setUTCMinutes(new_date.getUTCMinutes() + (dir * this.minuteStep));
            return new_date;
        },
        moveHour: function(date, dir) {
            if (!dir)
                return date;
            var new_date = new Date(date.valueOf());
            //dir = dir > 0 ? 1 : -1;
            new_date.setUTCHours(new_date.getUTCHours() + dir);
            return new_date;
        },
        moveDate: function(date, dir) {
            if (!dir)
                return date;
            var new_date = new Date(date.valueOf());
            //dir = dir > 0 ? 1 : -1;
            new_date.setUTCDate(new_date.getUTCDate() + dir);
            return new_date;
        },
        moveMonth: function(date, dir) {
            if (!dir)
                return date;
            var new_date = new Date(date.valueOf()),
                    day = new_date.getUTCDate(),
                    month = new_date.getUTCMonth(),
                    mag = Math.abs(dir),
                    new_month, test;
            dir = dir > 0 ? 1 : -1;
            if (mag == 1) {
                test = dir == -1
                        // If going back one month, make sure month is not current month
                        // (eg, Mar 31 -> Feb 31 == Feb 28, not Mar 02)
                        ? function() {
                            return new_date.getUTCMonth() == month;
                        }
                // If going forward one month, make sure month is as expected
                // (eg, Jan 31 -> Feb 31 == Feb 28, not Mar 02)
                : function() {
                    return new_date.getUTCMonth() != new_month;
                };
                new_month = month + dir;
                new_date.setUTCMonth(new_month);
                // Dec -> Jan (12) or Jan -> Dec (-1) -- limit expected date to 0-11
                if (new_month < 0 || new_month > 11)
                    new_month = (new_month + 12) % 12;
            } else {
                // For magnitudes >1, move one month at a time...
                for (var i = 0; i < mag; i++)
                    // ...which might decrease the day (eg, Jan 31 to Feb 28, etc)...
                    new_date = this.moveMonth(new_date, dir);
                // ...then reset the day, keeping it in the new month
                new_month = new_date.getUTCMonth();
                new_date.setUTCDate(day);
                test = function() {
                    return new_month != new_date.getUTCMonth();
                };
            }
            // Common date-resetting loop -- if date is beyond end of month, make it
            // end of month
            while (test()) {
                new_date.setUTCDate(--day);
                new_date.setUTCMonth(new_month);
            }
            return new_date;
        },
        moveYear: function(date, dir) {
            return this.moveMonth(date, dir * 12);
        },
        dateWithinRange: function(date) {
            return date >= this.startDate && date <= this.endDate;
        },
        keydown: function(e) {
            if (this.picker.is(':not(:visible)')) {
                if (e.keyCode == 27) // allow escape to hide and re-show picker
                    this.show();
                return;
            }
            var dateChanged = false,
                    dir, day, month,
                    newDate, newViewDate;
            switch (e.keyCode) {
                case 27: // escape
                    this.hide();
                    e.preventDefault();
                    break;
                case 37: // left
                case 39: // right
                    if (!this.keyboardNavigation)
                        break;
                    dir = e.keyCode == 37 ? -1 : 1;
                    viewMode = this.viewMode;
                    if (e.ctrlKey) {
                        viewMode += 2;
                    } else if (e.shiftKey) {
                        viewMode += 1;
                    }
                    if (viewMode == 4) {
                        newDate = this.moveYear(this.date, dir);
                        newViewDate = this.moveYear(this.viewDate, dir);
                    } else if (viewMode == 3) {
                        newDate = this.moveMonth(this.date, dir);
                        newViewDate = this.moveMonth(this.viewDate, dir);
                    } else if (viewMode == 2) {
                        newDate = this.moveDate(this.date, dir);
                        newViewDate = this.moveDate(this.viewDate, dir);
                    } else if (viewMode == 1) {
                        newDate = this.moveHour(this.date, dir);
                        newViewDate = this.moveHour(this.viewDate, dir);
                    } else if (viewMode == 0) {
                        newDate = this.moveMinute(this.date, dir);
                        newViewDate = this.moveMinute(this.viewDate, dir);
                    }
                    if (this.dateWithinRange(newDate)) {
                        this.date = newDate;
                        this.viewDate = newViewDate;
                        this.setValue();
                        this.update();
                        e.preventDefault();
                        dateChanged = true;
                    }
                    break;
                case 38: // up
                case 40: // down
                    if (!this.keyboardNavigation)
                        break;
                    dir = e.keyCode == 38 ? -1 : 1;
                    viewMode = this.viewMode;
                    if (e.ctrlKey) {
                        viewMode += 2;
                    } else if (e.shiftKey) {
                        viewMode += 1;
                    }
                    if (viewMode == 4) {
                        newDate = this.moveYear(this.date, dir);
                        newViewDate = this.moveYear(this.viewDate, dir);
                    } else if (viewMode == 3) {
                        newDate = this.moveMonth(this.date, dir);
                        newViewDate = this.moveMonth(this.viewDate, dir);
                    } else if (viewMode == 2) {
                        newDate = this.moveDate(this.date, dir * 7);
                        newViewDate = this.moveDate(this.viewDate, dir * 7);
                    } else if (viewMode == 1) {
                        if (this.showMeridian) {
                            newDate = this.moveHour(this.date, dir * 6);
                            newViewDate = this.moveHour(this.viewDate, dir * 6);
                        } else {
                            newDate = this.moveHour(this.date, dir * 4);
                            newViewDate = this.moveHour(this.viewDate, dir * 4);
                        }
                    } else if (viewMode == 0) {
                        newDate = this.moveMinute(this.date, dir * 4);
                        newViewDate = this.moveMinute(this.viewDate, dir * 4);
                    }
                    if (this.dateWithinRange(newDate)) {
                        this.date = newDate;
                        this.viewDate = newViewDate;
                        this.setValue();
                        this.update();
                        e.preventDefault();
                        dateChanged = true;
                    }
                    break;
                case 13: // enter
                    if (this.viewMode != 0) {
                        var oldViewMode = this.viewMode;
                        this.showMode(-1);
                        this.fill();
                        if (oldViewMode == this.viewMode && this.autoclose) {
                            this.hide();
                        }
                    } else {
                        this.fill();
                        if (this.autoclose) {
                            this.hide();
                        }
                    }
                    e.preventDefault();
                    break;
                case 9: // tab
                    this.hide();
                    break;
            }
            if (dateChanged) {
                var element;
                if (this.isInput) {
                    element = this.element;
                } else if (this.component) {
                    element = this.element.find('input');
                }
                if (element) {
                    element.change();
                }
                this.element.trigger({
                    type: 'changeDate',
                    date: this.date
                });
            }
        },
        showMode: function(dir) {
            if (dir) {
                var newViewMode = Math.max(0, Math.min(DPGlobal.modes.length - 1, this.viewMode + dir));
                if (newViewMode >= this.minView && newViewMode <= this.maxView) {
                    this.element.trigger({
                        type: 'changeMode',
                        date: this.viewDate,
                        oldViewMode: this.viewMode,
                        newViewMode: newViewMode
                    });

                    this.viewMode = newViewMode;
                }
            }
            /*
             vitalets: fixing bug of very special conditions:
             jquery 1.7.1 + webkit + show inline datetimepicker in bootstrap popover.
             Method show() does not set display css correctly and datetimepicker is not shown.
             Changed to .css('display', 'block') solve the problem.
             See https://github.com/vitalets/x-editable/issues/37
             
             In jquery 1.7.2+ everything works fine.
             */
            //this.picker.find('>div').hide().filter('.datetimepicker-'+DPGlobal.modes[this.viewMode].clsName).show();
            this.picker.find('>div').hide().filter('.datetimepicker-' + DPGlobal.modes[this.viewMode].clsName).css('display', 'block');
            this.updateNavArrows();
        },
        reset: function(e) {
            this._setDate(null, 'date');
        },
        convertViewModeText: function(viewMode) {
            switch (viewMode) {
                case 4:
                    return 'decade';
                case 3:
                    return 'year';
                case 2:
                    return 'month';
                case 1:
                    return 'day';
                case 0:
                    return 'hour';
            }
        }
    };

    $.fn.datetimepicker = function(option) {
        var args = Array.apply(null, arguments);
        args.shift();
        var internal_return;
        this.each(function() {
            var $this = $(this),
                    data = $this.data('datetimepicker'),
                    options = typeof option == 'object' && option;
            if (!data) {
                $this.data('datetimepicker', (data = new Datetimepicker(this, $.extend({}, $.fn.datetimepicker.defaults, options))));
            }
            if (typeof option == 'string' && typeof data[option] == 'function') {
                internal_return = data[option].apply(data, args);
                if (internal_return !== undefined) {
                    return false;
                }
            }
        });
        if (internal_return !== undefined)
            return internal_return;
        else
            return this;
    };

    $.fn.datetimepicker.defaults = {};
    $.fn.datetimepicker.Constructor = Datetimepicker;
    var dates = $.fn.datetimepicker.dates = {
        ar: {
            days: ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"],
            daysShort: ["أحد", "اثنين", "ثلاثاء", "أربعاء", "خميس", "جمعة", "سبت", "أحد"],
            daysMin: ["أح", "إث", "ث", "أر", "خ", "ج", "س", "أح"],
            months: ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
            monthsShort: ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
            today: "هذا اليوم",
            suffix: [],
            meridiem: [],
            rtl: true
        },
        bg: {
            days: ["Неделя", "Понеделник", "Вторник", "Сряда", "Четвъртък", "Петък", "Събота", "Неделя"],
            daysShort: ["Нед", "Пон", "Вто", "Сря", "Чет", "Пет", "Съб", "Нед"],
            daysMin: ["Н", "П", "В", "С", "Ч", "П", "С", "Н"],
            months: ["Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември"],
            monthsShort: ["Ян", "Фев", "Мар", "Апр", "Май", "Юни", "Юли", "Авг", "Сеп", "Окт", "Ное", "Дек"],
            today: "днес",
            suffix: [],
            meridiem: []
        },
        ca: {
            days: ["Diumenge", "Dilluns", "Dimarts", "Dimecres", "Dijous", "Divendres", "Dissabte", "Diumenge"],
            daysShort: ["Diu", "Dil", "Dmt", "Dmc", "Dij", "Div", "Dis", "Diu"],
            daysMin: ["dg", "dl", "dt", "dc", "dj", "dv", "ds", "dg"],
            months: ["Gener", "Febrer", "Març", "Abril", "Maig", "Juny", "Juliol", "Agost", "Setembre", "Octubre", "Novembre", "Desembre"],
            monthsShort: ["Gen", "Feb", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Oct", "Nov", "Des"],
            today: "Avui",
            suffix: [],
            meridiem: []
        },
        cs: {
            days: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"],
            daysShort: ["Ned", "Pon", "Úte", "Stř", "Čtv", "Pát", "Sob", "Ned"],
            daysMin: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
            months: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
            monthsShort: ["Led", "Úno", "Bře", "Dub", "Kvě", "Čer", "Čnc", "Srp", "Zář", "Říj", "Lis", "Pro"],
            today: "Dnes",
            suffix: [],
            meridiem: [],
            weekStart: 1,
            format: "dd.mm.yyyy"
        },
        da: {
            days: ["Søndag", "Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag", "Søndag"],
            daysShort: ["Søn", "Man", "Tir", "Ons", "Tor", "Fre", "Lør", "Søn"],
            daysMin: ["Sø", "Ma", "Ti", "On", "To", "Fr", "Lø", "Sø"],
            months: ["Januar", "Februar", "Marts", "April", "Maj", "Juni", "Juli", "August", "September", "Oktober", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"],
            today: "I Dag",
            suffix: [],
            meridiem: []
        },
        de: {
            days: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"],
            daysShort: ["Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam", "Son"],
            daysMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"],
            months: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
            monthsShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
            today: "Heute",
            suffix: [],
            meridiem: [],
            weekStart: 1,
            format: "dd.mm.yyyy"
        },
        ee: {
            days: ["Pühapäev", "Esmaspäev", "Teisipäev", "Kolmapäev", "Neljapäev", "Reede", "Laupäev", "Pühapäev"],
            daysShort: ["P", "E", "T", "K", "N", "R", "L", "P"],
            daysMin: ["P", "E", "T", "K", "N", "R", "L", "P"],
            months: ["Jaanuar", "Veebruar", "Märts", "Aprill", "Mai", "Juuni", "Juuli", "August", "September", "Oktoober", "November", "Detsember"],
            monthsShort: ["Jaan", "Veebr", "Märts", "Apr", "Mai", "Juuni", "Juuli", "Aug", "Sept", "Okt", "Nov", "Dets"],
            today: "Täna",
            suffix: [],
            meridiem: [],
            weekStart: 1,
            format: "dd.mm.yyyy hh:ii"
        },
        el: {
            days: ["Κυριακή", "Δευτέρα", "Τρίτη", "Τετάρτη", "Πέμπτη", "Παρασκευή", "Σάββατο", "Κυριακή"],
            daysShort: ["Κυρ", "Δευ", "Τρι", "Τετ", "Πεμ", "Παρ", "Σαβ", "Κυρ"],
            daysMin: ["Κυ", "Δε", "Τρ", "Τε", "Πε", "Πα", "Σα", "Κυ"],
            months: ["Ιανουάριος", "Φεβρουάριος", "Μάρτιος", "Απρίλιος", "Μάιος", "Ιούνιος", "Ιούλιος", "Αύγουστος", "Σεπτέμβριος", "Οκτώβριος", "Νοέμβριος", "Δεκέμβριος"],
            monthsShort: ["Ιαν", "Φεβ", "Μαρ", "Απρ", "Μάι", "Ιουν", "Ιουλ", "Αυγ", "Σεπ", "Οκτ", "Νοε", "Δεκ"],
            today: "Σήμερα",
            suffix: [],
            meridiem: []
        },
        en: {
            days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
            months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            meridiem: ["am", "pm"],
            suffix: ["st", "nd", "rd", "th"],
            today: "Today"
        },
        es: {
            days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
            daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
            daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
            months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
            today: "Hoy",
            suffix: [],
            meridiem: []
        },
        fi: {
            days: ["sunnuntai", "maanantai", "tiistai", "keskiviikko", "torstai", "perjantai", "lauantai", "sunnuntai"],
            daysShort: ["sun", "maa", "tii", "kes", "tor", "per", "lau", "sun"],
            daysMin: ["su", "ma", "ti", "ke", "to", "pe", "la", "su"],
            months: ["tammikuu", "helmikuu", "maaliskuu", "huhtikuu", "toukokuu", "kesäkuu", "heinäkuu", "elokuu", "syyskuu", "lokakuu", "marraskuu", "joulukuu"],
            monthsShort: ["tam", "hel", "maa", "huh", "tou", "kes", "hei", "elo", "syy", "lok", "mar", "jou"],
            today: "tänään",
            suffix: [],
            meridiem: []
        },
        fr: {
            days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"],
            daysShort: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
            daysMin: ["D", "L", "Ma", "Me", "J", "V", "S", "D"],
            months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
            monthsShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jui", "Jul", "Aou", "Sep", "Oct", "Nov", "Dec"],
            today: "Aujourd'hui",
            suffix: [],
            meridiem: ["am", "pm"],
            weekStart: 1,
            format: "dd/mm/yyyy"
        },
        he: {
            days: ["ראשון", "שני", "שלישי", "רביעי", "חמישי", "שישי", "שבת", "ראשון"],
            daysShort: ["א", "ב", "ג", "ד", "ה", "ו", "ש", "א"],
            daysMin: ["א", "ב", "ג", "ד", "ה", "ו", "ש", "א"],
            months: ["ינואר", "פברואר", "מרץ", "אפריל", "מאי", "יוני", "יולי", "אוגוסט", "ספטמבר", "אוקטובר", "נובמבר", "דצמבר"],
            monthsShort: ["ינו", "פבר", "מרץ", "אפר", "מאי", "יונ", "יול", "אוג", "ספט", "אוק", "נוב", "דצמ"],
            today: "היום",
            suffix: [],
            meridiem: [],
            rtl: true
        },
        hr: {
            days: ["Nedjelja", "Ponedjelja", "Utorak", "Srijeda", "Četrtak", "Petak", "Subota", "Nedjelja"],
            daysShort: ["Ned", "Pon", "Uto", "Srr", "Čet", "Pet", "Sub", "Ned"],
            daysMin: ["Ne", "Po", "Ut", "Sr", "Če", "Pe", "Su", "Ne"],
            months: ["Siječanj", "Veljača", "Ožujak", "Travanj", "Svibanj", "Lipanj", "Srpanj", "Kolovoz", "Rujan", "Listopad", "Studeni", "Prosinac"],
            monthsShort: ["Sije", "Velj", "Ožu", "Tra", "Svi", "Lip", "Jul", "Kol", "Ruj", "Lis", "Stu", "Pro"],
            today: "Danas",
            suffix: [],
            meridiem: []
        },
        hu: {
            days: ["Vasárnap", "Hétfő", "Kedd", "Szerda", "Csütörtök", "Péntek", "Szombat", "Vasárnap"],
            daysShort: ["Vas", "Hét", "Ked", "Sze", "Csü", "Pén", "Szo", "Vas"],
            daysMin: ["V", "H", "K", "Sze", "Cs", "P", "Szo", "V"],
            months: ["Január", "Február", "Március", "Április", "Május", "Június", "Július", "Augusztus", "Szeptember", "Október", "November", "December"],
            monthsShort: ["Jan", "Feb", "Már", "Ápr", "Máj", "Jún", "Júl", "Aug", "Sze", "Okt", "Nov", "Dec"],
            today: "Ma",
            suffix: [],
            meridiem: [],
            weekStart: 1
        },
        id: {
            days: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"],
            daysShort: ["Mgu", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Mgu"],
            daysMin: ["Mg", "Sn", "Sl", "Ra", "Ka", "Ju", "Sa", "Mg"],
            months: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
            suffix: [],
            meridiem: []
        },
        is: {
            days: ["Sunnudagur", "Mánudagur", "Þriðjudagur", "Miðvikudagur", "Fimmtudagur", "Föstudagur", "Laugardagur", "Sunnudagur"],
            daysShort: ["Sun", "Mán", "Þri", "Mið", "Fim", "Fös", "Lau", "Sun"],
            daysMin: ["Su", "Má", "Þr", "Mi", "Fi", "Fö", "La", "Su"],
            months: ["Janúar", "Febrúar", "Mars", "Apríl", "Maí", "Júní", "Júlí", "Ágúst", "September", "Október", "Nóvember", "Desember"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Maí", "Jún", "Júl", "Ágú", "Sep", "Okt", "Nóv", "Des"],
            today: "Í Dag",
            suffix: [],
            meridiem: []
        },
        it: {
            days: ["Domenica", "Lunedi", "Martedi", "Mercoledi", "Giovedi", "Venerdi", "Sabato", "Domenica"],
            daysShort: ["Dom", "Lun", "Mar", "Mer", "Gio", "Ven", "Sab", "Dom"],
            daysMin: ["Do", "Lu", "Ma", "Me", "Gi", "Ve", "Sa", "Do"],
            months: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
            monthsShort: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
            today: "Oggi",
            suffix: [],
            meridiem: [],
            weekStart: 1,
            format: "dd/mm/yyyy hh:ii:ss"
        },
        ja: {
            days: ["日曜", "月曜", "火曜", "水曜", "木曜", "金曜", "土曜", "日曜"],
            daysShort: ["日", "月", "火", "水", "木", "金", "土", "日"],
            daysMin: ["日", "月", "火", "水", "木", "金", "土", "日"],
            months: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
            monthsShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
            today: "今日",
            suffix: [],
            meridiem: []
        },
        ko: {
            days: ["일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일", "일요일"],
            daysShort: ["일", "월", "화", "수", "목", "금", "토", "일"],
            daysMin: ["일", "월", "화", "수", "목", "금", "토", "일"],
            months: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
            monthsShort: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
            suffix: [],
            meridiem: ["오전", "오후"],
            today: "오늘",
        },
        lt: {
            days: ["Sekmadienis", "Pirmadienis", "Antradienis", "Trečiadienis", "Ketvirtadienis", "Penktadienis", "Šeštadienis", "Sekmadienis"],
            daysShort: ["S", "Pr", "A", "T", "K", "Pn", "Š", "S"],
            daysMin: ["Sk", "Pr", "An", "Tr", "Ke", "Pn", "Št", "Sk"],
            months: ["Sausis", "Vasaris", "Kovas", "Balandis", "Gegužė", "Birželis", "Liepa", "Rugpjūtis", "Rugsėjis", "Spalis", "Lapkritis", "Gruodis"],
            monthsShort: ["Sau", "Vas", "Kov", "Bal", "Geg", "Bir", "Lie", "Rugp", "Rugs", "Spa", "Lap", "Gru"],
            today: "Šiandien",
            suffix: [],
            meridiem: [],
            weekStart: 1
        },
        lv: {
            days: ["Svētdiena", "Pirmdiena", "Otrdiena", "Trešdiena", "Ceturtdiena", "Piektdiena", "Sestdiena", "Svētdiena"],
            daysShort: ["Sv", "P", "O", "T", "C", "Pk", "S", "Sv"],
            daysMin: ["Sv", "Pr", "Ot", "Tr", "Ce", "Pk", "St", "Sv"],
            months: ["Janvāris", "Februāris", "Marts", "Aprīlis", "Maijs", "Jūnijs", "Jūlijs", "Augusts", "Septembris", "Oktobris", "Novembris", "Decembris"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Mai", "Jūn", "Jūl", "Aug", "Sep", "Okt", "Nov", "Dec."],
            today: "Šodien",
            suffix: [],
            meridiem: [],
            weekStart: 1
        },
        ms: {
            days: ["Ahad", "Isnin", "Selasa", "Rabu", "Khamis", "Jumaat", "Sabtu", "Ahad"],
            daysShort: ["Aha", "Isn", "Sel", "Rab", "Kha", "Jum", "Sab", "Aha"],
            daysMin: ["Ah", "Is", "Se", "Ra", "Kh", "Ju", "Sa", "Ah"],
            months: ["Januari", "Februari", "Mac", "April", "Mei", "Jun", "Julai", "Ogos", "September", "Oktober", "November", "Disember"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ogo", "Sep", "Okt", "Nov", "Dis"],
            today: "Hari Ini",
            suffix: [],
            meridiem: []
        },
        nb: {
            days: ["Søndag", "Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag", "Søndag"],
            daysShort: ["Søn", "Man", "Tir", "Ons", "Tor", "Fre", "Lør", "Søn"],
            daysMin: ["Sø", "Ma", "Ti", "On", "To", "Fr", "Lø", "Sø"],
            months: ["Januar", "Februar", "Mars", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Desember"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Des"],
            today: "I Dag",
            suffix: [],
            meridiem: []
        },
        nl: {
            days: ["Zondag", "Maandag", "Dinsdag", "Woensdag", "Donderdag", "Vrijdag", "Zaterdag", "Zondag"],
            daysShort: ["Zo", "Ma", "Di", "Wo", "Do", "Vr", "Za", "Zo"],
            daysMin: ["Zo", "Ma", "Di", "Wo", "Do", "Vr", "Za", "Zo"],
            months: ["Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mrt", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"],
            today: "Vandaag",
            suffix: [],
            meridiem: []
        },
        no: {
            days: ["Søndag", "Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag", "Søndag"],
            daysShort: ["Søn", "Man", "Tir", "Ons", "Tor", "Fre", "Lør", "Søn"],
            daysMin: ["Sø", "Ma", "Ti", "On", "To", "Fr", "Lø", "Sø"],
            months: ["Januar", "Februar", "Mars", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Desember"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Des"],
            today: "I Dag",
            suffix: [],
            meridiem: []
        },
        pl: {
            days: ["Niedziela", "Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek", "Sobota", "Niedziela"],
            daysShort: ["Nie", "Pn", "Wt", "Śr", "Czw", "Pt", "So", "Nie"],
            daysMin: ["N", "Pn", "Wt", "Śr", "Cz", "Pt", "So", "N"],
            months: ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"],
            monthsShort: ["Sty", "Lu", "Mar", "Kw", "Maj", "Cze", "Lip", "Sie", "Wrz", "Pa", "Lis", "Gru"],
            today: "Dzisiaj",
            suffix: [],
            meridiem: [],
            weekStart: 1
        },
        pt_br: {format: 'dd/mm/yyyy',
            days: ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", "Domingo"],
            daysShort: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb", "Dom"],
            daysMin: ["Do", "Se", "Te", "Qu", "Qu", "Se", "Sa", "Do"],
            months: ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
            monthsShort: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
            today: "Hoje",
            suffix: [],
            meridiem: []
        },
        pt: {
            days: ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", "Domingo"],
            daysShort: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb", "Dom"],
            daysMin: ["Do", "Se", "Te", "Qu", "Qu", "Se", "Sa", "Do"],
            months: ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
            monthsShort: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
            suffix: [],
            meridiem: ["am", "pm"],
            today: "Hoje"
        },
        ro: {
            days: ["Duminică", "Luni", "Marţi", "Miercuri", "Joi", "Vineri", "Sâmbătă", "Duminică"],
            daysShort: ["Dum", "Lun", "Mar", "Mie", "Joi", "Vin", "Sâm", "Dum"],
            daysMin: ["Du", "Lu", "Ma", "Mi", "Jo", "Vi", "Sâ", "Du"],
            months: ["Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"],
            monthsShort: ["Ian", "Feb", "Mar", "Apr", "Mai", "Iun", "Iul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            today: "Astăzi",
            suffix: [],
            meridiem: [],
            weekStart: 1
        },
        rs: {
            days: ["Недеља", "Понедељак", "Уторак", "Среда", "Четвртак", "Петак", "Субота", "Недеља"],
            daysShort: ["Нед", "Пон", "Уто", "Сре", "Чет", "Пет", "Суб", "Нед"],
            daysMin: ["Н", "По", "У", "Ср", "Ч", "Пе", "Су", "Н"],
            months: ["Јануар", "Фебруар", "Март", "Април", "Мај", "Јун", "Јул", "Август", "Септембар", "Октобар", "Новембар", "Децембар"],
            monthsShort: ["Јан", "Феб", "Мар", "Апр", "Мај", "Јун", "Јул", "Авг", "Сеп", "Окт", "Нов", "Дец"],
            today: "Данас",
            suffix: [],
            meridiem: []
        },
        ru: {
            days: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье"],
            daysShort: ["Вск", "Пнд", "Втр", "Срд", "Чтв", "Птн", "Суб", "Вск"],
            daysMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"],
            months: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            monthsShort: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
            today: "Сегодня",
            suffix: [],
            meridiem: []
        },
        sk: {
            days: ["Nedeľa", "Pondelok", "Utorok", "Streda", "Štvrtok", "Piatok", "Sobota", "Nedeľa"],
            daysShort: ["Ned", "Pon", "Uto", "Str", "Štv", "Pia", "Sob", "Ned"],
            daysMin: ["Ne", "Po", "Ut", "St", "Št", "Pia", "So", "Ne"],
            months: ["Január", "Február", "Marec", "Apríl", "Máj", "Jún", "Júl", "August", "September", "Október", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Máj", "Jún", "Júl", "Aug", "Sep", "Okt", "Nov", "Dec"],
            today: "Dnes",
            suffix: [],
            meridiem: []
        },
        sl: {
            days: ["Nedelja", "Ponedeljek", "Torek", "Sreda", "Četrtek", "Petek", "Sobota", "Nedelja"],
            daysShort: ["Ned", "Pon", "Tor", "Sre", "Čet", "Pet", "Sob", "Ned"],
            daysMin: ["Ne", "Po", "To", "Sr", "Če", "Pe", "So", "Ne"],
            months: ["Januar", "Februar", "Marec", "April", "Maj", "Junij", "Julij", "Avgust", "September", "Oktober", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Avg", "Sep", "Okt", "Nov", "Dec"],
            today: "Danes",
            suffix: [],
            meridiem: []
        },
        sv: {
            days: ["Söndag", "Måndag", "Tisdag", "Onsdag", "Torsdag", "Fredag", "Lördag", "Söndag"],
            daysShort: ["Sön", "Mån", "Tis", "Ons", "Tor", "Fre", "Lör", "Sön"],
            daysMin: ["Sö", "Må", "Ti", "On", "To", "Fr", "Lö", "Sö"],
            months: ["Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"],
            today: "I Dag",
            suffix: [],
            meridiem: []
        },
        sw: {
            days: ["Jumapili", "Jumatatu", "Jumanne", "Jumatano", "Alhamisi", "Ijumaa", "Jumamosi", "Jumapili"],
            daysShort: ["J2", "J3", "J4", "J5", "Alh", "Ij", "J1", "J2"],
            daysMin: ["2", "3", "4", "5", "A", "I", "1", "2"],
            months: ["Januari", "Februari", "Machi", "Aprili", "Mei", "Juni", "Julai", "Agosti", "Septemba", "Oktoba", "Novemba", "Desemba"],
            monthsShort: ["Jan", "Feb", "Mac", "Apr", "Mei", "Jun", "Jul", "Ago", "Sep", "Okt", "Nov", "Des"],
            today: "Leo",
            suffix: [],
            meridiem: []
        },
        th: {
            days: ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัส", "ศุกร์", "เสาร์", "อาทิตย์"],
            daysShort: ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส", "อา"],
            daysMin: ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส", "อา"],
            months: ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"],
            monthsShort: ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."],
            today: "วันนี้",
            suffix: [],
            meridiem: []
        },
        tr: {
            days: ["Pazar", "Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar"],
            daysShort: ["Pz", "Pzt", "Sal", "Çrş", "Prş", "Cu", "Cts", "Pz"],
            daysMin: ["Pz", "Pzt", "Sa", "Çr", "Pr", "Cu", "Ct", "Pz"],
            months: ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"],
            monthsShort: ["Oca", "Şub", "Mar", "Nis", "May", "Haz", "Tem", "Ağu", "Eyl", "Eki", "Kas", "Ara"],
            today: "Bugün",
            suffix: [],
            meridiem: []
        },
        ua: {
            days: ["Неділя", "Понеділок", "Вівторок", "Середа", "Четверг", "П'ятниця", "Субота", "Неділя"],
            daysShort: ["Нед", "Пнд", "Втр", "Срд", "Чтв", "Птн", "Суб", "Нед"],
            daysMin: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Нд"],
            months: ["Cічень", "Лютий", "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень"],
            monthsShort: ["Січ", "Лют", "Бер", "Квт", "Трв", "Чер", "Лип", "Сер", "Вер", "Жов", "Лис", "Грд"],
            today: "Сьогодні",
            weekStart: 1,
            meridiem: []
        },
        uk: {
            days: ["Неділя", "Понеділок", "Вівторок", "Середа", "Четвер", "П'ятниця", "Субота", "Неділя"],
            daysShort: ["Нед", "Пнд", "Втр", "Срд", "Чтв", "Птн", "Суб", "Нед"],
            daysMin: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Нд"],
            months: ["Січень", "Лютий", "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень"],
            monthsShort: ["Січ", "Лют", "Бер", "Кві", "Тра", "Чер", "Лип", "Сер", "Вер", "Жов", "Лис", "Гру"],
            today: "Сьогодні",
            suffix: [],
            meridiem: []
        },
        zh_cn: {
            days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
            daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
            daysMin: ["日", "一", "二", "三", "四", "五", "六", "日"],
            months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            today: "今日",
            suffix: [],
            meridiem: ["上午", "下午"],
            weekStart: 1,
            format: "yyyy-mm-dd hh:ii"
        },
        zh_tw: {
            days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
            daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
            daysMin: ["日", "一", "二", "三", "四", "五", "六", "日"],
            months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            today: "今天",
            suffix: [],
            meridiem: ["上午", "下午"]
        }
    };

    var DPGlobal = {
        modes: [
            {
                clsName: 'minutes',
                navFnc: 'Hours',
                navStep: 1
            },
            {
                clsName: 'hours',
                navFnc: 'Date',
                navStep: 1
            },
            {
                clsName: 'days',
                navFnc: 'Month',
                navStep: 1
            },
            {
                clsName: 'months',
                navFnc: 'FullYear',
                navStep: 1
            },
            {
                clsName: 'years',
                navFnc: 'FullYear',
                navStep: 10
            }
        ],
        isLeapYear: function(year) {
            return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
        },
        getDaysInMonth: function(year, month) {
            return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
        },
        getDefaultFormat: function(type, field) {
            if (type == "standard") {
                if (field == 'input')
                    return 'yyyy-mm-dd hh:ii';
                else
                    return 'yyyy-mm-dd hh:ii:ss';
            } else if (type == "php") {
                if (field == 'input')
                    return 'Y-m-d H:i';
                else
                    return 'Y-m-d H:i:s';
            } else {
                throw new Error("Invalid format type.");
            }
        },
        validParts: function(type) {
            if (type == "standard") {
                return /hh?|HH?|p|P|ii?|ss?|dd?|DD?|mm?|MM?|yy(?:yy)?/g;
            } else if (type == "php") {
                return /[dDjlNwzFmMnStyYaABgGhHis]/g;
            } else {
                throw new Error("Invalid format type.");
            }
        },
        nonpunctuation: /[^ -\/:-@\[-`{-~\t\n\rTZ]+/g,
        parseFormat: function(format, type) {
            // IE treats \0 as a string end in inputs (truncating the value),
            // so it's a bad format delimiter, anyway
            var separators = format.replace(this.validParts(type), '\0').split('\0'),
                    parts = format.match(this.validParts(type));
            if (!separators || !separators.length || !parts || parts.length == 0) {
                throw new Error("Invalid date format.");
            }
            return {separators: separators, parts: parts};
        },
        parseDate: function(date, format, language, type) {
            if (date instanceof Date) {
                var dateUTC = new Date(date.valueOf() - date.getTimezoneOffset() * 60000);
                dateUTC.setMilliseconds(0);
                return dateUTC;
            }
            if (/^\d{4}\-\d{1,2}\-\d{1,2}$/.test(date)) {
                format = this.parseFormat('yyyy-mm-dd', type);
            }
            if (/^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}\:\d{1,2}$/.test(date)) {
                format = this.parseFormat('yyyy-mm-dd hh:ii', type);
            }
            if (/^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}\:\d{1,2}\:\d{1,2}[Z]{0,1}$/.test(date)) {
                format = this.parseFormat('yyyy-mm-dd hh:ii:ss', type);
            }
            if (/^[-+]\d+[dmwy]([\s,]+[-+]\d+[dmwy])*$/.test(date)) {
                var part_re = /([-+]\d+)([dmwy])/,
                        parts = date.match(/([-+]\d+)([dmwy])/g),
                        part, dir;
                date = new Date();
                for (var i = 0; i < parts.length; i++) {
                    part = part_re.exec(parts[i]);
                    dir = parseInt(part[1]);
                    switch (part[2]) {
                        case 'd':
                            date.setUTCDate(date.getUTCDate() + dir);
                            break;
                        case 'm':
                            date = Datetimepicker.prototype.moveMonth.call(Datetimepicker.prototype, date, dir);
                            break;
                        case 'w':
                            date.setUTCDate(date.getUTCDate() + dir * 7);
                            break;
                        case 'y':
                            date = Datetimepicker.prototype.moveYear.call(Datetimepicker.prototype, date, dir);
                            break;
                    }
                }
                return UTCDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds(), 0);
            }
            var parts = date && date.match(this.nonpunctuation) || [],
                    date = new Date(0, 0, 0, 0, 0, 0, 0),
                    parsed = {},
                    setters_order = ['hh', 'h', 'ii', 'i', 'ss', 's', 'yyyy', 'yy', 'M', 'MM', 'm', 'mm', 'D', 'DD', 'd', 'dd', 'H', 'HH', 'p', 'P'],
                    setters_map = {
                        hh: function(d, v) {
                            return d.setUTCHours(v);
                        },
                        h: function(d, v) {
                            return d.setUTCHours(v);
                        },
                        HH: function(d, v) {
                            return d.setUTCHours(v == 12 ? 0 : v);
                        },
                        H: function(d, v) {
                            return d.setUTCHours(v == 12 ? 0 : v);
                        },
                        ii: function(d, v) {
                            return d.setUTCMinutes(v);
                        },
                        i: function(d, v) {
                            return d.setUTCMinutes(v);
                        },
                        ss: function(d, v) {
                            return d.setUTCSeconds(v);
                        },
                        s: function(d, v) {
                            return d.setUTCSeconds(v);
                        },
                        yyyy: function(d, v) {
                            return d.setUTCFullYear(v);
                        },
                        yy: function(d, v) {
                            return d.setUTCFullYear(2000 + v);
                        },
                        m: function(d, v) {
                            v -= 1;
                            while (v < 0)
                                v += 12;
                            v %= 12;
                            d.setUTCMonth(v);
                            while (d.getUTCMonth() != v)
                                if (isNaN(d.getUTCMonth()))
                                    return d;
                                else
                                    d.setUTCDate(d.getUTCDate() - 1);
                            return d;
                        },
                        d: function(d, v) {
                            return d.setUTCDate(v);
                        },
                        p: function(d, v) {
                            return d.setUTCHours(v == 1 ? d.getUTCHours() + 12 : d.getUTCHours());
                        }
                    },
            val, filtered, part;
            setters_map['M'] = setters_map['MM'] = setters_map['mm'] = setters_map['m'];
            setters_map['dd'] = setters_map['d'];
            setters_map['P'] = setters_map['p'];
            date = UTCDate(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds());
            if (parts.length == format.parts.length) {
                for (var i = 0, cnt = format.parts.length; i < cnt; i++) {
                    val = parseInt(parts[i], 10);
                    part = format.parts[i];
                    if (isNaN(val)) {
                        switch (part) {
                            case 'MM':
                                filtered = $(dates[language].months).filter(function() {
                                    var m = this.slice(0, parts[i].length),
                                            p = parts[i].slice(0, m.length);
                                    return m == p;
                                });
                                val = $.inArray(filtered[0], dates[language].months) + 1;
                                break;
                            case 'M':
                                filtered = $(dates[language].monthsShort).filter(function() {
                                    var m = this.slice(0, parts[i].length),
                                            p = parts[i].slice(0, m.length);
                                    return m.toLowerCase() == p.toLowerCase();
                                });
                                val = $.inArray(filtered[0], dates[language].monthsShort) + 1;
                                break;
                            case 'p':
                            case 'P':
                                val = $.inArray(parts[i].toLowerCase(), dates[language].meridiem);
                                break;
                        }
                    }
                    parsed[part] = val;
                }
                for (var i = 0, s; i < setters_order.length; i++) {
                    s = setters_order[i];
                    if (s in parsed && !isNaN(parsed[s]))
                        setters_map[s](date, parsed[s])
                }
            }
            return date;
        },
        formatDate: function(date, format, language, type) {
            if (date == null) {
                return '';
            }
            var val;
            if (type == 'standard') {
                val = {
                    // year
                    yy: date.getUTCFullYear().toString().substring(2),
                    yyyy: date.getUTCFullYear(),
                    // month
                    m: date.getUTCMonth() + 1,
                    M: dates[language].monthsShort[date.getUTCMonth()],
                    MM: dates[language].months[date.getUTCMonth()],
                    // day
                    d: date.getUTCDate(),
                    D: dates[language].daysShort[date.getUTCDay()],
                    DD: dates[language].days[date.getUTCDay()],
                    p: (dates[language].meridiem.length == 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : ''),
                    // hour
                    h: date.getUTCHours(),
                    // minute
                    i: date.getUTCMinutes(),
                    // second
                    s: date.getUTCSeconds()
                };

                if (dates[language].meridiem.length == 2) {
                    val.H = (val.h % 12 == 0 ? 12 : val.h % 12);
                }
                else {
                    val.H = val.h;
                }
                val.HH = (val.H < 10 ? '0' : '') + val.H;
                val.P = val.p.toUpperCase();
                val.hh = (val.h < 10 ? '0' : '') + val.h;
                val.ii = (val.i < 10 ? '0' : '') + val.i;
                val.ss = (val.s < 10 ? '0' : '') + val.s;
                val.dd = (val.d < 10 ? '0' : '') + val.d;
                val.mm = (val.m < 10 ? '0' : '') + val.m;
            } else if (type == 'php') {
                // php format
                val = {
                    // year
                    y: date.getUTCFullYear().toString().substring(2),
                    Y: date.getUTCFullYear(),
                    // month
                    F: dates[language].months[date.getUTCMonth()],
                    M: dates[language].monthsShort[date.getUTCMonth()],
                    n: date.getUTCMonth() + 1,
                    t: DPGlobal.getDaysInMonth(date.getUTCFullYear(), date.getUTCMonth()),
                    // day
                    j: date.getUTCDate(),
                    l: dates[language].days[date.getUTCDay()],
                    D: dates[language].daysShort[date.getUTCDay()],
                    w: date.getUTCDay(), // 0 -> 6
                    N: (date.getUTCDay() == 0 ? 7 : date.getUTCDay()), // 1 -> 7
                    S: (date.getUTCDate() % 10 <= dates[language].suffix.length ? dates[language].suffix[date.getUTCDate() % 10 - 1] : ''),
                    // hour
                    a: (dates[language].meridiem.length == 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : ''),
                    g: (date.getUTCHours() % 12 == 0 ? 12 : date.getUTCHours() % 12),
                    G: date.getUTCHours(),
                    // minute
                    i: date.getUTCMinutes(),
                    // second
                    s: date.getUTCSeconds()
                };
                val.m = (val.n < 10 ? '0' : '') + val.n;
                val.d = (val.j < 10 ? '0' : '') + val.j;
                val.A = val.a.toString().toUpperCase();
                val.h = (val.g < 10 ? '0' : '') + val.g;
                val.H = (val.G < 10 ? '0' : '') + val.G;
                val.i = (val.i < 10 ? '0' : '') + val.i;
                val.s = (val.s < 10 ? '0' : '') + val.s;
            } else {
                throw new Error("Invalid format type.");
            }
            var date = [],
                    seps = $.extend([], format.separators);
            for (var i = 0, cnt = format.parts.length; i < cnt; i++) {
                if (seps.length) {
                    date.push(seps.shift());
                }
                date.push(val[format.parts[i]]);
            }
            if (seps.length) {
                date.push(seps.shift());
            }
            return date.join('');
        },
        convertViewMode: function(viewMode) {
            switch (viewMode) {
                case 4:
                case 'decade':
                    viewMode = 4;
                    break;
                case 3:
                case 'year':
                    viewMode = 3;
                    break;
                case 2:
                case 'month':
                    viewMode = 2;
                    break;
                case 1:
                case 'day':
                    viewMode = 1;
                    break;
                case 0:
                case 'hour':
                    viewMode = 0;
                    break;
            }

            return viewMode;
        },
        headTemplate: '<thead>' +
                '<tr>' +
                '<th class="prev"><i class="{leftArrow}"/></th>' +
                '<th colspan="5" class="switch"></th>' +
                '<th class="next"><i class="{rightArrow}"/></th>' +
                '</tr>' +
                '</thead>',
        headTemplateV3: '<thead>' +
                '<tr>' +
                '<th class="prev"><span class="{iconType} {leftArrow}"></span> </th>' +
                '<th colspan="5" class="switch"></th>' +
                '<th class="next"><span class="{iconType} {rightArrow}"></span> </th>' +
                '</tr>' +
                '</thead>',
        contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>',
        footTemplate: '<tfoot><tr><th colspan="7" class="today"></th></tr></tfoot>'
    };
    DPGlobal.template = '<div class="datetimepicker">' +
            '<div class="datetimepicker-minutes">' +
            '<table class=" table-condensed">' +
            DPGlobal.headTemplate +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-hours">' +
            '<table class=" table-condensed">' +
            DPGlobal.headTemplate +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-days">' +
            '<table class=" table-condensed">' +
            DPGlobal.headTemplate +
            '<tbody></tbody>' +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-months">' +
            '<table class="table-condensed">' +
            DPGlobal.headTemplate +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-years">' +
            '<table class="table-condensed">' +
            DPGlobal.headTemplate +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '</div>';
    DPGlobal.templateV3 = '<div class="datetimepicker">' +
            '<div class="datetimepicker-minutes">' +
            '<table class=" table-condensed">' +
            DPGlobal.headTemplateV3 +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-hours">' +
            '<table class=" table-condensed">' +
            DPGlobal.headTemplateV3 +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-days">' +
            '<table class=" table-condensed">' +
            DPGlobal.headTemplateV3 +
            '<tbody></tbody>' +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-months">' +
            '<table class="table-condensed">' +
            DPGlobal.headTemplateV3 +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '<div class="datetimepicker-years">' +
            '<table class="table-condensed">' +
            DPGlobal.headTemplateV3 +
            DPGlobal.contTemplate +
            DPGlobal.footTemplate +
            '</table>' +
            '</div>' +
            '</div>';
    $.fn.datetimepicker.DPGlobal = DPGlobal;

    /* DATETIMEPICKER NO CONFLICT
     * =================== */

    $.fn.datetimepicker.noConflict = function() {
        $.fn.datetimepicker = old;
        return this;
    };

    /* DATETIMEPICKER DATA-API
     * ================== */

    $(document).on(
            'focus.datetimepicker.data-api click.datetimepicker.data-api',
            '[data-provide="datetimepicker"]',
            function(e) {
                var $this = $(this);
                if ($this.data('datetimepicker'))
                    return;
                e.preventDefault();
                // component click requires us to explicitly show it
                $this.datetimepicker('show');
            }
    );
    $(function() {
        $('[data-provide="datetimepicker-inline"]').datetimepicker();
    });

}(window.jQuery);


(function($) {
    "use strict";

    var defaultOptions = {
        tagClass: function(item) {
            return 'label label-info';
        },
        itemValue: function(item) {
            return item ? item.toString() : item;
        },
        itemText: function(item) {
            return this.itemValue(item);
        },
        freeInput: true,
        addOnBlur: true,
        maxTags: undefined,
        maxChars: undefined,
        confirmKeys: [13, 44],
        onTagExists: function(item, $tag) {
            $tag.hide().fadeIn();
        },
        trimValue: false,
        allowDuplicates: false
    };

    /**
     * Constructor function
     */
    function TagsInput(element, options) {
        this.itemsArray = [];

        this.$element = $(element);
        this.$element.hide();

        this.isSelect = (element.tagName === 'SELECT');
        this.multiple = (this.isSelect && element.hasAttribute('multiple'));
        this.objectItems = options && options.itemValue;
        this.placeholderText = element.hasAttribute('placeholder') ? this.$element.attr('placeholder') : '';
        this.inputSize = Math.max(1, this.placeholderText.length);

        this.$container = $('<div class="tagsinput"></div>');
        this.$input = $('<input type="text" disabled="disabled" placeholder="' + this.placeholderText + '"/>').appendTo(this.$container);

        this.$element.after(this.$container);

        var inputWidth = (this.inputSize < 3 ? 3 : this.inputSize) + "em";
        //this.$input.get(0).style.cssText = "width: " + inputWidth + " !important;";
        this.$input.get(0).style.cssText = "width: 0px" + " !important;";
        this.build(options);
    }

    TagsInput.prototype = {
        constructor: TagsInput,
        /**
         * Adds the given item as a new tag. Pass true to dontPushVal to prevent
         * updating the elements val()
         */
        add: function(item, dontPushVal) {
            var self = this;

            if (self.options.maxTags && self.itemsArray.length >= self.options.maxTags)
                return;

            // Ignore falsey values, except false
            if (item !== false && !item)
                return;

            // Trim value
            if (typeof item === "string" && self.options.trimValue) {
                item = $.trim(item);
            }

            // Throw an error when trying to add an object while the itemValue option was not set
            if (typeof item === "object" && !self.objectItems)
                throw("Can't add objects when itemValue option is not set");

            // Ignore strings only containg whitespace
            if (item.toString().match(/^\s*$/))
                return;

            // If SELECT but not multiple, remove current tag
            if (self.isSelect && !self.multiple && self.itemsArray.length > 0)
                self.remove(self.itemsArray[0]);

            if (typeof item === "string" && this.$element[0].tagName === 'INPUT') {
                var items = item.split(',');
                if (items.length > 1) {
                    for (var i = 0; i < items.length; i++) {
                        this.add(items[i], true);
                    }

                    if (!dontPushVal)
                        self.pushVal();
                    return;
                }
            }

            var itemValue = self.options.itemValue(item),
                    itemText = self.options.itemText(item),
                    tagClass = self.options.tagClass(item);

            // Ignore items allready added
            var existing = $.grep(self.itemsArray, function(item) {
                return self.options.itemValue(item) === itemValue;
            })[0];
            if (existing && !self.options.allowDuplicates) {
                // Invoke onTagExists
                if (self.options.onTagExists) {
                    var $existingTag = $(".tag", self.$container).filter(function() {
                        return $(this).data("item") === existing;
                    });
                    self.options.onTagExists(item, $existingTag);
                }
                return;
            }

            // if length greater than limit
            if (self.items().toString().length + item.length + 1 > self.options.maxInputLength)
                return;

            // raise beforeItemAdd arg
            var beforeItemAddEvent = $.Event('beforeItemAdd', {item: item, cancel: false});
            self.$element.trigger(beforeItemAddEvent);
            if (beforeItemAddEvent.cancel)
                return;

            // register item in internal array and map
            self.itemsArray.push(item);

            // add a tag element
            var $tag = $('<span class="tag ' + htmlEncode(tagClass) + '">' + htmlEncode(itemText) + '<span data-role="remove"></span></span>');
            $tag.data('item', item);
            self.findInputWrapper().before($tag);
            $tag.after(' ');

            // add <option /> if item represents a value not present in one of the <select />'s options
            if (self.isSelect && !$('option[value="' + encodeURIComponent(itemValue) + '"]', self.$element)[0]) {
                var $option = $('<option selected>' + htmlEncode(itemText) + '</option>');
                $option.data('item', item);
                $option.attr('value', itemValue);
                self.$element.append($option);
            }

            if (!dontPushVal)
                self.pushVal();

            // Add class when reached maxTags
            if (self.options.maxTags === self.itemsArray.length || self.items().toString().length === self.options.maxInputLength)
                self.$container.addClass('tagsinput-max');

            self.$element.trigger($.Event('itemAdded', {item: item}));
        },
        /**
         * Removes the given item. Pass true to dontPushVal to prevent updating the
         * elements val()
         */
        remove: function(item, dontPushVal) {
            var self = this;

            if (self.objectItems) {
                if (typeof item === "object")
                    item = $.grep(self.itemsArray, function(other) {
                        return self.options.itemValue(other) == self.options.itemValue(item);
                    });
                else
                    item = $.grep(self.itemsArray, function(other) {
                        return self.options.itemValue(other) == item;
                    });

                item = item[item.length - 1];
            }

            if (item) {
                var beforeItemRemoveEvent = $.Event('beforeItemRemove', {item: item, cancel: false});
                self.$element.trigger(beforeItemRemoveEvent);
                if (beforeItemRemoveEvent.cancel)
                    return;

                $('.tag', self.$container).filter(function() {
                    return $(this).data('item') === item;
                }).remove();
                $('option', self.$element).filter(function() {
                    return $(this).data('item') === item;
                }).remove();
                if ($.inArray(item, self.itemsArray) !== -1)
                    self.itemsArray.splice($.inArray(item, self.itemsArray), 1);
            }

            if (!dontPushVal)
                self.pushVal();

            // Remove class when reached maxTags
            if (self.options.maxTags > self.itemsArray.length)
                self.$container.removeClass('tagsinput-max');

            self.$element.trigger($.Event('itemRemoved', {item: item}));
        },
        /**
         * Removes all items
         */
        removeAll: function() {
            var self = this;

            $('.tag', self.$container).remove();
            $('option', self.$element).remove();

            while (self.itemsArray.length > 0)
                self.itemsArray.pop();

            self.pushVal();
        },
        /**
         * Refreshes the tags so they match the text/value of their corresponding
         * item.
         */
        refresh: function() {
            var self = this;
            $('.tag', self.$container).each(function() {
                var $tag = $(this),
                        item = $tag.data('item'),
                        itemValue = self.options.itemValue(item),
                        itemText = self.options.itemText(item),
                        tagClass = self.options.tagClass(item);

                // Update tag's class and inner text
                $tag.attr('class', null);
                $tag.addClass('tag ' + htmlEncode(tagClass));
                $tag.contents().filter(function() {
                    return this.nodeType == 3;
                })[0].nodeValue = htmlEncode(itemText);

                if (self.isSelect) {
                    var option = $('option', self.$element).filter(function() {
                        return $(this).data('item') === item;
                    });
                    option.attr('value', itemValue);
                }
            });
        },
        /**
         * Returns the items added as tags
         */
        items: function() {
            return this.itemsArray;
        },
        /**
         * Assembly value by retrieving the value of each item, and set it on the
         * element.
         */
        pushVal: function() {
            var self = this,
                    val = $.map(self.items(), function(item) {
                        return self.options.itemValue(item).toString();
                    });

            self.$element.val(val, true).trigger('change');
        },
        /**
         * Initializes the tags input behaviour on the element
         */
        build: function(options) {
            var self = this;

            self.options = $.extend({}, defaultOptions, options);
            // When itemValue is set, freeInput should always be false
            if (self.objectItems)
                self.options.freeInput = false;

            makeOptionItemFunction(self.options, 'itemValue');
            makeOptionItemFunction(self.options, 'itemText');
            makeOptionFunction(self.options, 'tagClass');

            // Typeahead Bootstrap version 2.3.2
            if (self.options.typeahead) {
                var typeahead = self.options.typeahead || {};

                makeOptionFunction(typeahead, 'source');

                self.$input.typeahead($.extend({}, typeahead, {
                    source: function(query, process) {
                        function processItems(items) {
                            var texts = [];

                            for (var i = 0; i < items.length; i++) {
                                var text = self.options.itemText(items[i]);
                                map[text] = items[i];
                                texts.push(text);
                            }
                            process(texts);
                        }

                        this.map = {};
                        var map = this.map,
                                data = typeahead.source(query);

                        if ($.isFunction(data.success)) {
                            // support for Angular callbacks
                            data.success(processItems);
                        } else if ($.isFunction(data.then)) {
                            // support for Angular promises
                            data.then(processItems);
                        } else {
                            // support for functions and jquery promises
                            $.when(data)
                                    .then(processItems);
                        }
                    },
                    updater: function(text) {
                        self.add(this.map[text]);
                    },
                    matcher: function(text) {
                        return (text.toLowerCase().indexOf(this.query.trim().toLowerCase()) !== -1);
                    },
                    sorter: function(texts) {
                        return texts.sort();
                    },
                    highlighter: function(text) {
                        var regex = new RegExp('(' + this.query + ')', 'gi');
                        return text.replace(regex, "<strong>$1</strong>");
                    }
                }));
            }

            // typeahead.js
            if (self.options.typeaheadjs) {
                var typeaheadjs = self.options.typeaheadjs || {};

                self.$input.typeahead(null, typeaheadjs).on('typeahead:selected', $.proxy(function(obj, datum) {
                    if (typeaheadjs.valueKey)
                        self.add(datum[typeaheadjs.valueKey]);
                    else
                        self.add(datum);
                    self.$input.typeahead('val', '');
                }, self));
            }

            self.$container.on('click', $.proxy(function(event) {
                if (!self.$element.attr('disabled')) {
                    //self.$input.removeAttr('disabled');
                }
                self.$input.focus();
            }, self));

            if (self.options.addOnBlur && self.options.freeInput) {
                self.$input.on('focusout', $.proxy(function(event) {
                    // HACK: only process on focusout when no typeahead opened, to
                    //       avoid adding the typeahead text as tag
                    if ($('.typeahead, .twitter-typeahead', self.$container).length === 0) {
                        self.add(self.$input.val());
                        self.$input.val('');
                    }
                }, self));
            }


            self.$container.on('keydown', 'input', $.proxy(function(event) {
                var $input = $(event.target),
                        $inputWrapper = self.findInputWrapper();

                if (self.$element.attr('disabled')) {
                    self.$input.attr('disabled', 'disabled');
                    return;
                }

                switch (event.which) {
                    // BACKSPACE
                    case 8:
                        if (doGetCaretPosition($input[0]) === 0) {
                            var prev = $inputWrapper.prev();
                            if (prev) {
                                self.remove(prev.data('item'));
                            }
                        }
                        break;

                        // DELETE
                    case 46:
                        if (doGetCaretPosition($input[0]) === 0) {
                            var next = $inputWrapper.next();
                            if (next) {
                                self.remove(next.data('item'));
                            }
                        }
                        break;

                        // LEFT ARROW
                    case 37:
                        // Try to move the input before the previous tag
                        var $prevTag = $inputWrapper.prev();
                        if ($input.val().length === 0 && $prevTag[0]) {
                            $prevTag.before($inputWrapper);
                            $input.focus();
                        }
                        break;
                        // RIGHT ARROW
                    case 39:
                        // Try to move the input after the next tag
                        var $nextTag = $inputWrapper.next();
                        if ($input.val().length === 0 && $nextTag[0]) {
                            $nextTag.after($inputWrapper);
                            $input.focus();
                        }
                        break;
                    default:
                        // ignore
                }

                // Reset internal input's size
                var textLength = $input.val().length,
                        wordSpace = Math.ceil(textLength / 5),
                        size = textLength + wordSpace + 1;
                $input.attr('size', Math.max(this.inputSize, $input.val().length));
            }, self));

            self.$container.on('keypress', 'input', $.proxy(function(event) {
                var $input = $(event.target);

                if (self.$element.attr('disabled')) {
                    self.$input.attr('disabled', 'disabled');
                    return;
                }

                var text = $input.val(),
                        maxLengthReached = self.options.maxChars && text.length >= self.options.maxChars;
                if (self.options.freeInput && (keyCombinationInList(event, self.options.confirmKeys) || maxLengthReached)) {
                    self.add(maxLengthReached ? text.substr(0, self.options.maxChars) : text);
                    $input.val('');
                    event.preventDefault();
                }

                // Reset internal input's size
                var textLength = $input.val().length,
                        wordSpace = Math.ceil(textLength / 5),
                        size = textLength + wordSpace + 1;
                $input.attr('size', Math.max(this.inputSize, $input.val().length));
            }, self));

            // Remove icon clicked
            self.$container.on('click', '[data-role=remove]', $.proxy(function(event) {
                if (self.$element.attr('disabled')) {
                    return;
                }
                clear_delivery_dating($(event.target).parent().text());
                self.remove($(event.target).closest('.tag').data('item'));
            }, self));

            // Only add existing value as tags when using strings as tags
            if (self.options.itemValue === defaultOptions.itemValue) {
                if (self.$element[0].tagName === 'INPUT') {
                    self.add(self.$element.val());
                } else {
                    $('option', self.$element).each(function() {
                        self.add($(this).attr('value'), true);
                    });
                }
            }
        },
        /**
         * Removes all tagsinput behaviour and unregsiter all event handlers
         */
        destroy: function() {
            var self = this;

            // Unbind events
            self.$container.off('keypress', 'input');
            self.$container.off('click', '[role=remove]');

            self.$container.remove();
            self.$element.removeData('tagsinput');
            self.$element.show();
        },
        /**
         * Sets focus on the tagsinput
         */
        focus: function() {
            this.$input.focus();
        },
        /**
         * Returns the internal input element
         */
        input: function() {
            return this.$input;
        },
        /**
         * Returns the element which is wrapped around the internal input. This
         * is normally the $container, but typeahead.js moves the $input element.
         */
        findInputWrapper: function() {
            var elt = this.$input[0],
                    container = this.$container[0];
            while (elt && elt.parentNode !== container)
                elt = elt.parentNode;

            return $(elt);
        }
    };

    /**
     * Register JQuery plugin
     */
    $.fn.tagsinput = function(arg1, arg2) {
        var results = [];

        this.each(function() {
            var tagsinput = $(this).data('tagsinput');
            // Initialize a new tags input
            if (!tagsinput) {
                tagsinput = new TagsInput(this, arg1);
                $(this).data('tagsinput', tagsinput);
                results.push(tagsinput);

                if (this.tagName === 'SELECT') {
                    $('option', $(this)).attr('selected', 'selected');
                }

                // Init tags from $(this).val()
                $(this).val($(this).val());
            } else if (!arg1 && !arg2) {
                // tagsinput already exists
                // no function, trying to init
                results.push(tagsinput);
            } else if (tagsinput[arg1] !== undefined) {
                // Invoke function on existing tags input
                var retVal = tagsinput[arg1](arg2);
                if (retVal !== undefined)
                    results.push(retVal);
            }
        });

        if (typeof arg1 == 'string') {
            // Return the results from the invoked function calls
            return results.length > 1 ? results : results[0];
        } else {
            return results;
        }
    };

    $.fn.tagsinput.Constructor = TagsInput;

    /**
     * Most options support both a string or number as well as a function as
     * option value. This function makes sure that the option with the given
     * key in the given options is wrapped in a function
     */
    function makeOptionItemFunction(options, key) {
        if (typeof options[key] !== 'function') {
            var propertyName = options[key];
            options[key] = function(item) {
                return item[propertyName];
            };
        }
    }
    function makeOptionFunction(options, key) {
        if (typeof options[key] !== 'function') {
            var value = options[key];
            options[key] = function() {
                return value;
            };
        }
    }
    /**
     * HtmlEncodes the given value
     */
    var htmlEncodeContainer = $('<div />');
    function htmlEncode(value) {
        if (value) {
            return htmlEncodeContainer.text(value).html();
        } else {
            return '';
        }
    }

    /**
     * Returns the position of the caret in the given input field
     * http://flightschool.acylt.com/devnotes/caret-position-woes/
     */
    function doGetCaretPosition(oField) {
        var iCaretPos = 0;
        if (document.selection) {
            oField.focus();
            var oSel = document.selection.createRange();
            oSel.moveStart('character', -oField.value.length);
            iCaretPos = oSel.text.length;
        } else if (oField.selectionStart || oField.selectionStart == '0') {
            iCaretPos = oField.selectionStart;
        }
        return (iCaretPos);
    }

    /**
     * Returns boolean indicates whether user has pressed an expected key combination. 
     * @param object keyPressEvent: JavaScript event object, refer
     *     http://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
     * @param object lookupList: expected key combinations, as in:
     *     [13, {which: 188, shiftKey: true}]
     */
    function keyCombinationInList(keyPressEvent, lookupList) {
        var found = false;
        $.each(lookupList, function(index, keyCombination) {
            if (typeof (keyCombination) === 'number' && keyPressEvent.which === keyCombination) {
                found = true;
                return false;
            }

            if (keyPressEvent.which === keyCombination.which) {
                var alt = !keyCombination.hasOwnProperty('altKey') || keyPressEvent.altKey === keyCombination.altKey,
                        shift = !keyCombination.hasOwnProperty('shiftKey') || keyPressEvent.shiftKey === keyCombination.shiftKey,
                        ctrl = !keyCombination.hasOwnProperty('ctrlKey') || keyPressEvent.ctrlKey === keyCombination.ctrlKey;
                if (alt && shift && ctrl) {
                    found = true;
                    return false;
                }
            }
        });

        return found;
    }

    /**
     * Initialize tagsinput behaviour on inputs and selects which have
     * data-role=tagsinput
     */
    $(function() {
        $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
    });
})(window.jQuery);
