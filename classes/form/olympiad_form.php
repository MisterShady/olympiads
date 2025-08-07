<?php
namespace block_olympiads\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class olympiad_form extends moodleform {
    public function definition() {
        global $CFG; 

        $mform = $this->_form;

        // Скрытое поле для ID олимпиады
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // Название олимпиады
        $mform->addElement('text', 'name', get_string('olympiad_name', 'block_olympiads'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setDefault('name', '');

        // Описание олимпиады
        $mform->addElement('editor', 'description', get_string('olympiad_description', 'block_olympiads'), [
            'rows' => 5,
            'cols' => 50
        ]);
        $mform->setType('description', PARAM_RAW);

        // Иконка олимпиады
        $mform->addElement('filemanager', 'icon', get_string('olympiad_icon', 'block_olympiads'), null, [
            'subdirs' => 0,
            'maxbytes' => $CFG->maxbytes,
            'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif'],
            'maxfiles' => 1
        ]);
        $mform->setType('icon', PARAM_INT);

        // Дата и время начала регистрации
        $mform->addElement('date_time_selector', 'registration_start', get_string('registration_start', 'block_olympiads'));
        $mform->addRule('registration_start', get_string('required'), 'required', null, 'client');
        $mform->setDefault('registration_start', time());

        // Дата и время окончания регистрации
        $mform->addElement('date_time_selector', 'registration_end', get_string('registration_end', 'block_olympiads'));
        $mform->addRule('registration_end', get_string('required'), 'required', null, 'client');
        $mform->setDefault('registration_end', time() + 1 * 24 * 3600); 

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Простая проверка: дата окончания должна быть позже начала
        if ($data['registration_end'] <= $data['registration_start']) {
            $errors['registration_end'] = get_string('error_end_before_start', 'block_olympiads');
        }

        return $errors;
    }
}