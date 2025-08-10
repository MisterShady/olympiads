<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_login();
$context = context_system::instance();
require_capability('block/olympiads:manage', $context);

$PAGE->set_url('/blocks/olympiads/manage_olympiads.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage_olympiads', 'block_olympiads'));
$PAGE->set_heading(get_string('manage_olympiads', 'block_olympiads'));

// Обработка действия удаления
$action = optional_param('action', '', PARAM_ALPHA);
$olympiadid = optional_param('id', 0, PARAM_INT);

if ($action === 'delete' && $olympiadid) {
    require_sesskey();
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'block_olympiads', 'olympiad_icon', $olympiadid);
    $DB->delete_records('block_olympiad_registrations', ['olympiadid' => $olympiadid]);
    $DB->delete_records('block_olympiads', ['id' => $olympiadid]);
    redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php'), get_string('olympiad_deleted', 'block_olympiads'));
}

$olympiads = $DB->get_records('block_olympiads', null, 'name ASC');

$table = new html_table();
$table->head = [
    get_string('olympiad_icon', 'block_olympiads'),
    get_string('olympiad_name', 'block_olympiads'),
    get_string('olympiad_description', 'block_olympiads'),
    get_string('registration_start', 'block_olympiads'),
    get_string('registration_end', 'block_olympiads'),
    get_string('creator', 'block_olympiads'),
    get_string('actions', 'block_olympiads')
];
$table->data = [];

$fs = get_file_storage();
foreach ($olympiads as $olympiad) {
    $user = $DB->get_record('user', ['id' => $olympiad->creatorid], 'firstname, lastname');
    $creator_name = $user ? fullname($user) : get_string('unknown', 'block_olympiads');

    // Формируем URL иконки
    $iconurl = $OUTPUT->image_url('i/course', 'core')->out(false); 
    if ($olympiad->icon) {
        $files = $fs->get_area_files($context->id, 'block_olympiads', 'olympiad_icon', $olympiad->id, 'id', false);
        $file = reset($files);
        if ($file) {
            $iconurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), '/', $file->get_filename(), false)->out();
        }
    }

    $edit_url = new moodle_url('/blocks/olympiads/edit_olympiad.php', ['id' => $olympiad->id]);
    $delete_url = new moodle_url('/blocks/olympiads/manage_olympiads.php', [
        'action' => 'delete',
        'id' => $olympiad->id,
        'sesskey' => sesskey()
    ]);
    $view_reg_url = new moodle_url('/blocks/olympiads/view_registrations.php', ['id' => $olympiad->id]);

    $actions = $OUTPUT->action_icon($edit_url, new pix_icon('i/edit', get_string('edit')));
    $actions .= $OUTPUT->action_icon($delete_url, new pix_icon('i/delete', get_string('delete')), null, [
        'onclick' => 'return confirm("' . get_string('confirm_delete', 'block_olympiads') . '");'
    ]);
    $actions .= $OUTPUT->action_icon($view_reg_url, new pix_icon('i/report', get_string('view_registrations', 'block_olympiads')));

    $table->data[] = [
        html_writer::img($iconurl, format_string($olympiad->name), ['style' => 'max-width: 50px; max-height: 50px;']),
        format_string($olympiad->name),
        format_text($olympiad->description, FORMAT_PLAIN),
        userdate($olympiad->registration_start),
        userdate($olympiad->registration_end),
        $creator_name,
        $actions
    ];
}

$add_url = new moodle_url('/blocks/olympiads/edit_olympiad.php');

// Рендеринг страницы
echo $OUTPUT->header();
$image_url = $OUTPUT->image_url('image', 'block_olympiads')->out(false);
echo $OUTPUT->render_from_template('block_olympiads/manage_olympiads', [
    'table' => html_writer::table($table),
    'add_url' => $add_url->out(),
    'add_text' => get_string('add_olympiad', 'block_olympiads'),
    'image_url' => $image_url
]);
echo $OUTPUT->footer();