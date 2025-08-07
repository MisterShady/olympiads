<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_login();
$context = context_system::instance();
require_capability('block/olympiads:register', $context);

$PAGE->set_url('/blocks/olympiads/register_olympiad.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$olympiadid = optional_param('id', 0, PARAM_INT);
if (!$olympiadid) {
    throw new moodle_exception('invalidolympiadid', 'block_olympiads');
}

$olympiad = $DB->get_record('block_olympiads', ['id' => $olympiadid], '*', MUST_EXIST);

$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $currenttime = time();
    $is_open = ($currenttime >= $olympiad->registration_start && $currenttime <= $olympiad->registration_end);
    $already_registered = $DB->record_exists('block_olympiad_registrations', [
        'olympiadid' => $olympiadid,
        'studentid' => $USER->id
    ]);

    if (!$is_open) {
        redirect(new moodle_url('/blocks/olympiads/register_olympiad.php', ['id' => $olympiadid]), 
                get_string('registration_closed', 'block_olympiads'), 
                null, 
                \core\output\notification::NOTIFY_ERROR);
    } elseif ($already_registered) {
        redirect(new moodle_url('/blocks/olympiads/register_olympiad.php', ['id' => $olympiadid]), 
                get_string('already_registered', 'block_olympiads'), 
                null, 
                \core\output\notification::NOTIFY_WARNING);
    } else {
        $record = new stdClass();
        $record->olympiadid = $olympiadid;
        $record->studentid = $USER->id;
        $record->dateregistered = $currenttime;
        try {
            $DB->insert_record('block_olympiad_registrations', $record);
            redirect(new moodle_url('/blocks/olympiads/register_olympiad.php', ['id' => $olympiadid]), 
                    get_string('registration_success', 'block_olympiads'), 
                    null, 
                    \core\output\notification::NOTIFY_SUCCESS);
        } catch (dml_exception $e) {
            redirect(new moodle_url('/blocks/olympiads/register_olympiad.php', ['id' => $olympiadid]), 
                    'Ошибка при регистрации. Пожалуйста, попробуйте снова.', 
                    null, 
                    \core\output\notification::NOTIFY_ERROR);
        }
    }
}

$currenttime = time();
$is_open = ($currenttime >= $olympiad->registration_start && $currenttime <= $olympiad->registration_end);
$already_registered = $DB->record_exists('block_olympiad_registrations', [
    'olympiadid' => $olympiadid,
    'studentid' => $USER->id
]);

$fs = get_file_storage();
$iconurl = $OUTPUT->image_url('i/course', 'core')->out(false);
if ($olympiad->icon) {
    $files = $fs->get_area_files($context->id, 'block_olympiads', 'olympiad_icon', $olympiad->id, 'id', false);
    $file = reset($files);
    if ($file) {
        $iconurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), '/', $file->get_filename(), false)->out();
    }
}

$PAGE->set_title(get_string('olympiad_details', 'block_olympiads', $olympiad->name));
$PAGE->set_heading(get_string('olympiad_details', 'block_olympiads', $olympiad->name));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_olympiads/register_olympiad', [
    'name' => format_string($olympiad->name),
    'description' => format_text($olympiad->description, FORMAT_PLAIN),
    'registration_start' => userdate($olympiad->registration_start),
    'registration_end' => userdate($olympiad->registration_end),
    'iconurl' => $iconurl,
    'defaulticon' => $OUTPUT->image_url('i/course', 'core')->out(false),
    'can_register' => $is_open && !$already_registered,
    'sesskey' => sesskey(),
    'olympiadid' => $olympiadid,
    'already_registered' => $already_registered,
    'registration_closed' => !$is_open
]);
echo $OUTPUT->footer();