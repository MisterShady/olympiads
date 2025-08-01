<?php
require_once(__DIR__ . '/../../config.php');
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
    redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php')); // Вернуться к списку олимпиад
} elseif ($data = $form->get_data()) {
    global $DB, $USER;

    $record = new stdClass();
    $record->name = $data->name;
    $record->description = $data->description['text'];
    $record->registration_start = $data->registration_start;
    $record->registration_end = $data->registration_end;
    $record->registration_created = time();
    $record->creatorid = $USER->id;

    if ($olympiadid) {
        // Обновление существующей записи
        $record->id = $olympiadid;
        $DB->update_record('block_olympiads', $record);
        redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php'), get_string('olympiad_updated', 'block_olympiads'));
    } else {
        // Создание новой записи
        $DB->insert_record('block_olympiads', $record);
        redirect(new moodle_url('/blocks/olympiads/manage_olympiads.php'), get_string('olympiad_added', 'block_olympiads'));
    }
}

// Загружаем данные для редактирования
if ($olympiadid) {
    $olympiad = $DB->get_record('block_olympiads', ['id' => $olympiadid], '*', MUST_EXIST);
    $form->set_data([
        'id' => $olympiad->id,
        'name' => $olympiad->name,
        'description' => ['text' => $olympiad->description],
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