$(document).ready(function () {
    setCurrentData();
});

function setCurrentData() {
    var d = new Date();
    var day = d.getDate() + 1;
    if (day < 10) day = "0" + day;
    var month = d.getMonth() + 1;
    if (month < 10) month = "0" + month;
    var year = d.getFullYear();

    var inputFrom = $('input[name="from"]');
    var inputTo = $('input[name="to"]');

    if (inputFrom && inputFrom.val() === "") {
        console.log(inputFrom.val());
        inputFrom.val(year + "-" + month + "-" + day);
    }

    if (inputTo && inputTo.val() === "") {
        console.log(inputTo.val());
        inputTo.val(year + "-" + month + "-" + day);
    }
}
