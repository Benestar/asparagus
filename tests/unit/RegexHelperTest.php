<?php

namespace Asparagus\Tests;

use Asparagus\RegexHelper;

/**
 * @covers Asparagus\RegexHelper
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class RegexHelperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideMatchingExpressions
	 */
	public function testMatchesRegex_matching( $regex, $expression ) {
		$regexHelper = new RegexHelper();

		$this->assertTrue( $regexHelper->matchesRegex( $regex, $expression ) );
	}

	public function provideMatchingExpressions() {
		return array(
			array( '\{variable}', '?abc' ),
			array( '\{variable}', '$abc' ),
			array( 'AS \{variable}', 'AS ?nyan' ),
			array( '\{iri}', 'http://www.example.com/test#' ),
			array( '\{prefix}', 'foo_bar42' ),
			array( '\{name}', 'foo_bar42' ),
			array( '\{prefixed_iri}', '<http://www.example.com/test#>' ),
			array( '\{prefixed_iri}', 'nyan:cat' ),
			array( '\{native}', '123' ),
			array( '\{native}', '"foo bar"' ),
			array( '\{path}', 'nyan:cat' ),
			array( '\{path}', '^nyan:cat' ),
			array( '\{path}', 'nyan:cat' ),
			array( '\{path}', 'nyan:cat/nyan:kitten' ),
			array( '\{path}', 'nyan:cat/nyan:kitten/cat:kitten' ),
			array( '\{path}', 'nyan:cat|nyan:kitten' ),
			array( '\{path}', 'nyan:cat*' ),
			array( '\{path}', 'nyan:cat+' ),
			array( '\{path}', 'nyan:cat?' ),
			array( '\{path}', '!nyan:cat' ),
			array( '\{path}', '!(nyan:cat|nyan:kitten)' ),
			array( '\{path}', '!^nyan:cat' ),
			array( '\{path}', '!(^nyan:cat|^nyan:kitten)' ),
			array( '\{path}', '(nyan:cat)' ),
			array( '\{function}', 'COUNT (?x)' ),
			array( '\{function}', '?x + ?y' ),
			array( '\{function}', '!BOUND (?x)' ),
			array( '\{function}', '<http://www.example.com/test#nyan>' ),
		);
	}

	/**
	 * @dataProvider provideNonMatchingExpressions
	 */
	public function testMatchesRegex_nonMatching( $regex, $expression ) {
		$regexHelper = new RegexHelper();

		$this->assertFalse( $regexHelper->matchesRegex( $regex, $expression ) );
	}

	public function provideNonMatchingExpressions() {
		return array(
			array( '\{variable}', 'foobar' ),
			array( '\{variable}', '?foo bar' ),
			array( 'AS \{variable}', 'AS ?nyan foobar' ),
			array( '\{iri}', '<http://www.example.com/test#>' ),
			array( '\{iri}', 'http://www.example.com/te>st#' ),
			array( '\{iri}', 'http://www.example.com/te}st#' ),
			array( '\{iri}', 'http://www.example.com/te st#' ),
			array( '\{prefix}', 'nyan cat' ),
			array( '\{prefix}', 'nyan:cat' ),
			array( '\{name}', 'nyan cat' ),
			array( '\{name}', 'nyan:cat' ),
			array( '\{prefixed_iri}', 'http://www.example.com/test#nyan' ),
			array( '\{prefixed_iri}', 'nyan:cat:kitten' ),
			array( '\{native}', '"abc' ),
			array( '\{native}', 'ab123' ),
			array( '\{path}', 'foobar' ),
			array( '\{path}', '?foobar' ),
			array( '\{path}', '^!nyan:cat' ),
			array( '\{path}', '!!!nyan:cat' ),
			array( '\{path}', '()' ),
			array( '\{function}', 'FOO BAR' ),
			array( '\{function}', '(COUNT (?x))' )
		);
	}

	/**
	 * @dataProvider provideGetMatches
	 */
	public function testGetMatches( $regex, $expression, $matches ) {
		$regexHelper = new RegexHelper();

		$this->assertEquals( $matches, $regexHelper->getMatches( $regex, $expression ) );
	}

	public function provideGetMatches() {
		return array(
			array( '\{variable}', ' ?abc nyan $def ', array( 'abc', 'def' ) ),
			array( 'AS \{variable}', 'FOO (?x) AS ?nyan', array( 'nyan' ) ),
			array( 'AS \{variable}', 'FOO (<http://www.example.com/test?kitten> = "X AS ?kitten") AS ?nyan', array( 'nyan' ) )
		);
	}

	/**
	 * @dataProvider provideEscapeSequences
	 */
	public function testEscapeSequences( $expression, array $matches ) {
		$regexHelper = new RegexHelper();
		$escaped = $regexHelper->escapeSequences( $expression, $replacements );

		$this->assertEquals( $matches, array_values( $replacements ) );
		$this->assertEquals( $expression, strtr( $escaped, $replacements ) );
	}

	public function provideEscapeSequences() {
		return array(
			array( 'foo bar', array() ),
			array( 'cat " kitten', array() ),
			array( 'I\'m a "nyan cat" and this is cool', array( '"nyan cat"' ) ),
			array( 'This is "so sweet" and it\'s a "nyan" cat', array( '"so sweet"', '"nyan"' ) ),
			array( 'This <iri> is cool', array( '<iri>' ) ),
			array( 'This <iri> is > confusing', array( '<iri>' ) ),
			array( 'A "nyan cat" with <iri> 42', array( '"nyan cat"', '<iri>' ) ),
			array( 'A "nyan <iri> cat" with 42', array( '"nyan <iri> cat"' ) ),
			array( 'A "nyan <iri cat" with <iri> 42', array( '"nyan <iri cat"', '<iri>' ) )
		);
	}

}
