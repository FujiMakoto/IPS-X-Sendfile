//<?php

class hook286 extends _HOOK_CLASS_
{
	/**
	 * Override the \IPS\Http\Ranges' constructor, making a X-Sendfile request instead
	 *
	 * @param    \IPS\File $file     File object we are sending
	 * @param    int       $throttle Throttle speed (kb/sec)
	 */
	public function __construct( $file, $throttle=0 )
	{
		/* If X-Sendfile is disabled / we haven't set it up yet, process a normal Range request instead */
		if ( !\IPS\Settings::i()->xsendfile_enable or !$server = \IPS\Settings::i()->xsendfile_server )
		{
			return call_user_func_array( 'parent::__construct', func_get_args() );
		}

		/* If we're not working with a FileSystem object, we also want to process a normal Range request */
		if ( !($file instanceof \IPS\File\FileSystem) )
		{
			return call_user_func_array( 'parent::__construct', func_get_args() );
		}

		/* Figure our which headers we need to set */
		if ( $server == 'apache' )
		{
			\IPS\Output::i()->sendHeader( 'X-Sendfile: ' .
				$file->configuration['dir'] . '/' . $file->container . '/' . $file->filename
			);
		}

	}

}