<?php
$functions = [
    'local_oulearn_add_course_enrolment_method' => [
        'classname' => 'local_oulearn_external',
        'methodname' => 'add_course_enrolment_method',
        'classpath' => 'local/oulearn/externallib.php',
        'description' => 'Add new instance enrollment method on course',
        'ajax' => true,
        'type' => 'write',
    ],

    'local_oulearn_remove_course_enrolment_method' => [
        'classname' => 'local_oulearn_external',
        'methodname' => 'remove_course_enrolment_method',
        'classpath' => 'local/oulearn/externallib.php',
        'description' => 'removeinstance enrollment method on course',
        'ajax' => true,
        'type' => 'write',
    ],

    'local_oulearn_update_course_enrolment_method' => [
        'classname' => 'local_oulearn_external',
        'methodname' => 'update_course_enrolment_method',
        'classpath' => 'local/oulearn/externallib.php',
        'description' => 'update instance enrollment method on course',
        'ajax' => true,
        'type' => 'write',
    ],
];