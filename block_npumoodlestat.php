<?php

class block_npumoodlestat extends block_base
{
    const moduleName = 'block_npumoodlestat';

    function init()
    {
        try {
            $this->title = get_string('block_title', self::moduleName);
        } catch (coding_exception $e) {
            $this->title = $e->getMessage();
        }
    }

    function get_content()
    {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $this->content->footer .= $this->createButton('/local/npumoodlestat/category.php', 'block_statistics_category');
        $this->content->footer .= $this->createButton('/local/npumoodlestat/course.php', 'block_statistics_course');


        return $this->content;
    }

    /**
     * @param string $url
     * @param string $stringIdentifier
     * @return string
     */
    function createButton($url, $stringIdentifier)
    {
        $button = html_writer::tag('button', $this->getString($stringIdentifier), [
            'style' => 'width: 100%;',
            'class' => 'btn btn-primary'
        ]);
        $link = html_writer::link($this->getUrl($url), $button);
        return html_writer::tag('p', $link);
    }

    /**
     * @param string $stringIdentifier
     * @return string
     */
    function getString($stringIdentifier)
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
    function getUrl($url)
    {
        try {
            return new moodle_url($url);
        } catch (moodle_exception $e) {
            return null;
        }
    }
}