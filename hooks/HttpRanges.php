//<?php

class hook286 extends _HOOK_CLASS_
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
		/* Generic file headers */
		\IPS\Output::i()->sendHeader( 'Content-Disposition: ' .
			\IPS\Output::getContentDisposition( 'attachment', $file->originalFilename )
		);

		/* Pass off the the X-Sendfile hooked handler */
		$file->printFile( null, null, $throttle );
	}

}