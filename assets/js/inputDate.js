$(document).ready(function () {
    setCurrentData();

    $('select[name="category"]').change(function () {
        $('#select-course').addClass('my-is-loading');
        // noinspection JSUnusedGlobalSymbols
        $.ajax({
            type: "POST",
            url: "/report/npumoodlestat/ajax.php",
            data: {
                category: $(this).val()
            }
        }).done(function (data) {
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
                        '<span class="icon is-small is-right"><i class="fas fa-check"></i></span>' +
                        '</option>');
                }
            } else {
                courseSelect.prop('disabled', true);
            }
        }).always(function () {
            $('#select-course').removeClass('my-is-loading');
        });
    });
});

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
