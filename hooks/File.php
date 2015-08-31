//<?php

class hook287 extends _HOOK_CLASS_
{

	/**
	 * Hand off the file transfer to the web server using X-Sendfile
	 *
	 * @param   int|null $start     Start point to print from (for ranges)
	 * @param   int|null $length	Length to print to (for ranges)
	 * @param   int|null $throttle	Throttle speed
	 *
	 * @throws	\Whoops\Exception\ErrorException
	 * @return	mixed
	 */
	public function printFile( $start=NULL, $length=NULL, $throttle=NULL )
	{
		/* Should we send debug headers? */
		if ( \IPS\Settings::i()->xsendfile_debug_headers )
		{
			\IPS\Output::i()->sendHeader( 'X-Sendfile-Debug-Enabled: ' . (int) \IPS\Settings::i()->xsendfile_enable );
			\IPS\Output::i()->sendHeader( 'X-Sendfile-Debug-Server: ' . \IPS\Settings::i()->xsendfile_server );
		}

		/* If X-Sendfile is disabled / we haven't set it up yet, process a normal filesystem request instead */
		if ( !\IPS\Settings::i()->xsendfile_enable or !$server = \IPS\Settings::i()->xsendfile_server )
		{
			return call_user_func_array( 'parent::printFile', func_get_args() );
		}

		/* Figure our which headers we need to set */
		if ( $server == 'apache' )
		{
			\IPS\Output::i()->sendHeader( 'X-Sendfile: ' .
				$path = $this->configuration['dir'] . '/' . $this->container . '/' . $this->filename
			);
		}
		elseif ( $server == 'nginx' )
		{
			\IPS\Output::i()->sendHeader( 'X-Accel-Redirect: ' .
				$path = '/' . ( \IPS\Settings::i()->xsendfile_custom_uri
					? \IPS\Settings::i()->xsendfile_internal_uri
					: $this->configuration['url'] )
					. '/' . $this->container . '/' . $this->filename
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
				$path = $this->configuration['dir'] . '/' . $this->container . '/' . $this->filename
			);
		}
		else
		{
			/* What in the world have you done if you reached this point? */
			throw new \Whoops\Exception\ErrorException( 'Unrecognized X-Sendfile server' );
		}

		/* Additional debug headers */
		if ( \IPS\Settings::i()->xsendfile_debug_headers )
		{
			\IPS\Output::i()->sendHeader( 'X-Sendfile-Debug-Path: ' . $path );
			\IPS\Output::i()->sendHeader( 'Content-Disposition: ' . \IPS\Output::getContentDisposition( 'attachment',
					'xsendfile_debug_success.' . pathinfo( $this->originalFilename, PATHINFO_EXTENSION ) )
			);
		}

		/* Generic file headers */
		\IPS\Output::i()->sendHeader( 'Content-Type: ' . \IPS\File::getMimeType( $this->originalFilename ) );
		// \IPS\Output::i()->sendHeader( "Content-Length: " . $this->filesize() );
	}

}