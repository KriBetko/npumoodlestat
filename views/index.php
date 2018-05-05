<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NPU Stat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?PHP
/** @noinspection PhpIncludeInspection */
require_once('../../config.php');

try {
    echo $OUTPUT->header();
} catch (coding_exception $e) {
    //echo $e->getMessage();
}

try {
    $categories = $DB->get_records('course_categories', null);
} catch (dml_exception $e) {
    echo $e->getMessage();
}
?>

<section class="my-section">
    <div class="my-container">
        <h1 class="my-title">
            Статистика по категоріям
        </h1>

        <form name="form" method="post">
            <div class="my-field">
                <label class="my-label" for="from">Від</label>
                <div class="my-control">
                    <input class="my-input"
                           type="date"
                           name="from"
                           id="from"
                           value="2012-01-01"
                           min="2012-01-01">
                </div>
            </div>

            <div class="my-field">
                <label class="my-label" for="to">До</label>
                <div class="my-control">
                    <input class="my-input"
                           type="date"
                           name="to"
                           id="to">
                </div>
            </div>

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

            <div class="my-field">
                <div class="my-control">
                    <label class="my-label" for="category">Категорія</label>
                    <select name="category">
                        <?php
                        foreach ($categories as $category) {
                            echo "<option value='" . $category->id . "'>" . $category->name . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="my-field">
                <div class="my-control">
                    <button type="submit" name="submit" class="my-button is-link">Submit</button>
                </div>
            </div>

            <!--<input type="submit" name="button1" value="Go" onclick="week(form)">-->
        </form>
    </div>
</section>

<?PHP

if ($_POST['button1']) {
    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    $course = $DB->get_records('course', array('category' => $_POST['cat']));

    echo "<table border = 1>";
    echo "<td>", "Назва курсу", "</td>";
    echo "<td>", "Кiлькiсть вiдвiдувань", "</td>";
    echo "<td>", "Зареестровано студентiв", "</td>";
    echo "<td>", "Коеф. вiдвiдуваностi", "</td>";
    echo "<td>", "Кiлькiсть видiв дiяльностi", "</td>";
    foreach ($course as $c1) {
        $course_name = $DB->get_records('course', array('fullname' => $c1->fullname));
        foreach ($course_name as $c2) {
            //$views = $DB->get_records('logstore_standard_log',array('action'=>'viewed', 'target'=>'course', 'courseid'=>$c2->id));
            $views = $DB->get_records_sql('SELECT * FROM {logstore_standard_log} WHERE action = ? AND target = ? AND courseid = ? AND timecreated > ? AND timecreated < ?', array('viewed', 'course', $c2->id, $from, $to));
            $stud = $DB->get_records('context', array('contextlevel' => 50, 'instanceid' => $c2->id));
            foreach ($stud as $c4) {
                $stud2 = $DB->get_records('role_assignments', array('roleid' => 5, 'contextid' => $c4->id));
                $count2 = 0;
                $count = 0;
                $count3 = 0;
                foreach ($stud2 as $c5) {
                    $count2++;
                }
            }
            foreach ($views as $c3) {
                $count++;
            }
            $vidu = $DB->get_records_sql('SELECT * FROM {course_modules} WHERE course = ?', array($c2->id));
            foreach ($vidu as $gg) {
                $count3++;
            }
            echo "<tr>", "<td>", $c1->fullname, "</td>";
            echo "<td>", $count, "</td>";
            echo "<td>", $count2, "</td>";
            echo "<td>", round($count / $count2), "</td>";
            echo "<td>", $count3, "</td>";

        }
    }
}
?>
</body>
</html>