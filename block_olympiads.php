<?php
defined('MOODLE_INTERNAL') || die();
class block_olympiads extends block_base {
    public function init() {
        $this->title = get_string('olympiads', 'block_olympiads');
    }

    public function applicable_formats() {
        return [
            'my' => true, 
            'site' => true,
            'course' => false
        ];
    }
    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $context = context_system::instance();
        $canmanage = has_capability('block/olympiads:manage', $context);
        $canregister = has_capability('block/olympiads:register', $context);

        $this->content->text .= '<p>' . get_string('welcome', 'block_olympiads') . '</p>';

        if ($canmanage) {
            $this->content->text .= '<p><a href="' . new moodle_url('/blocks/olympiads/manage_olympiads.php') . '">' . get_string('manage_olympiads', 'block_olympiads') . '</a></p>';
        }

        if ($canregister) {
            $this->content->text .= '<p><a href="' . new moodle_url('/blocks/olympiads/view_olympiads.php') . '">' . get_string('view_olympiads', 'block_olympiads') . '</a></p>';
        }

        return $this->content;
    }
    public function has_config() {
        return false; 
    }
}