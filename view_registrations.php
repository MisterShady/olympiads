<?php
require_once(__DIR__ . '/../../config.php');
require_login();
$context = context_system::instance();
require_capability('block/olympiads:viewregistrations', $context);

$olympiadid = required_param('id', PARAM_INT);
$olympiad = $DB->get_record('block_olympiads', ['id' => $olympiadid], '*', MUST_EXIST);

$PAGE->set_url('/blocks/olympiads/view_registrations.php', ['id' => $olympiadid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('view_registrations_for', 'block_olympiads', format_string($olympiad->name)));
$PAGE->set_heading(get_string('view_registrations_for', 'block_olympiads', format_string($olympiad->name)));

$registrations = $DB->get_records('block_olympiad_registrations', ['olympiadid' => $olympiadid]);

$table = new html_table();
$table->head = [
    get_string('fullnameuser'),
    get_string('email')
];
$table->data = [];

foreach ($registrations as $registration) {
    $user = $DB->get_record('user', ['id' => $registration->studentid], 'id, firstname, lastname, email');
    if ($user) {
        $table->data[] = [
            fullname($user),
            $user->email
        ];
    }
}

echo $OUTPUT->header();

if (empty($table->data)) {
    echo $OUTPUT->notification(get_string('no_registrations', 'block_olympiads'), 'info');
} else {
    echo html_writer::table($table);
}

echo $OUTPUT->footer();