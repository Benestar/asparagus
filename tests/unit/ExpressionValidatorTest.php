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
	public function testValidate_validExpressions( $expression, $options ) {
		$expressionValidator = new ExpressionValidator();
		$expressionValidator->validate( $expression, $options );

		$this->assertTrue( true );
	}

	public function provideValidExpressions() {
		return array(
			array( '?a', ExpressionValidator::VALIDATE_VARIABLE ),
			array( '$b', ExpressionValidator::VALIDATE_VARIABLE ),
			array( 'http://www.example.com/test#', ExpressionValidator::VALIDATE_IRI ),
			array( 'abc', ExpressionValidator::VALIDATE_PREFIX ),
			array( 'test:FooBar', ExpressionValidator::VALIDATE_PREFIXED_IRI ),
			array( '<http://www.example.com/test#>', ExpressionValidator::VALIDATE_PREFIXED_IRI ),
			array( '123', ExpressionValidator::VALIDATE_NATIVE ),
			array( '"Foo bar"', ExpressionValidator::VALIDATE_NATIVE ),
			array( 'foaf:knows/foaf:name', ExpressionValidator::VALIDATE_PATH ),
			array( 'foaf:knows/foaf:knows/foaf:name', ExpressionValidator::VALIDATE_PATH ),
			array( 'foaf:knows/^foaf:knows', ExpressionValidator::VALIDATE_PATH ),
			array( 'foaf:knows+/foaf:name', ExpressionValidator::VALIDATE_PATH ),
			array( '(ex:motherOf|ex:fatherOf)+', ExpressionValidator::VALIDATE_PATH ),
			array( 'rdf:type/rdfs:subClassOf*', ExpressionValidator::VALIDATE_PATH ),
			array( '^rdf:type', ExpressionValidator::VALIDATE_PATH ),
			array( '!(rdf:type|^rdf:type)', ExpressionValidator::VALIDATE_PATH ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION ),
			array( '?abc', ExpressionValidator::VALIDATE_FUNCTION ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION ),
			array( '?x * ?x < ?y', ExpressionValidator::VALIDATE_FUNCTION ),
			array( 'CONTAINS (?x, ")))"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION ),
			array( '<http://www.example.com/test#nyan> ?p ?q', ExpressionValidator::VALIDATE_FUNCTION ),
			array( '(COUNT (?x) AS ?count)', ExpressionValidator::VALIDATE_FUNCTION_AS ),
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
			array( '"abc', ExpressionValidator::VALIDATE_NATIVE, 'native' ),
			array( 'abc123', ExpressionValidator::VALIDATE_NATIVE, 'native' ),
			array( 'foobar (?x > ?y)', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( '(RAND ())', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( 'COUNT (?x) + 5) * ?a', ExpressionValidator::VALIDATE_FUNCTION, 'function' ),
			array( 'CONTAINS (?x, "test"^^xsd:string)', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( '?x + ?y > ?z', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( ' AS ?abc', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
			array( 'COUNT (?x) AS ?count', ExpressionValidator::VALIDATE_FUNCTION_AS, 'function with variable assignment' ),
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
