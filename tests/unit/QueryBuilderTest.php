<?php

namespace Asparagus\Tests;

use Asparagus\QueryBuilder;

/**
 * @covers Asparagus\QueryBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testSelect() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->select( '?a', '?b' )
		);

		// use variables ?a and ?b
		$queryBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals(
			'SELECT ?a ?b WHERE { ?a ?b ?c . }',
			$queryBuilder->getSPARQL()
		);
	}

	public function testSelect_invalidArgument() {
		$queryBuilder = new QueryBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->select( '?a', false );
	}

	public function testWhere() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->where( '?a', '?b', '?c' )
		);
	}

	public function testPlus() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->plus( '?a', '?b', '?c' )
		);
	}

	public function testPlus_knownSubject() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->plus( '?x', '?y' )
		);
	}

	public function testPlus_knownPredicate() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->plus( '?z' )
		);
	}

	public function testGroupBy() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->groupBy( '?test' )
		);
	}

	public function testHaving() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->having( '?test' )
		);
	}

	public function testOrderBy() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->orderBy( '?test' )
		);
	}

	public function testLimit() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->limit( 5 )
		);
	}

	public function testOffset() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->offset( 5 )
		);
	}

	public function testGetSPARQL() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( 'SELECT * WHERE { }', $queryBuilder->getSPARQL() );
	}

	public function testGetSPARQL_withPrefixes() {
		$queryBuilder = new QueryBuilder( array( 'foo' => 'bar' ) );

		$this->assertEquals( 'PREFIX foo: <bar> SELECT * WHERE { }', $queryBuilder->getSPARQL() );
	}

	public function testGetSPARQL_noPrefixes() {
		$queryBuilder = new QueryBuilder( array( 'foo' => 'bar' ) );

		$this->assertEquals( 'SELECT * WHERE { }', $queryBuilder->getSPARQL( false ) );
	}

	public function testToString() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( 'SELECT * WHERE { }', strval( $queryBuilder ) );
	}

	public function testFormat() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( "SELECT * WHERE {\n}\n", $queryBuilder->format() );
	}

}
