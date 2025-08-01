<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_block_olympiads_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

      if ($oldversion < 2025073105) {

        // Define table block_olympiads to be created.
        $table = new xmldb_table('block_olympiads');

        // Adding fields to table block_olympiads.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('registration_start', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('registration_end', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('registration_created', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('creatorid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_olympiads.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('creatorid', XMLDB_KEY_FOREIGN, ['creatorid'], 'user', ['id']);

        // Conditionally launch create table for block_olympiads.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_olympiad_registrations to be created.
        $table = new xmldb_table('block_olympiad_registrations');

        // Adding fields to table block_olympiad_registrations.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('olympiadid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dateregistered', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_olympiad_registrations.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('olympiadid', XMLDB_KEY_FOREIGN, ['olympiadid'], 'block_olympiads', ['id']);
        $table->add_key('studentid', XMLDB_KEY_FOREIGN, ['studentid'], 'user', ['id']);

        // Conditionally launch create table for block_olympiad_registrations.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Olympiads savepoint reached.
        upgrade_block_savepoint(true, 2025073105, 'olympiads');
    }

    // Everything has succeeded to here. Return true.
    return true;
}