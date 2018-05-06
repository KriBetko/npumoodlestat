<?php

class Helper
{
    const moduleName = 'report_npumoodlestat';

    public static function dump($variable)
    {
        echo "<pre>", var_export($variable), "</pre>";
    }

    public static function dumpWithName($varName, $variable)
    {
        echo "<span>$varName: </span>", "<pre>", var_export($variable), "</pre>";
    }

    /**
     * @param string $message
     */
    public static function errorMessage($message)
    {
        echo "<div class=\"my-notification my-is-danger\">$message</div>";
    }

    /**
     * @param moodle_database $db
     * @return int|null
     */
    public static function getStudentRoleId($db)
    {
        try {
            $role = $db->get_record('role', ['archetype' => 'student']);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $role ? $role->id : $role;
    }

    /**
     * @param moodle_database $db
     * @return int|null
     */
    public static function getSubCourseModuleId($db)
    {
        try {
            $module = $db->get_record('modules', ['name' => 'subcourse']);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $module ? $module->id : $module;
    }

    /**
     * @param moodle_database $db
     * @param int $roleId
     * @param int $contextId
     * @return array|null
     */
    public static function getRoleAssignments($db, $roleId, $contextId)
    {
        try {
            $roleAssignments = $db->get_records('role_assignments', [
                'roleid' => $roleId,
                'contextid' => $contextId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $roleAssignments;
    }

    /**
     * @param moodle_database $db
     * @param int $instanceId
     * @return context|null
     */
    public static function getContext($db, $instanceId)
    {
        try {
            $context = $db->get_record('context', [
                'contextlevel' => '50',
                'instanceid' => $instanceId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $context;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @param int $moduleId
     * @return array|null
     */
    public static function getCourseModulesByModuleId($db, $courseId, $moduleId)
    {
        try {
            $modules = $db->get_records('course_modules', [
                'course' => $courseId,
                'module' => $moduleId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $modules;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return array|null
     */
    public static function getCourseModules($db, $courseId)
    {
        try {
            $modules = $db->get_records('course_modules', [
                'course' => $courseId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $modules;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @param int $timeFrom
     * @param int $timeTo
     * @return array|null
     */
    public static function getLogStoreStandardLog($db, $courseId, $timeFrom, $timeTo)
    {
        try {
            $views = $db->get_records_sql(
                "SELECT * FROM mdl_logstore_standard_log 
                              WHERE action = 'viewed'
                              AND target = 'course' 
                              AND courseid = ? 
                              AND timecreated > ? 
                              AND timecreated < ?",
                [
                    $courseId,
                    $timeFrom,
                    $timeTo
                ]
            );
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $views;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return array|null
     */
    public static function getSubCourses($db, $courseId)
    {
        try {
            $subCourses = $db->get_records('subcourse', ['course' => $courseId]);
        } catch (dml_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }

        return $subCourses;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return object|null
     */
    public static function getCourse($db, $courseId)
    {
        try {
            $course = $db->get_record('course', ['id' => $courseId]);
        } catch (dml_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }

        return $course;
    }

    /**
     * @param string $stringIdentifier
     * @return string
     */
    public static function getString($stringIdentifier)
    {
        try {
            return get_string($stringIdentifier, self::moduleName);
        } catch (coding_exception $e) {
            return '';
        }
    }

    /**
     * @param string $url
     * @return moodle_url|null
     */
    public static function getUrl($url)
    {
        try {
            return new moodle_url($url);
        } catch (moodle_exception $e) {
            return null;
        }
    }
}