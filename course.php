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
            Статистика по курсам
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
                                <select name="course">
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

require "Helper.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    $moduleSubCourseId = Helper::getSubCourseModuleId($DB);

    if ($moduleSubCourseId) {
        echo
        "<section class=\"my-section\">",
        "<div class=\"my-container\">";

        $course = Helper::getCourse($DB, $_POST['course']);

        if ($course) {
            echo "<h1 class=\"my-title\">Статистика для курсу ",
            "<a href=\"/course/view.php?id=$course->id\">\"$course->fullname\"</a>",
            "</h1>";
        }

        $subCourses = Helper::getSubCourses($DB, $_POST['course']);

        if ($subCourses) {
            echo "<table class=\"my-table my-is-bordered my-is-fullwidth\">";

            echo "<thead><tr>";
            echo "<th>Назва пiкурсу</th>";
            echo "<th>Кiлькiсть вiдвiдувань</th>";
            echo "<th>Зареестровано студентiв</th>";
            echo "<th>Коеф. вiдвiдуваностi</th>";
            echo "<th>Кiлькiсть видiв дiяльностi</th>";

            echo "</tr></thead>";

            echo "<tfoot><tr>";
            echo "<th>Назва пiкурсу</th>";
            echo "<th>Кiлькiсть вiдвiдувань</th>";
            echo "<th>Зареестровано студентiв</th>";
            echo "<th>Коеф. вiдвiдуваностi</th>";
            echo "<th>Кiлькiсть видiв дiяльностi</th>";
            echo "</tr></tfoot>";

            echo "<tbody>";

            $studentRoleId = Helper::getStudentRoleId($DB);

            foreach ($subCourses as $subCourse) {
                $countOfViews = 0;
                $countOfStudents = 0;
                $countOfCourseModules = 0;

                echo "<tr>", "<td><a href=\"/course/view.php?id={$subCourse->refcourse}\">{$subCourse->name}</a></td>";

                $subCourseContext = Helper::getContext($DB, $subCourse->refcourse);

                if ($subCourseContext) {
                    $subCourseStudents = Helper::getRoleAssignments($DB, $studentRoleId, $subCourseContext->id);

                    if ($subCourseStudents) {
                        $countOfStudents = count($subCourseStudents);
                    }
                }

                $views = Helper::getLogStoreStandardLog($DB, $subCourse->refcourse, $from, $to);

                $countOfViews = count($views);

                $courseModules = Helper::getCourseModules($DB, $subCourse->refcourse);

                if ($courseModules) {
                    $countOfCourseModules = count($courseModules);
                }

                echo "<td>{$countOfViews}</td>";
                echo "<td>{$countOfStudents}</td>";

                if ($countOfViews == 0 || $countOfStudents == 0) {
                    echo '<td>0</td>';
                } else {
                    echo '<td>', round($countOfViews / $countOfStudents), '</td>';
                }

                echo '<td>', $countOfCourseModules, '</td>';

                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        }

        echo "</div>";
        echo "</section>";
    } else {
        Helper::errorMessage('Module "subcourse" nor fount on your Moodle');
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