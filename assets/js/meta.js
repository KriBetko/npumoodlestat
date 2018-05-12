$(document).ready(function () {
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
