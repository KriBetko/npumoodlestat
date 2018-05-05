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
    echo '<p style="color: red;">' . $e->getMessage() . '</p>';
}

try {
    $categories = $DB->get_records('course_categories', null);
} catch (dml_exception $e) {
    echo '<p style="color: red;">' . $e->getMessage() . '</p>';
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

<section class="my-section">
    <div class="my-container">
        <h1 class="my-title">
            Статистика
        </h1>
    </div>
</section>

<?PHP

var_dump($_POST);

if ($_POST['submit']) {
    $from = strtotime($_POST['from']);
    $to = strtotime($_POST['to']);

    try {
        $coursesInCategory = $DB->get_records('course', ['category' => $_POST['category']]);
    } catch (dml_exception $e) {
        echo error($e->getMessage());
    }

    if ($coursesInCategory) {
        echo '<table class="my-table my-is-bordered">';

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
            echo '<tr>', '<td>', $courseInCategory->fullname, '</td>';

            try {
                $coursesByFullName = $DB->get_records('course', ['fullname' => $courseInCategory->fullname]);
            } catch (dml_exception $e) {
                echo error($e->getMessage());
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
                        echo error($e->getMessage());
                    }

                    if ($views) {
                        $countOfViews = count($views);
                    }

                    echo '<td>', $countOfViews, '</td>';

                    try {
                        $courseContexts = $DB->get_records('context', ['contextlevel' => 50, 'instanceid' => $courseByFullName->id]);
                    } catch (dml_exception $e) {
                        echo error($e->getMessage());
                    }

                    if ($courseContexts) {
                        foreach ($courseContexts as $courseContext) {
                            try {
                                $courseStudents = $DB->get_records('role_assignments', ['roleid' => 5, 'contextid' => $courseContext->id]);
                            } catch (dml_exception $e) {
                                echo error($e->getMessage());
                            }

                            if ($courseStudents) {
                                $countOfStudents = count($courseStudents);
                            }
                        }
                    }

                    echo '<td>', $countOfStudents, '</td>';
                    echo '<td>', round($countOfViews / $countOfStudents), '</td>';

                    try {
                        $courseModules = $DB->get_records_sql('SELECT * FROM mdl_course_modules WHERE course = ?', [$courseByFullName->id]);
                    } catch (dml_exception $e) {
                        echo error($e->getMessage());
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

    function error($message)
    {
        return '<td style="color: red;">' . $message . '</td>';
    }
}
?>

<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

<script src="assets/js/index.js"></script>

</body>
</html>