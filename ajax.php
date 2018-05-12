<?php

/** @noinspection PhpIncludeInspection */
require_once('../../config.php');

require_once 'Helper.php';

if ($_POST['category']) {
    $courses = Helper::getCoursesWithSubCoursesByCategory($DB, $_POST['category']);

    header('Content-Type: application/json');
    echo json_encode(['courses' => array_values($courses)], true);
} else if ($_POST['course']) {
    $groups = Helper::getCourseGroups($DB, $_POST['course'], $_POST['from'], $_POST['to']);

    header('Content-Type: application/json');
    echo json_encode(['groups' => array_values($groups)], true);
}
