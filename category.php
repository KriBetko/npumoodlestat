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
require(__DIR__ . '/../../config.php');
require('Helper.php');

try {
    echo $OUTPUT->header();
} catch (coding_exception $e) {
    Helper::errorMessage($e->getMessage());
}

try {
    $categories = $DB->get_records('course_categories', null);
} catch (dml_exception $e) {
    Helper::errorMessage($e->getMessage());
}
?>

<section class="my-section">
    <div class="my-container">
        <h2 class="my-title my-is-4">
            Статистика курсів за категоряіми
        </h2>

        <form name="form" method="post">

            <div class="my-field my-is-horizontal">
                <div class="my-field-label my-is-normal">
                    <label class="my-label">Дата</label>
                </div>
                <div class="my-field-body">
                    <div class="my-field">
                        <p class="my-control my-is-expanded">
                            <?php
                            if ($_POST['submit']) {
                                echo '<input class="my-input" type="date" name="from" value="' . $_POST['from'] . '"">';
                            } else {
                                echo '<input class="my-input" type="date" name="from">';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="my-field">
                        <p class="my-control my-is-expanded">
                            <?php
                            if ($_POST['submit']) {
                                echo '<input class="my-input" type="date" name="to" value="' . $_POST['to'] . '"">';
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

            <!--            <label class="checkbox">-->
            <!--                <input type="checkbox" name="subcategory">-->
            <!--                Підкатегорії-->
            <!--            </label>-->

            <div class="my-field">
                <div class="my-control">
                    <input type="submit" name="submit" class="my-button my-is-link">
                </div>
            </div>
        </form>
    </div>
</section>

<?PHP

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo
    '<section class="my-section">',
    '<div class="my-container">',
    ' <h1 class="my-title">Статистика</h1>';

    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    $courses = Helper::getCoursesInCategory($DB, $_POST['category'], $from, $to);

    echo '<table class="my-table my-is-bordered my-is-fullwidth">';

    echo '<thead>';
    echo '<tr>';
    echo '<th style="border: 0;">', '</th>';
    echo '<th colspan="4" class="my-has-text-centered">Відвідування</th>';
    echo '<th colspan="2" class="my-has-text-centered">Види діяльності та ресурси</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<th>', 'Назва курсу', '</th>';

    if ($_POST['subcategory']) {
        echo '<th>', 'Назва категорії', '</th>';
    }

    echo '<th>', 'Зареестровано студентiв', '</th>';
    echo '<th>', 'К-cть груп', '</th>';
    echo '<th>', 'К-cть вiдвiдувань', '</th>';
    echo '<th>', 'Коеф. вiдвiдуваностi', '</th>';
    echo '<th>', 'К-cть видiв дiяльностi', '</th>';
    echo '<th>', 'К-cть ресурсів', '</th>';
    echo '</tr>';
    echo '</thead>';

    echo '<tfoot>';
    echo '<tr>';
    echo '<th>', 'Назва курсу', '</th>';
    echo '<th>', 'Зареестровано студентiв', '</th>';
    echo '<th>', 'К-cть груп', '</th>';
    echo '<th>', 'К-cть вiдвiдувань', '</th>';
    echo '<th>', 'Коеф. вiдвiдуваностi', '</th>';
    echo '<th>', 'К-cть видiв дiяльностi', '</th>';
    echo '<th>', 'К-cть ресурсів', '</th>';
    echo '</tr>';
    echo '</tfoot>';

    echo '<tbody>';

    foreach ($courses as $course) {
        $countOfStudents = Helper::getCountOfStudentsOnCourse($DB, $course->id, $from, $to);
        $countOfViews = Helper::getCountOfCourseViews($DB, $course->id, $from, $to);

        echo
        '<tr>',
        '<td>',
            '<a href="' . '/course/view.php?id=' . $course->id . '">',
        $course->fullname,
        '</a>',
        '</td>';

        echo '<td class="my-has-text-centered">', $countOfStudents, '</td>';

        echo '<td class="my-has-text-centered">', Helper::getCountOfCourseGroups($DB, $course->id, $from, $to), '</td>';

        echo '<td class="my-has-text-centered">', $countOfViews, '</td>';

        if ($countOfViews == 0 || $countOfStudents == 0) {
            echo '<td class="my-has-text-centered">0</td>';
        } else {
            echo '<td class="my-has-text-centered">', round($countOfViews / $countOfStudents), '</td>';
        }

        $countOfResources = Helper::getCountOfResourcesAndActivitiesInCourse($DB, $course->id);

        echo '<td class="my-has-text-centered">', $countOfResources['activities'], '</td>';

        echo '<td class="my-has-text-centered">', $countOfResources['resources'], '</td>';

        echo '</tr>';
    }

    echo '</tbody>';

    echo
    '</div>',
    '</section>';
}
?>

<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

<script src="assets/js/inputDate.js"></script>

</body>
</html>