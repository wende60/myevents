
"use strict";

var _config;

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var myEventsDatepicker = {

    config: (_config = {
        inst: {},
        format: false,
        firstDay: false,
        lang: false,
        current: false,
        formatRegEx: /(\.|\-|\/|\||\:)/,
        weekDayNames: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
        monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'Novermber', 'Dezember']
    }, _defineProperty(_config, 'firstDay', 1), _defineProperty(_config, 'format', 'dd.mm.yyyy'), _defineProperty(_config, 'testing', true), _config),

    /**
     * init global config
     * @author      kgde@wendenburg.de
     * @param       {object} customized global config data
     * @return      {void}
     */
    init: function init(data) {
        var _this = this;

        // overwrite default values
        Object.keys(data).forEach(function (key) {
            _this.config[key] = data[key];
        });

        // move sunday to last position if first day is 1
        var shiftDay = this.config.weekDayNames.splice(0, this.config.firstDay);
        if (shiftDay.length) {
            this.config.weekDayNames.push(shiftDay);
        }
    },


    /**
     * return instance data merged from defaults and passed data
     * @author      kgde@wendenburg.de
     * @param       {string} element id to render myEventsDatepicker in
     * @param       {object} customized myEventsDatepicker-instance data
     * @return      {object} merged myEventsDatepicker-instance data
     */
    createInstanceData: function createInstanceData(id, data) {
        var _this2 = this;

        var dateObj = new Date();
        var instanceData = {
            dates: [],
            id: id,
            calWrapper: document.getElementById(id),
            year: dateObj.getFullYear(),
            month: dateObj.getMonth() + 1,
            todayTime: dateObj.getTime(),
            onlyPast: false,
            onlyFuture: false,
            eventDays: [],
            eventEntry: false,
            eventList: false
        };

        // overwrite instance default values
        Object.keys(data).forEach(function (key) {
            switch (key) {
                case 'onlyPast':
                case 'onlyFuture':
                    instanceData[key] = _this2.getMsFromToday(data[key]);
                    break;
                case 'eventEntry':
                case 'eventList':
                    instanceData[key] = document.getElementById(data[key]);
                    break;
            }
        });
        return instanceData;
    },


    /**
     * create myEventsDatepicker-instance
     * @author      kgde@wendenburg.de
     * @param       {string} calendar instance id
     * @return      {object} calendar instance data
     */
    createCalendar: function createCalendar(id, data) {

        // note initialized yet? use defaults
        if (this.config.firstDay === false) {
            this.init({});
        }

        // set instance config data
        this.config.inst[id] = this.createInstanceData(id, data);

        this.syncMyEventsDatepicker(id);

        // collect all dates for calendar instance
        this.updateCalendarDays(id);

        // set properties for each date of calensar instance
        this.updateCalendarProperties(id);

        this.createHtml(id);
    },
    updateCalendarDays: function updateCalendarDays(id) {
        var inst = this.config.inst[id];
        inst.dates = this.getCalendarDays(inst.year, inst.month);
    },
    updateCalendarProperties: function updateCalendarProperties(id) {
        var inst = this.config.inst[id];
        inst.dates = this.setDateProperties(inst.dates, inst);
    },
    syncMyEventsDatepicker: function syncMyEventsDatepicker(id) {
        var inst = this.config.inst[id];
        var eventString = inst.eventEntry.value || false;

        if (eventString) {
            var eventTimes = eventString.split(",");
            eventTimes.forEach(function (eventUnixTime) {
                inst.eventDays.push(parseInt(eventUnixTime));
            });

            var firstEvent = new Date(inst.eventDays[0]);
            inst.year = firstEvent.getFullYear();
            inst.month = firstEvent.getMonth() + 1;
        }
    },
    syncMyEventEntry: function syncMyEventEntry(id) {
        var inst = this.config.inst[id];
        inst.eventEntry.value = inst.eventDays.join(',');
    },
    dplNextMonth: function dplNextMonth(id) {
        var inst = this.config.inst[id];
        if (inst.month + 1 < 13) {
            inst.month += 1;
        } else {
            inst.year += 1;
            inst.month = 1;
        }
        this.updateCalendarDays(id);
        this.updateCalendarProperties(id);
        this.createHtml(id);
        return [inst.year, inst.month];
    },
    dplPastMonth: function dplPastMonth(id) {
        var inst = this.config.inst[id];
        if (inst.month - 1 > 0) {
            inst.month -= 1;
        } else {
            inst.year -= 1;
            inst.month = 12;
        }
        this.updateCalendarDays(id);
        this.updateCalendarProperties(id);
        this.createHtml(id);
        return [inst.year, inst.month];
    },
    setMonthAndYear: function setMonthAndYear(id, month, year) {
        var inst = this.config.inst[id];
        inst.year = year;
        inst.month = month;

        this.updateCalendarDays(id);
        this.updateCalendarProperties(id);
        this.createHtml(id);
        return [inst.year, inst.month];
    },


    /**
     * get all dates of a month by an array of weeks
     * prepare all dates for a table-row output
     * @author      kgde@wendenburg.de
     * @param       {string} instance id
     * @return      {array} all weeks contaning all dates and the date details
     */
    getDatesByWeeks: function getDatesByWeeks(id) {
        var dates = this.config.inst[id].dates.slice(0),
            weeks = [],
            week;
        while (dates.length) {
            week = dates.splice(0, 7);
            weeks.push(week);
        }
        return weeks;
    },


    /**
     * collect all dates needed for a calendar
     * including days before and after to fill up a week
     * @author      kgde@wendenburg.de
     * @param       {number} year 4 digits
     * @param       {number} month 1-2 digits from 1
     * @return      {array} array of objects containing date's details
     */
    getCalendarDays: function getCalendarDays(y, m) {

        // get all day objects of month
        var i,
            item,
            currentDates = this.getDaysOfMonth(y, m),
            firstDayOfMonth = currentDates[0].dplDay,
            lastDayOfMonth = currentDates[currentDates.length - 1].dplDay;

        // get day objects from past month to fill up week days
        if (firstDayOfMonth > 0) {
            var pastMonth = m - 1 > 0 ? m - 1 : 12;
            var pastYear = m - 1 > 0 ? y : y - 1;
            var pastDates = this.getDaysOfMonth(pastYear, pastMonth);
            var pastDatesAdd = pastDates.slice(firstDayOfMonth * -1);
            currentDates = pastDatesAdd.concat(currentDates);
        }

        // get day objects from next month to fill up week days
        if (lastDayOfMonth < 6) {
            var nextMonth = m + 1 > 12 ? 1 : m + 1;
            var nextYear = m + 1 > 12 ? y + 1 : y;
            var nextDates = this.getDaysOfMonth(nextYear, nextMonth);
            var nextDatesAdd = nextDates.slice(0, 6 - lastDayOfMonth);
            currentDates = currentDates.concat(nextDatesAdd);
        }
        return currentDates;
    },


    /**
     * collect all dates of a month
     * @author      kgde@wendenburg.de
     * @param       {number} year 4 digits
     * @param       {number} month 1-2 digits from 1
     * @return      {array} array of objects containing date's details
     */
    getDaysOfMonth: function getDaysOfMonth(y, m) {

        var date = false,
            dates = [],
            month = m,
            day = 0;

        while (month === m) {
            // store date from last loop
            if (date !== false) {
                dates.push(date);
            }
            date = this.getDateByYmd(y, m, day += 1);
            month = date.month;
        }
        return dates;
    },


    /**
     * get date object
     * @author      kgde@wendenburg.de
     * @param       {number} year 4 digits
     * @param       {number} month 1-2 digits from 1
     * @param       {number} day 1-2 digits from 1
     * @return      {object} new Date
     */
    getDateByYmd: function getDateByYmd(y, m, d) {
        var dateObj = new Date(y, m - 1, d);
        return this.getDate(dateObj);
    },


    /**
     * get detailed data from a date object
     * @author      kgde@wendenburg.de
     * @param       {object} new Date
     * @return      {object} date's details
     */
    getDate: function getDate() {
        var dateObj = arguments.length <= 0 || arguments[0] === undefined ? new Date() : arguments[0];


        // modify day of week if week does not start from Sunday (0)
        var getDay = dateObj.getDay();
        var dplDay = getDay - this.config.firstDay;
        if (dplDay < 0) {
            dplDay += 7;
        }

        var date = dateObj.getDate();
        var month = dateObj.getMonth() + 1;
        var year = dateObj.getFullYear();

        return {
            date: date,
            day: getDay,
            dplDay: dplDay,
            month: month,
            year: year,
            time: dateObj.getTime(),
            isWeekend: getDay === 0 || getDay === 6 ? true : false,
            dateString: this.createFormattedDate(date, month, year)
        };
    },


    /**
     * calculate some date properties depending on calendar instance's config
     * @author      kgde@wendenburg.de
     * @param       {array} array of calendar instance's date details
     * @param       {object} calendar instance's config data
     * @return      {array} array of calendar instance's date details with additional infos
     */
    setDateProperties: function setDateProperties(dates, inst) {

        dates.forEach(function (item) {

            item.isActive = true;
            item.isCurrent = false;
            item.isOtherMonth = false;
            item.isEvent = false;

            // first check if active
            if (inst.onlyPast && item.time > inst.onlyPast) {
                item.isActive = false;
            }
            if (inst.onlyFuture && item.time < inst.onlyFuture) {
                item.isActive = false;
            }
            if (inst.month !== item.month) {
                item.isOtherMonth = true;
            }
            if (inst.todayTime && inst.todayTime === item.time) {
                item.isCurrent = true;
            }

            if (inst.eventDays.length) {
                inst.eventDays.forEach(function (eventTime) {
                    if (item.time === eventTime) {
                        item.isEvent = true;
                    }
                });
            }

            item.id = inst.id;
        });
        return dates;
    },


    /**
     * get timestring from today's date plus shift days
     * @author      kgde@wendenburg.de
     * @param       {number} days
     * @return      {number} time in milliseconds from 1970
     */
    getMsFromToday: function getMsFromToday(shiftDays) {
        var today = this.getDate();
        return this.getDateByYmd(today.year, today.month, today.date + shiftDays).time;
    },
    getTimeFromMsAndDays: function getTimeFromMsAndDays(time, shiftDays) {
        var dateObj = new Date(time);
        var timeDate = this.getDate(dateObj);
        return this.getDateByYmd(timeDate.year, timeDate.month, timeDate.date + shiftDays).time;
    },


    /**
     * create a formatted datestring according to the format string
     * @author      kgde@wendenburg.de
     * @param       {number} date
     * @param       {number} month
     * @param       {number} year
     * @return      {string} formatted datestring
     */
    createFormattedDate: function createFormattedDate(date, month, year) {

        var formatParts = this.config.format.split(this.config.formatRegEx);
        var dateString = "";

        formatParts.map(function (item) {
            switch (item) {
                case 'd':
                    dateString += date;
                    break;
                case 'dd':
                    dateString += date < 10 ? '0' + date : date;
                    break;
                case 'm':
                    dateString += month;
                    break;
                case 'mm':
                    dateString += month < 10 ? '0' + month : month;
                    break;
                case 'yyyy':
                    dateString += year;
                    break;
                default:
                    dateString += item;
            }
        });
        return dateString;
    },


    /**
     * create a dateObject by a formatted datestring according to the format string
     * @author      kgde@wendenburg.de
     * @param       {number} date
     * @param       {number} month
     * @param       {number} year
     * @return      {string} formatted datestring
     */
    createDateObjFromFormattedDate: function createDateObjFromFormattedDate(dateString) {

        var formatParts = this.config.format.split(this.config.formatRegEx);
        var timeParts = dateString.split(this.config.formatRegEx);
        var dateParts = {};

        formatParts.map(function (item, index) {
            switch (item) {
                case 'd':
                case 'dd':
                    dateParts.date = parseInt(timeParts[index], 10);
                    break;
                case 'm':
                case 'mm':
                    dateParts.month = parseInt(timeParts[index], 10);
                    break;
                case 'yyyy':
                    dateParts.year = timeParts[index];
                    break;
            }
        });
        return this.getDateByYmd(dateParts.year, dateParts.month, dateParts.date);
    },
    handleDayClick: function handleDayClick(e, dayData) {

        var inst = this.config.inst[dayData.id];
        var removeTime = false;
        inst.eventDays.find(function (eventTime, index) {
            if (eventTime === dayData.time) {
                removeTime = index;
            }
        }, dayData.time);

        if (removeTime !== false) {
            inst.eventDays.splice(removeTime, 1);
        } else {
            inst.eventDays.push(dayData.time);
        }

        this.syncMyEventEntry(dayData.id);
        this.updateCalendarProperties(dayData.id);
        this.createHtml(dayData.id);
    },


    /**
     * create calendar's html
     * @author      kgde@wendenburg.de
     * @param       {array} array of objects containing date's details
     * @return      {void}
     */
    createHtml: function createHtml(id) {
        var _this3 = this;

        var inst = this.config.inst[id];
        var table = document.createElement('table');
        var tableHead = document.createElement('thead');
        var tableBody = document.createElement('tbody');
        var inst = this.config.inst[id];
        var datesByWeek = this.getDatesByWeeks(id);

        // create myEventsDatepicker title bar
        var titleRow = document.createElement('tr');
        var buttonBack = document.createElement('th');
        var titleCell = document.createElement('th');
        var buttonNext = document.createElement('th');

        var backContent = document.createTextNode('\<');
        var titleContent = document.createTextNode(this.config.monthNames[inst.month - 1] + ' ' + inst.year);
        var nextContent = document.createTextNode('\>');

        titleRow.appendChild(buttonBack);
        titleRow.appendChild(titleCell);
        titleRow.appendChild(buttonNext);

        buttonBack.appendChild(backContent);
        titleCell.appendChild(titleContent);
        buttonNext.appendChild(nextContent);

        titleCell.setAttribute('colspan', 5);
        tableHead.appendChild(titleRow);

        buttonBack.addEventListener('click', function (e) {
            _this3.dplPastMonth(id);
        }, false);
        buttonNext.addEventListener('click', function (e) {
            _this3.dplNextMonth(id);
        }, false);

        // create myEventsDatepicker's day names bar
        var weekRowDayNames = document.createElement('tr');
        this.config.weekDayNames.forEach(function (weekDay) {
            var weekDayTh = document.createElement('td');
            var weekDayName = document.createTextNode(weekDay);
            weekDayTh.appendChild(weekDayName);
            weekRowDayNames.appendChild(weekDayTh);
        });
        tableHead.appendChild(weekRowDayNames);

        // create all days of a month
        datesByWeek.forEach(function (weekDayData) {
            var weekRowDays = document.createElement('tr');
            weekDayData.forEach(function (dayData) {
                var weekDayTd = document.createElement('td');
                var weekDayDate = document.createTextNode(dayData.date);
                weekDayTd.appendChild(weekDayDate);
                weekRowDays.appendChild(weekDayTd);
                // set day's td classe
                var classes = [];
                if (dayData.isOtherMonth) {
                    classes.push("calendarIsOtherMonth");
                }
                if (dayData.isActive) {
                    classes.push("calendarIsActive");
                    weekDayTd.addEventListener('click', function (e) {
                        _this3.handleDayClick(e, dayData);
                    }, false);
                }
                if (dayData.isWeekend) {
                    classes.push("calendarIsWeekend");
                }
                if (dayData.isCurrent) {
                    classes.push("calendarIsToday");
                }
                if (dayData.isEvent) {
                    classes.push("calendarIsEvent");
                }
                weekDayTd.setAttribute('class', classes.join(' '));
            });
            tableBody.appendChild(weekRowDays);
        });

        inst.calWrapper.innerHTML = '';
        table.appendChild(tableHead);
        table.appendChild(tableBody);
        inst.calWrapper.appendChild(table);

        inst.eventList.innerHTML = '';
        inst.eventDays.forEach(function (eventTime) {
            var dayData = _this3.getDate(new Date(eventTime));
            var timeLi = document.createElement('li');
            var timeString = document.createTextNode(dayData.dateString);
            timeLi.appendChild(timeString);
            inst.eventList.appendChild(timeLi);
        });
    },


    /**
     * testout id set rue
     * @author      kgde@wendenburg.de
     * @param       {mixed} list of output items
     * @return      {void}
     */
    db: function db() {
        if (!this.config.testing) {
            return false;
        }
        try {
            console.info('----------');

            for (var _len = arguments.length, out = Array(_len), _key = 0; _key < _len; _key++) {
                out[_key] = arguments[_key];
            }

            out.forEach(function (m) {
                console.info(m);
            });
            console.info('----------');
        } catch (err) {
            alert(err);
        }
    }
};

