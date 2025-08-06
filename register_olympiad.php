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
    'defaulticon' => $OUTPUT->image_url('i/course', 'core')->out(false)
]);
echo $OUTPUT->footer();