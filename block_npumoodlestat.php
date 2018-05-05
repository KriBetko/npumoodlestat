<?php

class block_npumoodlestat extends block_base
{
    function init()
    {
        $this->title = get_string('pluginname', 'block_npumoodlestat');
    }

    function get_content()
    {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = 'Статистика по курсам НПУ';

        $this->content->footer .=
            html_writer::link(
                new moodle_url('/local/npumoodlestat/index.php'),
                html_writer::tag('button', get_string('Статистика за категорiями')),
                ['type' => 'button']
            );

        $this->content->footer .=
            html_writer::link(
                new moodle_url('/local/npumoodlestat/meta.php'),
                html_writer::tag('button', get_string('Статистика за мета курсами')),
                ['type' => 'button']
            );

        return $this->content;
    }
}