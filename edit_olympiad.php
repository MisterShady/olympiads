<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_login();
$context = context_system::instance();
require_capability('block/olympiads:manage', $context);

$PAGE->set_url('/blocks/olympiads/edit_olympiad.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$olympiadid = optional_param('id', 0, PARAM_INT);
$action = ($olympiadid ? 'edit' : 'add');

$PAGE->set_title(get_string($action . '_olympiad', 'block_olympiads'));
$PAGE->set_heading(get_string($action . '_olympiad', 'block_olympiads'));

// Создаем форму
$form = new block_olympiads\form\olympiad_form(null, ['id' => $olympiadid]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php'));
} elseif ($data = $form->get_data()) {
    global $DB, $USER;

    $record = new stdClass();
    $record->name = $data->name;
    $record->description = $data->description['text'];
    $record->registration_start = $data->registration_start;
    $record->registration_end = $data->registration_end;
    $record->registration_created = time();
    $record->creatorid = $USER->id;

    $fs = get_file_storage();
    $filearea = 'olympiad_icon';
    $component = 'block_olympiads';

    if ($olympiadid) {
        // Обновление
        $record->id = $olympiadid;
        $DB->update_record('block_olympiads', $record);
        $fs->delete_area_files($context->id, $component, $filearea, $olympiadid);
        file_save_draft_area_files($data->icon, $context->id, $component, $filearea, $olympiadid);
        $files = $fs->get_area_files($context->id, $component, $filearea, $olympiadid, 'id', false);
        $file = reset($files);
        $record->icon = $file ? $file->get_id() : null;
        $DB->update_record('block_olympiads', (object)['id' => $olympiadid, 'icon' => $record->icon]);
        redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php'), get_string('olympiad_updated', 'block_olympiads'));
    } else {
        // Создание
        $olympiadid = $DB->insert_record('block_olympiads', $record);
        file_save_draft_area_files($data->icon, $context->id, $component, $filearea, $olympiadid);
        $files = $fs->get_area_files($context->id, $component, $filearea, $olympiadid, 'id', false);
        $file = reset($files);
        $record->icon = $file ? $file->get_id() : null;
        $DB->update_record('block_olympiads', (object)['id' => $olympiadid, 'icon' => $record->icon]);
        redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php'), get_string('olympiad_added', 'block_olympiads'));
    }
}

// Загружаем данные для редактирования
if ($olympiadid) {
    $olympiad = $DB->get_record('block_olympiads', ['id' => $olympiadid], '*', MUST_EXIST);
    $draftitemid = file_get_submitted_draft_itemid('icon');
    file_prepare_draft_area($draftitemid, $context->id, 'block_olympiads', 'olympiad_icon', $olympiadid, [
        'subdirs' => 0,
        'maxbytes' => $CFG->maxbytes,
        'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif'],
        'maxfiles' => 1
    ]);
    $form->set_data([
        'id' => $olympiad->id,
        'name' => $olympiad->name,
        'description' => ['text' => $olympiad->description],
        'icon' => $draftitemid,
        'registration_start' => $olympiad->registration_start,
        'registration_end' => $olympiad->registration_end
    ]);
}

// Рендеринг страницы
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_olympiads/edit_olympiad', [
    'form' => $form->render()
]);
echo $OUTPUT->footer();