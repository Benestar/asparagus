<?php

namespace Asparagus\Tests;

use Asparagus\QueryPrefixBuilder;

/**
 * @covers Asparagus\QueryPrefixBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryPrefixBuilderTest extends \PHPUnit_Framework_TestCase {

	private static $prefixes = array(
		'test' => 'http://www.example.com/test#',
		'foo' => 'http://www.foo.org/bar#'
	);

	public function testConstructor() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes );

		$this->assertEquals( self::$prefixes, $prefixBuilder->getPrefixes() );
	}

	public function testConstructor_invalidIRI() {
		$this->setExpectedException( 'InvalidArgumentException' );

		new QueryPrefixBuilder( array( 'bar' => null ) );
	}

	public function testConstructor_invalidPrefix() {
		$this->setExpectedException( 'InvalidArgumentException' );

		new QueryPrefixBuilder( array( 4 => 'http://foo.bar.com/nyan#' ) );
	}

	public function testGetSPARQL() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes );

		$this->assertEquals(
			'PREFIX test: <http://www.example.com/test#> PREFIX foo: <http://www.foo.org/bar#> ',
			$prefixBuilder->getSPARQL()
		);
	}

}
