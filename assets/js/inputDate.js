$(document).ready(function () {
    setCurrentData();

    $('select[name="category"]').change(onCategorySelect);

    $('select[name="course"]').change(onCourseSelect);
});

function onCategorySelect() {
    post('/report/npumoodlestat/ajax.php', {
        category: $('select[name="category"]').val()
    }, function () {
        $('#select-course').addClass('my-is-loading');
    }, function (data) {
        var courseSelect = $('select[name="course"]');
        courseSelect.find('option').remove();

        if (data['courses'].length > 0) {
            courseSelect.prop('disabled', false);

            for (var course in data['courses']) {
                // noinspection JSUnfilteredForInLoop
                courseSelect.append(
                    '<option value="' +
                    data['courses'][course]['id'] + '">' +
                    data['courses'][course]['fullname'] +
                    '</option>');
            }

            onCourseSelect();
        } else {
            courseSelect.prop('disabled', true);
            $('select[name="group"]').prop('disabled', true).find('option').remove();
        }
    }, function () {
        $('#select-course').removeClass('my-is-loading');
    });
}

function onCourseSelect() {
    post('/report/npumoodlestat/ajax.php', {
        course: $('select[name="course"]').val(),
        from: $('input[name="from"]').val(),
        to: $('input[name="to"]').val()
    }, function () {
        $('#select-group').addClass('my-is-loading');
    }, function (data) {
        var groupSelect = $('select[name="group"]');
        groupSelect.find('option').remove();

        if (data['groups'].length > 0) {
            groupSelect.prop('disabled', false);

            for (var course in data['groups']) {
                // noinspection JSUnfilteredForInLoop
                groupSelect.append(
                    '<option value="' +
                    data['groups'][course]['id'] + '">' +
                    data['groups'][course]['name'] +
                    '</option>');
            }
        } else {
            groupSelect.prop('disabled', true);
        }
    }, function () {
        $('#select-group').removeClass('my-is-loading');
    });
}

/**
 * @param {string} url
 * @param {object} data
 * @param {function} beforeSend
 * @param {function} success
 * @param {function} complete
 */
function post(url, data, beforeSend, success, complete) {
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        beforeSend: beforeSend,
        success: success,
        complete: complete
    });
}

/**
 * @param {Date} date
 */
function MyDate(date) {
    var day = date.getDate();
    this.day = day === 0 ? day + 1 : day;
    this.month = date.getMonth() + 1;
    this.year = date.getFullYear();
}

/**
 * @param {int|undefined} dayModifier
 * @returns {string}
 */
MyDate.prototype.getDate = function (dayModifier) {
    var month = this.month < 10 ? "0" + this.month : this.month;
    var day = this.day - dayModifier < 10 ? "0" + (this.day - dayModifier) : this.day - dayModifier;

    return this.year + "-" + month + "-" + day;
};

function setCurrentData() {
    var date = new MyDate(new Date());

    var inputFrom = $('input[name="from"]');
    var inputTo = $('input[name="to"]');

    if (inputFrom && inputFrom.val() === "") {
        inputFrom.val(date.getDate(1));
    }

    if (inputTo && inputTo.val() === "") {
        inputTo.val(date.getDate(0));
    }
}
