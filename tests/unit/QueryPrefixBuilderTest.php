<?php

namespace Asparagus\Tests;

use Asparagus\QueryPrefixBuilder;

/**
 * @covers QueryPrefixBuilder
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

	public function testSetPrefixes() {
		$prefixBuilder = new QueryPrefixBuilder();
		$prefixBuilder->setPrefixes( self::$prefixes );

		$this->assertEquals( self::$prefixes, $prefixBuilder->getPrefixes() );
	}

	public function testSetPrefixes_invalidIRI() {
		$prefixBuilder = new QueryPrefixBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$prefixBuilder->setPrefixes( array( 'bar' => null ) );
	}

	public function testSetPrefixes_invalidPrefix() {
		$prefixBuilder = new QueryPrefixBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$prefixBuilder->setPrefixes( array( 4 => 'http://foo.bar.com/nyan#' ) );
	}

	public function testSetPrefixes_duplicatePrefix() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes );
		$this->setExpectedException( 'OutOfBoundsException' );

		$prefixBuilder->setPrefixes( array( 'test' => 'http://foo.bar.com/nyan#' ) );
	}

	public function testSetPrefixes_duplicatePrefixWithSameIRI() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes );
		$prefixBuilder->setPrefixes( array( 'test' => 'http://www.example.com/test#' ) );

		$this->assertEquals( self::$prefixes, $prefixBuilder->getPrefixes() );
	}

	public function testHasPrefix() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes );

		$this->assertTrue( $prefixBuilder->hasPrefix( 'test' ) );
		$this->assertFalse( $prefixBuilder->hasPrefix( 'nyan' ) );
	}

	public function testGetSPARQL() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes );

		$this->assertEquals(
			'PREFIX test: <http://www.example.com/test#> PREFIX foo: <http://www.foo.org/bar#> ',
			$prefixBuilder->getSPARQL()
		);
	}

}
