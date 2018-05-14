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
     * @param int $from
     * @param int $to
     * @return array|null
     */
    public static function getRoleAssignments($db, $roleId, $contextId, $from, $to)
    {
        try {
            $roleAssignments = $db->get_records_sql('
                SELECT * FROM mdl_role_assignments 
                WHERE roleid = ?
                AND contextid = ?
                AND timemodified > ?
                AND timemodified < ?', [
                $roleId,
                $contextId,
                $from,
                $to
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
     * @return array
     */
    public static function getCourseModules($db, $courseId)
    {
        try {
            $modules = $db->get_records('course_modules', [
                'course' => $courseId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return [];
        }

        return $modules;
    }

    /**
     * @param moodle_database $db
     * @param int $moduleId
     * @return stdClass|null
     */
    public static function getCourseModule($db, $moduleId)
    {
        try {
            $module = $db->get_record('modules', [
                'id' => $moduleId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return null;
        }

        return $module;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @param int $from
     * @param int $to
     * @return int
     */
    public static function getCountOfCourseViews($db, $courseId, $from, $to)
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
                    $from,
                    $to
                ]
            );
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return 0;
        }

        return count($views);
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
     * @param int $courseModuleId
     * @param int $courseId
     * @param int $sectionNum
     * @return stdClass|null
     */
    public static function getSubCourseModuleFromId($courseModuleId, $courseId, $sectionNum)
    {
        try {
            return get_coursemodule_from_id('subcourse', $courseModuleId, $courseId, $sectionNum);
        } catch (coding_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }
    }

    /**
     * @param moodle_database $db
     * @param int $courseModuleId
     * @param int $courseId
     * @param int $sectionNum
     * @return stdClass|null
     */
    public static function getCourseBySubCourseModule($db, $courseModuleId, $courseId, $sectionNum)
    {
        $module = self::getSubCourseModuleFromId($courseModuleId, $courseId, $sectionNum);

        if ($module) {
            try {
                return $db->get_record('subcourse', ['id' => $module->instance]);
            } catch (dml_exception $e) {
                Helper::errorMessage($e->getMessage());
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return stdClass|null
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
     * @param moodle_database $db
     * @return array|null
     */
    public static function getCategories($db)
    {
        try {
            $course = $db->get_records('course_categories', []);
        } catch (dml_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }

        return $course;
    }

    /**
     * @param moodle_database $db
     * @param int $categoryId
     * @return array|null
     */
    public static function getCourses($db, $categoryId)
    {
        try {
            $course = $db->get_records('course', ['category' => $categoryId]);
        } catch (dml_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }

        return $course;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return array|null
     */
    public static function getCourseSections($db, $courseId)
    {
        try {
            $sections = $db->get_records_sql("SELECT * FROM mdl_course_sections WHERE course = ? ORDER BY section ASC", [
                $courseId
            ]);
        } catch (dml_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }

        return $sections;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @param int $sectionId
     * @return array|null
     */
    public static function getCourseModulesBySection($db, $courseId, $sectionId)
    {
        try {
            $course = $db->get_records('course_modules', ['course' => $courseId, 'section' => $sectionId]);
        } catch (dml_exception $e) {
            Helper::errorMessage($e->getMessage());
            return null;
        }

        return $course;
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @param int $from
     * @param int $to
     * @return int
     */
    public static function getCountOfStudentsOnCourse($db, $courseId, $from, $to)
    {
        $subCourseContext = Helper::getContext($db, $courseId);

        if ($subCourseContext) {
            $studentRoleId = Helper::getStudentRoleId($db);
            $subCourseStudents = Helper::getRoleAssignments($db, $studentRoleId, $subCourseContext->id, $from, $to);

            if ($subCourseStudents) {
                return count($subCourseStudents);
            }
        }

        return 0;
    }

    /**
     * @param $course
     * @return course_modinfo|null
     */
    public static function getFastModInfo($course)
    {
        try {
            return get_fast_modinfo($course);
        } catch (moodle_exception $e) {
            return null;
        }
    }

    public static function getCoursesWithSubCoursesByCategory($db, $categoryId)
    {
        $result = [];

        $courses = self::getCourses($db, $categoryId);

        $subCourseModuleId = self::getSubCourseModuleId($db);

        foreach ($courses as $course) {
            $modInfo = Helper::getFastModInfo($course);

            foreach ($modInfo->get_cms() as $module) {
                if ($module->module === $subCourseModuleId) {
                    array_push($result, $course);
                    break;
                }
            }
        }

        return $result;
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
            Helper::errorMessage($e->getMessage());
            return null;
        }
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return array|null
     */
    public static function getCourseGroups($db, $courseId)
    {
        try {
            return $db->get_records_sql('
                SELECT * FROM mdl_groups 
                WHERE courseid = ?', [
                $courseId
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return [];
        }
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return int
     */
    public static function getCountOfCourseGroups($db, $courseId)
    {
        $groups = self::getCourseGroups($db, $courseId);
        return $groups ? count($groups) : 0;
    }

    /**
     * @param moodle_database $db
     * @param int $categoryId
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function getCoursesInCategory($db, $categoryId, $from, $to)
    {
        try {
            return $db->get_records_sql('
                SELECT * FROM mdl_course 
                WHERE category = ?
                AND timecreated > ?
                AND timecreated < ?', [
                $categoryId,
                $from,
                $to
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return [];
        }
    }

    /**
     * @param moodle_database $db
     * @param int $groupId
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function getGroupMembers($db, $groupId, $from, $to)
    {
        try {
            return $db->get_records_sql('
                SELECT * FROM mdl_groups_members 
                WHERE groupid = ?
                AND timeadded > ?
                AND timeadded < ?', [
                $groupId,
                $from,
                $to
            ]);
        } catch (dml_exception $e) {
            self::errorMessage($e->getMessage());
            return [];
        }
    }

    /**
     * @param moodle_database $db
     * @param int $groupId
     * @param int $from
     * @param int $to
     * @return int
     */
    public static function getCountOfMembersOnGroup($db, $groupId, $from, $to)
    {
        $members = self::getGroupMembers($db, $groupId, $from, $to);
        return $members ? count($members) : 0;
    }

    /**
     * @param moodle_database $db
     * @param int $groupId
     * @param int $from
     * @param int $to
     * @return int
     */
    public static function getCountOfCourseViewsByUsers($db, $groupId, $from, $to)
    {
        $users = self::getGroupMembers($db, $groupId, $from, $to);

        if ($users) {
            $ids = [];

            foreach ($users as $user) {
                $ids[] = $user->id;
            }

            try {
                return count($db->get_records_sql("
                SELECT * FROM mdl_logstore_standard_log 
                WHERE action = 'viewed'
                AND target = 'course'
                AND userid IN (" . implode(',', $ids) . ")
                AND timecreated > ?
                AND timecreated < ?", [
                    $ids,
                    $from,
                    $to
                ]));
            } catch (dml_exception $e) {
                self::errorMessage($e->getMessage());
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * @param moodle_database $db
     * @param int $courseId
     * @return array
     */
    public static function getCountOfResourcesAndActivitiesInCourse($db, $courseId)
    {
        $course = self::getCourse($db, $courseId);
        $courseModules = self::getCourseModules($db, $courseId);

        $registeredModules = get_module_types_names();

        $modNames = [];

        foreach ($registeredModules as $key => $registeredModule) {
            foreach ($courseModules as $courseModule) {
                $modInfo = self::getCourseModule($db, $courseModule->module);

                if ($modInfo) {
                    if ($key === $modInfo->name) {
                        $modNames[$modInfo->name] = $registeredModule;
                        break;
                    }
                }
            }
        }

        $modulesMetaData = get_module_metadata($course, $modNames);

        $activities = 0;
        $resources = 0;

        foreach ($courseModules as $courseModule) {
            $modInfo = self::getCourseModule($db, $courseModule->module);
            foreach ($modulesMetaData as $moduleMetaData) {
                if ($modInfo->name === $moduleMetaData->name) {
                    if ($moduleMetaData->archetype === MOD_CLASS_ACTIVITY) {
                        $activities++;
                    } else if ($moduleMetaData->archetype == MOD_ARCHETYPE_RESOURCE) {
                        $resources++;
                    }
                }
            }
        }

        return [
            'activities' => $activities,
            'resources' => $resources
        ];
    }
}