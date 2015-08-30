//<?php

class hook286 extends _HOOK_CLASS_
{
	/**
	 * Override the \IPS\Http\Ranges' constructor, making a X-Sendfile request instead
	 *
	 * @param    \IPS\File $file     File object we are sending
	 * @param    int       $throttle Throttle speed (kb/sec)
	 *
	 * @throws \Whoops\Exception\ErrorException
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
		elseif ( $server == 'nginx' )
		{
			\IPS\Output::i()->sendHeader( 'X-Accel-Redirect: ' .
				'/' . \IPS\Settings::i()->xsendfile_internal_uri . '/' . $file->container . '/' . $file->namename
			);

			/* Throttling is only supported with Nginx */
			if ( $throttle )
			{
				\IPS\Output::i()->sendHeader( 'X-Accel-Limit-Rate: ' . $throttle * 1000 );  // Throttle is in bytes
			}
		}
		elseif ( $server == 'lighttpd' )
		{
			\IPS\Output::i()->sendHeader( 'X-LIGHTTPD-send-file: ' .
				$file->configuration['dir'] . '/' . $file->container . '/' . $file->filename
			);
		}
		else
		{
			/* What in the world have you done if you reached this point? */
			throw new \Whoops\Exception\ErrorException( 'Unrecognized X-Sendfile server' );
		}

		/* Generic file headers */
		\IPS\Output::i()->sendHeader( 'Content-Type: ' . \IPS\File::getMimeType( $file->originalFilename ) );
		\IPS\Output::i()->sendHeader( 'Content-Disposition: ' . \IPS\Output::getContentDisposition( 'attachment', $file->originalFilename ) );
		\IPS\Output::i()->sendHeader( "Content-Length: " . $file->filesize() );
	}

}