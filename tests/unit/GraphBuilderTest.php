<?php

namespace Asparagus\Tests;

use Asparagus\GraphBuilder;

/**
 * @covers Asparagus\GraphBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class GraphBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testWhere() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals( ' ?a ?b ?c .', $graphBuilder->getSPARQL() );
	}

	public function testWhere_invalidSubject() {
		$graphBuilder = new GraphBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->where( null, '?b', '?c' );
	}

	public function testWhere_invalidPredicate() {
		$graphBuilder = new GraphBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->where( '?a', null, '?c' );
	}

	public function testWhere_invalidObject() {
		$graphBuilder = new GraphBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->where( '?a', '?b', null );
	}

	public function testPlus_knownSubject() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->also( '?x', '?y' );

		$this->assertEquals( ' ?a ?b ?c ; ?x ?y .', $graphBuilder->getSPARQL() );
	}

	public function testPlus_knownPredicate() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->also( '?x' );

		$this->assertEquals( ' ?a ?b ?c , ?x .', $graphBuilder->getSPARQL() );
	}

	public function testPlus_unknownSubject() {
		$graphBuilder = new GraphBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->also( '?x', '?y' );
	}

	public function testPlus_unknownPredicate() {
		$graphBuilder = new GraphBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->also( '?y' );
	}

	public function testFilter() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->filter( 'AVG (?x) > 5' );

		$this->assertEquals( ' FILTER AVG (?x) > 5', $graphBuilder->getSPARQL() );
	}

	public function testFilter_invalidExpression() {
		$graphBuilder = new GraphBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->filter( 'FooBar' );
	}

	public function testFilterExists() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->filterExists( $graphBuilder );

		$this->assertEquals( ' ?a ?b ?c . FILTER EXISTS { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testFilterNotExists() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->filterNotExists( $graphBuilder );

		$this->assertEquals( ' ?a ?b ?c . FILTER NOT EXISTS { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testOptional_triple() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->optional( '?a', '?b', '?c' );

		$this->assertEquals( ' OPTIONAL { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testOptional_graph() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->optional( $graphBuilder );

		$this->assertEquals( ' ?a ?b ?c . OPTIONAL { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testGetSPARQL() {
		$graphBuilder = new GraphBuilder();
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->also( '?x', '?y' );
		$graphBuilder->also( '?z' );
		$graphBuilder->where( '?a', '?b', '?z' );

		$this->assertEquals( ' ?a ?b ?c , ?z ; ?x ?y , ?z .', $graphBuilder->getSPARQL() );
	}

}
