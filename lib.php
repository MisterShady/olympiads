<?php
defined('MOODLE_INTERNAL') || die();

function block_olympiads_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_SYSTEM && $context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    if ($filearea !== 'olympiad_icon') {
        send_file_not_found();
    }

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_olympiads', 'olympiad_icon', $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}