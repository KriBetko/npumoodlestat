<?php
/**
 * Report settings
 *
 * @package    report_npumoodlestat
 * @copyright  2018 Ivan Kolodrivskiy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once('Helper.php');

/** @var navigation_node $ADMIN */
$ADMIN->add('reports', new admin_externalpage(
        'report_npumoodlestat',
        Helper::getString('block_title') . ' - ' . Helper::getString('block_statistics_category'),
        Helper::getUrl('/report/npumoodlestat/category.php'))
);

$ADMIN->add('reports', new admin_externalpage(
        'report_npumoodlestat',
        Helper::getString('block_title') . ' - ' . Helper::getString('block_statistics_course'),
        Helper::getUrl('/report/npumoodlestat/meta.php'))
);

$settings = null;
