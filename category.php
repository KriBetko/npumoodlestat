﻿<!DOCTYPE html>
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
        <h1 class="my-title">
            Статистика по категоріям
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
                                <select name="category">
                                    <?php
                                    foreach ($categories as $category) {
                                        echo "<option value='" . $category->id . "'>" . $category->name . "</option>";
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

<?PHP

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo
    '<section class="my-section">',
    '<div class="my-container">',
    ' <h1 class="my-title">Статистика</h1>';

    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    try {
        $coursesInCategory = $DB->get_records('course', ['category' => $_POST['category']]);
    } catch (dml_exception $e) {
        Helper::errorMessage($e->getMessage());
    }

    if ($coursesInCategory) {
        echo '<table class="my-table my-is-bordered my-is-fullwidth">';

        echo '<thead>', '<tr>';
        echo '<th>', 'Назва курсу', '</th>';
        echo '<th>', 'Кiлькiсть вiдвiдувань', "</th>";
        echo '<th>', 'Зареестровано студентiв', "</th>";
        echo '<th>', 'Коеф. вiдвiдуваностi', "</th>";
        echo '<th>', 'Кiлькiсть видiв дiяльностi', "</th>";
        echo '</tr>', '</thead>';

        echo '<tfoot>', '<tr>';
        echo '<th>', 'Назва курсу', '</th>';
        echo '<th>', 'Кiлькiсть вiдвiдувань', "</th>";
        echo '<th>', 'Зареестровано студентiв', "</th>";
        echo '<th>', 'Коеф. вiдвiдуваностi', "</th>";
        echo '<th>', 'Кiлькiсть видiв дiяльностi', "</th>";
        echo '</tr>', '</tfoot>';

        echo '<tbody>';

        foreach ($coursesInCategory as $courseInCategory) {
            /** @noinspection HtmlUnknownTarget */
            echo
            '<tr>',
            '<td>',
                '<a href="' . '/course/view.php?id=' . $courseInCategory->id . '">',
            $courseInCategory->fullname,
            '</a>',
            '</td>';

            try {
                $coursesByFullName = $DB->get_records('course', ['fullname' => $courseInCategory->fullname]);
            } catch (dml_exception $e) {
                Helper::errorMessage($e->getMessage());
            }

            if ($coursesByFullName) {
                $countOfViews = 0;
                $countOfStudents = 0;
                $countOfCourseModules = 0;

                foreach ($coursesByFullName as $courseByFullName) {
                    try {
                        $views = $DB->get_records_sql(
                            'SELECT * FROM mdl_logstore_standard_log WHERE action = ? AND target = ? AND courseid = ? AND timecreated > ? AND timecreated < ?',
                            ['viewed', 'course', $courseByFullName->id, $from, $to]
                        );
                    } catch (dml_exception $e) {
                        Helper::errorMessage($e->getMessage());
                    }

                    if ($views) {
                        $countOfViews = count($views);
                    }

                    echo '<td>', $countOfViews, '</td>';

                    try {
                        $courseContexts = $DB->get_records('context', ['contextlevel' => 50, 'instanceid' => $courseByFullName->id]);
                    } catch (dml_exception $e) {
                        Helper::errorMessage($e->getMessage());
                    }

                    if ($courseContexts) {
                        foreach ($courseContexts as $courseContext) {
                            try {
                                $courseStudents = $DB->get_record_sql(
                                    'SELECT * from mdl_role_assignments WHERE roleid = ? AND contextid = ? AND timemodified > ? AND timemodified < ?', [
                                    5, $courseContext->id, $from, $to
                                ]);
                            } catch (dml_exception $e) {
                                Helper::errorMessage($e->getMessage());
                            }

                            if ($courseStudents) {
                                $countOfStudents = count($courseStudents);
                            }
                        }
                    }

                    echo '<td>', $countOfStudents, '</td>';

                    if ($countOfViews == 0 || $countOfStudents == 0) {
                        echo '<td>0</td>';
                    } else {
                        echo '<td>', round($countOfViews / $countOfStudents), '</td>';
                    }

                    try {
                        $courseModules = $DB->get_records_sql('SELECT * FROM mdl_course_modules WHERE course = ? AND added > ? AND added < ?', [
                            $courseByFullName->id, $from, $to
                        ]);
                    } catch (dml_exception $e) {
                        Helper::errorMessage($e->getMessage());
                    }

                    if ($courseModules) {
                        $countOfCourseModules = count($courseModules);
                    }

                    echo '<td>', $countOfCourseModules, '</td>';
                }
            }

            echo '</tr>';
        }

        echo '</tbody>';
    }

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