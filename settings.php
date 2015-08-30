//<?php

/* Supported web servers */
$servers = array( 'apache' => 'Apache', 'nginx' => 'Nginx', 'lighttpd' => 'Lighttpd' );

/* Try and determine our active web server (just used as the default value on setup as a minor convenience) */
$serverSoftware = null;
if ( preg_match( '/^apache/i', $_SERVER['SERVER_SOFTWARE'] ) ) {
	$serverSoftware = 'apache';
} elseif ( preg_match( '/^nginx/i', $_SERVER['SERVER_SOFTWARE'] ) ) {
	$serverSoftware = 'nginx';
} elseif ( preg_match( '/^lighttpd/i', $_SERVER['SERVER_SOFTWARE'] ) ) {
	$serverSoftware = 'lighttpd';
}

$form->addHeader( 'xsendfile_header_general' );  // Settings above headers look bad if they don't have their own
$form->add( new \IPS\Helpers\Form\YesNo( 'xsendfile_enable', \IPS\Settings::i()->xsendfile_enable, false,
	array( 'togglesOn' => array(
		'form_xsendfile_server',
		'form_header_xsendfile_header_server'
	))
));
$form->add( new \IPS\Helpers\Form\YesNo( 'xsendfile_debug_headers', \IPS\Settings::i()->xsendfile_debug_headers ) );

$form->addHeader( 'xsendfile_header_server' );
$form->add( new \IPS\Helpers\Form\Select( 'xsendfile_server', \IPS\Settings::i()->xsendfile_server ?: $serverSoftware,
	true, array(
		'options' => $servers,
		'toggles' => array(
			'nginx' => array(
				'form_xsendfile_internal_uri'
			)
		)
	)
));
$form->add( new \IPS\Helpers\Form\Text( 'xsendfile_internal_uri', \IPS\Settings::i()->xsendfile_internal_uri ) );

if ( $values = $form->values() )
{
	$form->saveAsSettings();
	return TRUE;
}

return $form;