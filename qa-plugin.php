<?php


if ( !defined('QA_VERSION') ) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}
	
qa_register_plugin_module('page', 'q2a-send-email.php', 'q2a_send_email_page', 'Send Email');

/*
	Omit PHP closing tag to help avoid accidental output
*/