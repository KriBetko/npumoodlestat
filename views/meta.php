<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NPU Stat</title>
    <link rel="stylesheet" href="assets/style/bulma.min.css">
    <link rel="stylesheet" href="assets/style/style.min.css">
</head>
<body>

<?PHP
/** @noinspection PhpIncludeInspection */
require_once('../../config.php');

try {
    echo $OUTPUT->header();
} catch (coding_exception $e) {
    echo "<pre style=\"color: red;\">{\$e->getMessage()}</pre>";
}

try {
    $categories = $DB->get_records('subcourse', null);
} catch (dml_exception $e) {
    echo "<pre style=\"color: red;\">{\$e->getMessage()}</pre>";
}
?>

<section class="my-section">
    <div class="my-container">
        <h1 class="my-title">
            Статистика по мета-курсам
        </h1>
        <form name="form" method="post">
            <div class="my-field my-is-horizontal">
                <div class="my-field-label my-is-normal">
                    <label class="my-label">Дата</label>
                </div>
                <div class="my-field-body">
                    <div class="my-field">
                        <p class="my-control my-is-expanded">
                            <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                echo "<input class=\"my-input\" type=\"date\" name=\"from\" value=\"{$_POST['from']}\">";
                            } else {
                                echo '<input class="my-input" type="date" name="from">';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="my-field">
                        <p class="my-control my-is-expanded">
                            <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                echo "<input class=\"my-input\" type=\"date\" name=\"to\" value=\"{$_POST['to']}\">";
                            } else {
                                echo '<input class="my-input" type="date" name="to">';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="my-field my-is-horizontal">
                <div class="my-field-label my-is-normal">
                    <label class="my-label">Курс</label>
                </div>
                <div class="my-field-body">
                    <div class="my-field">
                        <div class="my-control my-is-expanded">
                            <div class="my-select my-is-fullwidth">
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <select name="category">
                                    <?php

                                    $stack = array();

                                    foreach ($categories as $category) {
                                        array_push($stack, $category->course);
                                    }

                                    $result = array_unique($stack);

                                    try {
                                        $courseWith = $DB->get_records('course', null);
                                    } catch (dml_exception $e) {
                                        echo "<pre style=\"color: red;\">{$e->getMessage()}</pre>";
                                    }

                                    end($result);

                                    foreach ($courseWith as $b) {
                                        for ($x = 0; $x <= key($result); $x++) {
                                            if ($b->id == $result[$x]) {
                                                print "<option value=\"{$b->id}\">{$b->fullname}</option>";
                                            }
                                        }
                                    }

                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-field">
                <div class="my-control">
                    <input type="submit" name="submit" class="my-button my-is-link">
                </div>
            </div>
        </form>
    </div>
</section>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>", var_export($_POST), "</pre>";

    echo
    "<section class=\"my-section\">",
    "<div class=\"my-container\">",
    "<h1 class=\"my-title\">Статистика</h1>";

    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    try {
        $subCourses = $DB->get_records('subcourse', ['course' => $_POST['course']]);
    } catch (dml_exception $e) {
        error($e);
    }

    echo "<table class=\"my-table my-is-bordered my-is-fullwidth\">";

    echo "<thead><tr>";
    echo "<th>Назва пiкурсу</th>";
    echo "<th>Кiлькiсть вiдвiдувань</th>";
    echo "<th>Зареестровано студентiв</th>";
    echo "</tr></thead>";

    echo "<tfoot><tr>";
    echo "<th>Назва пiкурсу</th>";
    echo "<th>Кiлькiсть вiдвiдувань</th>";
    echo "<th>Зареестровано студентiв</th>";
    echo "</tr></tfoot>";

    echo "<tbody>";

    try {
        $subCourseModules = $DB->get_records_sql('SELECT * FROM mdl_course_modules WHERE course = ? AND module = ?', [
            $_POST['course'], '34'
        ]);
    } catch (dml_exception $e) {
        error($e);
    }

    echo "<pre>", var_export($subCourseModules), "</pre>";

    $subCourseModulesId = [];

    foreach ($subCourseModules as $subCourseModule) {
        $subCourseModulesId[] = $subCourseModule->id;
    }

    foreach ($subCourses as $index => $subCourse) {
        echo '<tr>', "<td><a href=\"/course/view.php?id={$subCourse->id}\">{$subCourse->fullname}</a>{$index}</td>";

        try {
            $subCoursesByName = $DB->get_records('subcourse', ['name' => $subCourse->name]);
        } catch (dml_exception $e) {
            error($e);
        }

        foreach ($subCoursesByName as $subCourseByName) {
            try {
                $views = $DB->get_records_sql('SELECT * FROM mdl_logstore_standard_log WHERE action = ? AND target = ? AND contextinstanceid = ? AND timecreated > ? AND timecreated < ?', [
                    'viewed', 'course_module', $subCourseModulesId[$index], $from, $to
                ]);
            } catch (dml_exception $e) {
                error($e);
            }

            $countOfViews = count($views);

            try {
                $subCoursesContext = $DB->get_records('context', ['contextlevel' => 50, 'instanceid' => $subCourseByName->id]);
            } catch (dml_exception $e) {
                error($e);
            }

            foreach ($subCoursesContext as $subCourseContext) {
                try {
                    $subCourseStudents = $DB->get_records('role_assignments', ['roleid' => 5, 'contextid' => $subCourseContext->id]);
                } catch (dml_exception $e) {
                    error($e);
                }

                $countOfStudents = count($subCourseStudents);
            }

            echo "<tr><td>{$subCourse->name} {$subCourseModulesId[$index]}</td>";
            echo "<td>{$countOfViews}</td>";
            echo "<td>{$countOfStudents}</td>";
        }

        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</section>";

    /**
     * @param moodle_exception $exception
     */
    function error($exception)
    {
        echo "<pre style=\"color: red;\">{$exception->getMessage()}</pre>";
    }
}
?>

<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

<script src="assets/js/inputDate.js"></script>

</body>
</html>