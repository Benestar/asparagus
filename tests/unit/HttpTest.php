<?php

namespace Asparagus\Tests;

use Asparagus\Http;

/**
 * @covers Asparagus\Http
 *
 * @todo this is really evil >.<
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class HttpTest extends \PHPUnit_Framework_TestCase {

	public function testRequest() {
		$http = new Http( 'Asparagus Test/Asparagus 0.1' );
		$result = $http->request( 'http://wikidata-mobile.wmflabs.org/w/api.php' );

		$this->assertContains( 'MediaWiki API help', $result );
	}

	public function testRequestWithParams() {
		$http = new Http( 'Asparagus Test/Asparagus 0.1' );
		$result = $http->request( 'http://wikidata-mobile.wmflabs.org/w/api.php', array(
			'action' => 'query',
			'format' => 'json'
		) );

		$this->assertEquals( '{}', $result );
	}

	public function testRequestFails() {
		$http = new Http( 'Asparagus Test/Asparagus 0.1' );
		$this->setExpectedException( 'RuntimeException', null, 404 );

		$http->request( 'http://wikidata-mobile.wmflabs.org/not-existing-file.php' );
	}

}
