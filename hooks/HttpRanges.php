//<?php

class hook15 extends _HOOK_CLASS_
{
	/**
	 * Override the \IPS\Http\Ranges' constructor, preparing for a X-Sendfile request instead
	 *
	 * @param   \IPS\File $file		File object we are sending
	 * @param   int       $throttle	Throttle speed (kb/sec)
	 *
	 * @throws	\Whoops\Exception\ErrorException
	 */
	public function __construct( $file, $throttle=0 )
	{
		/* If X-Sendfile is disabled / we haven't set it up yet, process a normal filesystem request instead */
		if ( !\IPS\Settings::i()->xsendfile_enable or !$server = \IPS\Settings::i()->xsendfile_server )
		{
			return call_user_func_array( 'parent::__construct', func_get_args() );
		}

		/* Pass off the the X-Sendfile hooked handler */
		$file->printFile( null, null, $throttle );
	}

}