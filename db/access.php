<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/user_queue_mail:cohort_sync' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'view',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            //'manager' => CAP_ALLOW,
        )
    ),
	
	
);
