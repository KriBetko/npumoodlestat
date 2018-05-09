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

require_once 'Helper.php';

try {
    echo $OUTPUT->header();
} catch (coding_exception $e) {
    echo "<pre style=\"color: red;\">{\$e->getMessage()}</pre>";
}

$categories = Helper::getCategories($DB);

$courses = Helper::getCoursesWithSubCoursesByCategory($DB, reset($categories)->id);

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
                    <label class="my-label">Категорія</label>
                </div>
                <div class="my-field-body">
                    <div class="my-field">
                        <div class="my-control my-is-expanded">
                            <div class="my-select my-is-fullwidth">
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <select name="category">
                                    <?php

                                    foreach ($categories as $category) {
                                        print "<option value=\"{$category->id}\">{$category->name}</option>";
                                    }

                                    ?>
                                </select>
                            </div>
                        </div>
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
                            <div class="my-select my-is-fullwidth" id="select-course">
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <select name="course">
                                    <?php

                                    foreach ($courses as $course) {
                                        print "<option value=\"{$course->id}\">{$course->fullname}</option>";
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
    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    $moduleSubCourseId = Helper::getSubCourseModuleId($DB);

    if ($moduleSubCourseId) {
        echo
        "<section class=\"my-section\">",
        "<div class=\"my-container\">";

        $course = Helper::getCourse($DB, $_POST['course']);

        if ($course) {
            echo "<p class=\"my-title my-is-1 my-is-spaced\">Статистика для мета-курсу ",
            "<a href=\"/course/view.php?id=$course->id\">\"$course->fullname\"</a>",
            "</p>";

            $courseStudents = Helper::getCountOfStudentsOnCourse($DB, $course->id, $from, $to);

            echo "<p class='my-subtitle my-is-3'>Кількість студентів: {$courseStudents}</p>";

            $courseViews = Helper::getCountOfCourseViews($DB, $course->id, $from, $to);

            echo "<p class='my-subtitle my-is-3'>Кількість переглядів: {$courseViews}</p>";

            $coursePopularity = 0;

            if ($courseViews != 0 && $courseStudents != 0) {
                $coursePopularity = round($courseViews / $courseStudents);
            }

            echo "<p class='my-subtitle my-is-3'>Коеф. вiдвiдуваностi: {$coursePopularity}</p>";

            $courseInfo = Helper::getFastModInfo($course);

            if ($courseInfo) {
                $modulesBySection = [];

                foreach ($courseInfo->get_cms() as $module) {
                    if (!array_key_exists($module->section, $modulesBySection)) {
                        $modulesBySection[$module->section] = [];
                    }

                    array_push($modulesBySection[$module->section], $module);

                }

                foreach ($courseInfo->get_section_info_all() as $section) {
                    echo "<article class=\"my-message my-is-primary\">";

                    if ($section->section > 0) {
                        echo "<div class=\"my-message-header\">";

                        $url = "/course/view.php?id=" . $course->id . "#section-" . $section->id;

                        echo "<a href='{$url}'>";

                        echo $section->name ? $section->name : "Тема" . $section->section;

                        echo "</a>";

                        echo "</div>";
                    }

                    echo "<div class=\"my-message-body\">";

                    if (count($modulesBySection[$section->id]) > 0) {
                        echo "<table class=\"my-table my-is-bordered my-is-fullwidth\">";

                        echo "<thead><tr>";
                        echo "<th>Тип модулю</th>";
                        echo "<th>Назва модулю</th>";

                        echo "<tbody>";

                        /** @var cm_info $module */
                        foreach ($modulesBySection[$section->id] as $module) {
                            echo "<tr>";

                            echo "<td>", $module->modfullname, "</td>";

                            echo "<td>", "<a href=\"/mod/{$module->modname}/view.php?id={$module->id}\">";

                            echo $module->name;

                            echo "</a>", "</td>";

                            echo "</tr>";
                        }

                        echo "</tbody>";

                        echo "</table>";
                    } else {
                        echo "<p>В секції не знайдено жодного модуля</p>";
                    }

                    echo "</div>";

                    echo "</article>";
                }
            }
        }

        echo "</div>";
        echo "</section>";
    } else {
        Helper::errorMessage('Модуль "subcourse" не знайдено у вашый системы Moodle');
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