$(document).ready(function () {
    setCurrentData();
});

/**
 * @param {Date} date
 */
function MyDate(date) {
    this.day = date.getDate() + 1;
    this.month = date.getMonth() + 1;
    this.year = date.getFullYear();
}

/**
 * @param {int|undefined} dayModifier
 * @returns {string}
 */
MyDate.prototype.getDate = function (dayModifier) {
    var month = this.month < 10 ? "0" + this.month : this.month;
    var day = this.day < 10 ? "0" + (this.day - dayModifier) : this.day - dayModifier;

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
