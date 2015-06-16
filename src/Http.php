<?php

namespace Asparagus;

use RuntimeException;

/**
 * Package-private class to deal with HTTP related stuff.
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class Http {

	/**
	 * @var resource cURL-Handle
	 */
	private $ch;

	/**
	 * @param string $userAgent
	 */
	public function __construct( $userAgent ) {
		$this->ch = curl_init();
		curl_setopt( $this->ch, CURLOPT_USERAGENT, $userAgent );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
	}

	public function __destruct() {
		curl_close( $this->ch );
	}

	/**
	 * @param string $url
	 * @param string[] $params
	 * @return string
	 * @throws RuntimeException
	 */
	public function request( $url, array $params = array() ) {
		curl_setopt( $this->ch, CURLOPT_URL, $url . '?' . http_build_query( $params ) );
		curl_setopt( $this->ch, CURLOPT_HTTPGET, true );

		$response = curl_exec( $this->ch );

		if ( curl_errno( $this->ch ) ) {
			throw new RuntimeException( curl_error( $this->ch ), curl_errno( $this->ch ) );
		} else if ( curl_getinfo( $this->ch, CURLINFO_HTTP_CODE ) >= 400 ) {
			throw new RuntimeException( 'HTTP error: ' . $url, curl_getinfo( $this->ch, CURLINFO_HTTP_CODE ) );
		}

		return $response;
	}

}
