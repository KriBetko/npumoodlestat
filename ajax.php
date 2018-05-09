<?php

/** @noinspection PhpIncludeInspection */
require_once('../../config.php');

require_once 'Helper.php';

$courses = Helper::getCoursesWithSubCoursesByCategory($DB, $_POST['category']);

header('Content-Type: application/json');
echo json_encode(['courses' => array_values($courses)], true);