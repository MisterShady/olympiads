<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Возможность управлять олимпиадами (создание, редактирование, удаление)
    'block/olympiads:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ]
    ],
    // Возможность регистрироваться на олимпиады
    'block/olympiads:register' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW
        ]
    ],
    // Возможность просматривать регистрации на олимпиады
    'block/olympiads:viewregistrations' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ]
    ]
];