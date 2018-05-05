<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>NPU Stat</title>
</head>
<body>
<?PHP
//require_once(dirname(dirname(__FILE__)).'/config.php');
require_once('../../config.php');
echo $OUTPUT->header();
$categor = $DB->get_records('subcourse', null);
?>
<form name='form1' method="post">
    <!--<input type='text' name='from' value = '01-01-2000'>
    <input type='text' name='to' value = 'now'>-->
    <input type="date" name="from" id="from" value="2012-01-01" min="2012-01-01">
    <input type="date" name="to" id="to" value="">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var d = new Date();
            var day = d.getDate() + 1;
            if (day < 10) day = "0" + day;
            var month = d.getMonth() + 1;
            if (month < 10) month = "0" + month;
            var year = d.getFullYear();
            var name_input = year + "-" + month + "-" + day;
            document.getElementById('to').value = name_input;
        });
    </script>

    <select name='cat'></select>
    <?PHP
    $stack = array();
    foreach ($categor as $c) {
        array_push($stack, $c->course);
    }
    $result = array_unique($stack);
    $coursewith = $DB->get_records('course', null);
    end($result);
    foreach ($coursewith as $b) {
        for ($x = 0; $x <= key($result); $x++) {
            if ($b->id == $result[$x]) {
                print "<option value=' " . $b->id . " '>" . $b->fullname . "</option>";
            }
        }
    }
    print_r($stack);
    print_r($result);
    echo key($result);
    ?>
    </br>
    <input type="submit" name="button1" value="Go" onclick="week(form1)">

</form>
<?PHP

if ($_POST['button1']) {
    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);
    $course = $DB->get_records('subcourse', array('course' => $_POST['cat']));


    echo "<table border = 1>";
    echo "<td>", "Назва пiкурсу", "</td>";
    echo "<td>", "Кiлькiсть вiдвiдувань", "</td>";
    echo "<td>", "Зареестровано студентiв", "</td>";
    $sub = $DB->get_records_sql('SELECT * FROM {course_modules} WHERE course = ? AND module = ?', array($_POST['cat'], '34'));
    $sub2 = array();
    foreach ($sub as $hh) {
        array_push($sub2, $hh->id);
    }
    $sub1 = 0;
    //foreach($sub as $g1){
    //$course_name = $DB->get_records('subcourse',array('id'=>$g1->id));
    foreach ($course as $c1) {
        $course_name = $DB->get_records('subcourse', array('name' => $c1->name));
        foreach ($course_name as $c2) {
            //$views = $DB->get_records('logstore_standard_log',array('action'=>'viewed', 'target'=>'course', 'courseid'=>$c2->id));
            $views = $DB->get_records_sql('SELECT * FROM {logstore_standard_log} WHERE action = ? AND target = ? AND contextinstanceid = ? AND timecreated > ? AND timecreated < ?', array('viewed', 'course_module', $sub2[$sub1], $from, $to));

            $stud = $DB->get_records('context', array('contextlevel' => 50, 'instanceid' => $c2->id));
            foreach ($stud as $c4) {
                $stud2 = $DB->get_records('role_assignments', array('roleid' => 5, 'contextid' => $c4->id));
                $count2 = 0;
                $count = 0;
                foreach ($stud2 as $c5) {
                    $count2++;
                }
            }
            foreach ($views as $c3) {
                $count++;
            }
            echo "<tr>", "<td>", $c1->name, " ", $sub2[$sub1], "</td>";
            echo "<td>", $count, "</td>";
            echo "<td>", $count2, "</td>";
            $sub1++;
        }
    }
}
?>
</body>
</html>