<?php

namespace Asparagus\Tests;

use Asparagus\GraphBuilder;
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
			$queryBuilder->also( '?a', '?b', '?c' )
		);
	}

	public function testPlus_knownSubject() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->also( '?x', '?y' )
		);
	}

	public function testPlus_knownPredicate() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->also( '?z' )
		);
	}

	public function testFilter() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->filter( 'AVG (?x) > 9' )
		);
	}

	public function testFilterExists() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->filterExists( new GraphBuilder() )
		);
	}

	public function testFilterNotExists() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->filterNotExists( new GraphBuilder() )
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
			$queryBuilder->having( 'AVG(?size) > 10' )
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

	public function testGetSPARQL_undefinedVariable() {
		$queryBuilder = new QueryBuilder();
		$this->setExpectedException( 'RangeException', '?x' );

		$queryBuilder->select( '?x' )->getSPARQL();
	}

	public function testGetSPARQL_undefinedPrefix() {
		$queryBuilder = new QueryBuilder();
		$this->setExpectedException( 'RangeException', 'foo, nyan' );

		$queryBuilder->where( '?x', 'foo:bar', 'nyan:data' )->getSPARQL();
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
