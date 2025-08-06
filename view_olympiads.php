<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_login();
$context = context_system::instance();
require_capability('block/olympiads:register', $context);

$PAGE->set_url('/blocks/olympiads/view_olympiads.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('view_olympiads', 'block_olympiads'));
$PAGE->set_heading(get_string('view_olympiads', 'block_olympiads'));

// Получаем текущие олимпиады (регистрация открыта)
$currenttime = time();
$olympiads = $DB->get_records_sql(
    'SELECT * FROM {block_olympiads} WHERE registration_start <= ? AND registration_end >= ? ORDER BY name ASC',
    [$currenttime, $currenttime]
);

$fs = get_file_storage();
$olympiad_data = [];
foreach ($olympiads as $olympiad) {
    $iconurl = $OUTPUT->image_url('i/course', 'core')->out(false); // Дефолтная иконка
    if ($olympiad->icon) {
        $files = $fs->get_area_files($context->id, 'block_olympiads', 'olympiad_icon', $olympiad->id, 'id', false);
        $file = reset($files);
        if ($file) {
            $iconurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), '/', $file->get_filename(), false)->out();
        }
    }

    // Создаем объект moodle_url и сразу получаем строку
    $url = new moodle_url('/blocks/olympiads/register_olympiad.php', ['id' => $olympiad->id]);
    $details_url = $url->out();

    $olympiad_data[] = [
        'name' => format_string($olympiad->name),
        'description' => format_text($olympiad->description, FORMAT_PLAIN),
        'registration_start' => userdate($olympiad->registration_start),
        'registration_end' => userdate($olympiad->registration_end),
        'iconurl' => $iconurl,
        'details_url' => $details_url
    ];
}

// Рендеринг страницы
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_olympiads/view_olympiads', [
    'olympiads' => $olympiad_data,
    'defaulticon' => $OUTPUT->image_url('i/course', 'core')->out(false)
]);
echo $OUTPUT->footer();