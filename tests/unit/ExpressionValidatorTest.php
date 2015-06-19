<?php

namespace Asparagus\Tests;

use Asparagus\ExpressionValidator;

/**
 * @covers Asparagus\ExpressionValidator
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ExpressionValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidExpressions
	 */
	public function testValidate_validExpressions( $expression, $options, array $variables, array $prefixes ) {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validate( $expression, $options );

		$this->assertEquals( $variables, $expressionValidator->getVariables() );
		$this->assertEquals( $prefixes, $expressionValidator->getPrefixes() );
	}

	public function provideValidExpressions() {
		return array(
			array( '?a', ExpressionValidator::VALIDATE_VARIABLE, array( 'a' ), array() ),
			array( '$b', ExpressionValidator::VALIDATE_VARIABLE, array( 'b' ), array() ),
			array( 'http://www.example.com/test#', ExpressionValidator::VALIDATE_IRI, array(), array() ),
			array( 'abc', ExpressionValidator::VALIDATE_PREFIX, array(), array() ),
			array( 'test:FooBar', ExpressionValidator::VALIDATE_PREFIXED_IRI, array(), array( 'test' ) ),
			array( 'foaf:knows/foaf:name', ExpressionValidator::VALIDATE_PATH, array(), array( 'foaf' ) ),
			array( 'foaf:knows/foaf:knows/foaf:name', ExpressionValidator::VALIDATE_PATH, array(), array( 'foaf' ) ),
			array( 'foaf:knows/^foaf:knows', ExpressionValidator::VALIDATE_PATH, array(), array( 'foaf' ) ),
			array( 'foaf:knows+/foaf:name', ExpressionValidator::VALIDATE_PATH, array(), array( 'foaf' ) ),
			array( '(ex:motherOf|ex:fatherOf)+', ExpressionValidator::VALIDATE_PATH, array(), array( 'ex' ) ),
			array( 'rdf:type/rdfs:subClassOf*', ExpressionValidator::VALIDATE_PATH, array(), array( 'rdf', 'rdfs' ) ),
			array( '^rdf:type', ExpressionValidator::VALIDATE_PATH, array(), array( 'rdf' ) ),
			array( '!(rdf:type|^rdf:type)', ExpressionValidator::VALIDATE_PATH, array(), array( 'rdf' ) ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION, array( 'x' ), array( 'xsd' ) ),
			array( '?abc', ExpressionValidator::VALIDATE_FUNCTION, array( 'abc' ), array() ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION, array( 'x', 'y', 'z' ), array() ),
			array( '?x * ?x < ?y', ExpressionValidator::VALIDATE_FUNCTION, array( 'x', 'y' ), array() ),
			array( 'CONTAINS (?x, ")))"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION, array( 'x' ), array( 'xsd' ) ),
			array( '<http://www.example.com/test#nyan> ?p ?q', ExpressionValidator::VALIDATE_FUNCTION, array( 'p', 'q' ), array() ),
			array( 'COUNT (?x) AS ?count', ExpressionValidator::VALIDATE_FUNCTION_AS, array( 'x' ), array() ),
		);
	}

	/**
	 * @dataProvider provideInvalidExpressions
	 */
	public function testValidate_invalidExpressions( $expression, $options, $errorMessage ) {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'InvalidArgumentException', $errorMessage );

		$expressionValidator->validate( $expression, $options );
	}

	public function provideInvalidExpressions() {
		return array(
			array( 'nyan', ExpressionValidator::VALIDATE_VARIABLE, 'variable' ),
			array( '<http://www.example.com/test#>', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( 'http://www.example.com/test#> foo bar', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( '<abc><>', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( 'http://www.example.com/te st#', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( 'http://www.example.com/test#ab\cd', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( 'http://www.example.com/test#ab|cd', ExpressionValidator::VALIDATE_IRI, 'IRI' ),
			array( 'ab:cd', ExpressionValidator::VALIDATE_PREFIX, 'prefix' ),
			array( 'ab/cd', ExpressionValidator::VALIDATE_PREFIX, 'prefix' ),
			array( 'ab cd', ExpressionValidator::VALIDATE_PREFIX, 'prefix' ),
			array( 'foobar', ExpressionValidator::VALIDATE_PREFIXED_IRI, 'prefixed IRI' ),
			array( 'test:Foo:Bar', ExpressionValidator::VALIDATE_PREFIXED_IRI, 'prefixed IRI' ),
			array( 'foobar (?x > ?y)', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( '(RAND ())', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( 'COUNT (?x) + 5) * ?a', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( ' AS ?abc', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( '', ExpressionValidator::VALIDATE_ALL, 'or a' ),
			array( '     ', ExpressionValidator::VALIDATE_ALL, 'or a' ),
		);
	}

	public function testValidate_invalidArgument() {
		$expressionValidator = new ExpressionValidator();
		$this->setExpectedException( 'InvalidArgumentException' );

		$expressionValidator->validate( null, ExpressionValidator::VALIDATE_ALL );
	}

}
