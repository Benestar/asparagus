<?php

namespace Asparagus\Tests;

use Asparagus\QueryPrefixBuilder;
use Asparagus\UsageValidator;

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
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes, new UsageValidator() );

		$this->assertEquals( self::$prefixes, $prefixBuilder->getPrefixes() );
	}

	public function testConstructor_invalidIRI() {
		$this->setExpectedException( 'InvalidArgumentException' );

		new QueryPrefixBuilder( array( 'bar' => null ), new UsageValidator() );
	}

	public function testConstructor_invalidPrefix() {
		$this->setExpectedException( 'InvalidArgumentException' );

		new QueryPrefixBuilder( array( 4 => 'http://foo.bar.com/nyan#' ), new UsageValidator() );
	}

	public function testGetSPARQL() {
		$prefixBuilder = new QueryPrefixBuilder( self::$prefixes, new UsageValidator() );

		$this->assertEquals(
			'PREFIX test: <http://www.example.com/test#> PREFIX foo: <http://www.foo.org/bar#> ',
			$prefixBuilder->getSPARQL()
		);
	}

}
