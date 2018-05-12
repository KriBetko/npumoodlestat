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

$courses = Helper::getCoursesWithSubCoursesByCategory($DB, $_POST['category'] ? $_POST['category'] : reset($categories)->id);

$groups = Helper::getCourseGroups($DB, $_POST['course'] ? $_POST['course'] : reset($courses)->id);

?>

<section class="my-section">
    <div class="my-container">
        <h1 class="my-title my-is-4">
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
                                <select name="category" <?php if (!$categories) echo 'disabled' ?>>
                                    <?php

                                    foreach ($categories as $categoryId) {
                                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['category'] === $categoryId->id) {
                                            print "<option value=\"{$categoryId->id}\" selected>{$categoryId->name}</option>";
                                        } else {
                                            print "<option value=\"{$categoryId->id}\">{$categoryId->name}</option>";
                                        }
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
                                <select name="course" <?php if (!$courses) echo 'disabled' ?>>
                                    <?php

                                    foreach ($courses as $course) {
                                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['course'] === $course->id) {
                                            print "<option value=\"{$course->id}\" selected>{$course->fullname}</option>";
                                        } else {
                                            print "<option value=\"{$course->id}\">{$course->fullname}</option>";
                                        }
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
                    <label class="my-label">Група</label>
                </div>
                <div class="my-field-body">
                    <div class="my-field">
                        <div class="my-control my-is-expanded">
                            <div class="my-select my-is-fullwidth" id="select-group">
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <select name="group" <?php if (!$groups) echo 'disabled' ?>>
                                    <?php

                                    foreach ($groups as $groupId) {
                                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['group'] === $groupId->id) {
                                            print "<option value=\"{$groupId->id}\" selected>{$groupId->name}</option>";
                                        } else {
                                            print "<option value=\"{$groupId->id}\">{$groupId->name}</option>";
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
    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);
    $categoryId = $_POST['category'];
    $courseId = $_POST['course'];
    $groupId = $_POST['group'];

    $moduleSubCourseId = Helper::getSubCourseModuleId($DB);

    if ($moduleSubCourseId) {
        echo
        "<section class=\"my-section\">",
        "<div class=\"my-container\">";

        $course = Helper::getCourse($DB, $courseId);

        if ($course) {
            echo "<h1 class=\"my-title my-is-4\">Статистика для мета-курсу ",
            "<a href=\"/course/view.php?id=$course->id\">\"$course->fullname\"</a>",
            "</h1>";

            $groupStudents = Helper::getCountOfMembersOnGroup($DB, $groupId, $from, $to);

            echo "<h2 class='my-subtitle'>Кількість студентів: {$groupStudents}</h2>";

            $courseViews = Helper::getCountOfCourseViewsByUsers($DB, $groupId, $from, $to);

            echo "<h2 class='my-subtitle'>Кількість переглядів: {$courseViews}</h2>";

            $coursePopularity = 0;

            if ($courseViews != 0 && $groupStudents != 0) {
                $coursePopularity = round($courseViews / $groupStudents);
            }

            echo "<h2 class='my-subtitle'>Коеф. вiдвiдуваностi: {$coursePopularity}</h2>";

            $courseInfo = Helper::getFastModInfo($course);

            if ($courseInfo) {
                $modulesBySection = [];

                foreach ($courseInfo->get_cms() as $module) {
                    if (!array_key_exists($module->section, $modulesBySection)) {
                        $modulesBySection[$module->section] = [];
                    }

                    if ($module->module === $moduleSubCourseId) {
                        $modulesBySection[$module->section][] = $module;
                    }
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

                        echo '<thead>';
                        echo '<tr>';
                        echo '<th colspan="2" style="border: 0;">', '</th>';
                        echo '<th colspan="4" class="my-has-text-centered">Відвідування</th>';
                        echo '<th colspan="2" class="my-has-text-centered">Види діяльності та ресурси</th>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th>', 'Дисципліна', '</th>';
                        echo '<th>', 'Назва курсу', '</th>';
                        echo '<th>', 'Зареєстровано студентів', '</th>';
                        echo '<th>', 'К-cть груп', '</th>';
                        echo '<th>', 'К-cть вiдвiдувань', '</th>';
                        echo '<th>', 'Коеф. вiдвiдуваностi', '</th>';
                        echo '<th>', 'К-cть видiв дiяльностi', '</th>';
                        echo '<th>', 'К-cть ресурсів', '</th>';
                        echo '</tr>';
                        echo '</thead>';

                        echo '<tfoot>';
                        echo '<tr>';
                        echo '<th>', 'Дисципліна', '</th>';
                        echo '<th>', 'Назва курсу', '</th>';
                        echo '<th>', 'Зареєстровано студентів', '</th>';
                        echo '<th>', 'К-cть груп', '</th>';
                        echo '<th>', 'К-cть вiдвiдувань', '</th>';
                        echo '<th>', 'Коеф. вiдвiдуваностi', '</th>';
                        echo '<th>', 'К-cть видiв дiяльностi', '</th>';
                        echo '<th>', 'К-cть ресурсів', '</th>';
                        echo '</tr>';
                        echo '</tfoot>';

                        echo "<tbody>";

                        /** @var cm_info $module */
                        foreach ($modulesBySection[$section->id] as $module) {
                            $subCourse = Helper::getCourseBySubCourseModule($DB, $module->id, $course->id, $section->section);
                            $subCourseCourse = Helper::getCourse($DB, $subCourse->refcourse);

                            echo "<tr>";

                            echo "<td>", $module->name, "</td>";

                            echo "<td>", "<a href=\"/course/view.php?id={$subCourseCourse->id}\">";

                            echo $subCourseCourse->fullname;

                            echo "</a>", "</td>";

                            $countOfStudents = Helper::getCountOfStudentsOnCourse($DB, $subCourseCourse->id, $from, $to);

                            echo "<td class=\"my-has-text-centered\">{$countOfStudents}</td>";

                            $countOfGroups = Helper::getCountOfCourseGroups($DB, $subCourseCourse->id);

                            echo "<td class=\"my-has-text-centered\">{$countOfGroups}</td>";

                            $countOfViews = Helper::getCountOfCourseViews($DB, $subCourseCourse->id, $from, $to);

                            echo "<td class=\"my-has-text-centered\">{$countOfViews}</td>";

                            if ($countOfViews == 0 || $countOfStudents == 0) {
                                echo '<td class="my-has-text-centered">0</td>';
                            } else {
                                echo '<td class="my-has-text-centered">', round($countOfViews / $countOfStudents), '</td>';
                            }

                            $countOfResources = Helper::getCountOfResourcesAndActivitiesInCourse($DB, $subCourseCourse->id);

                            echo '<td class="my-has-text-centered">', count($countOfResources[0]), '</td>';

                            echo '<td class="my-has-text-centered">', count($countOfResources[1]), '</td>';

                            echo "</tr>";
                        }

                        echo "</tbody>";

                        echo "</table>";
                    } else {
                        echo "<p style=\"margin-bottom: 0\">В секції не знайдено жодного підкурсу</p>";
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