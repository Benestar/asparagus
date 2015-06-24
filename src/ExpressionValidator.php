<?php

namespace Asparagus;

use InvalidArgumentException;

/**
 * Package-private class to validate expressions like variables and IRIs.
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ExpressionValidator {

	/**
	 * Accept all expressions
	 */
	const VALIDATE_ALL = 255;

	/**
	 * Accept variables
	 */
	const VALIDATE_VARIABLE = 1;

	/**
	 * Accept IRIs
	 */
	const VALIDATE_IRI = 2;

	/**
	 * Accept prefixes
	 */
	const VALIDATE_PREFIX = 4;

	/**
	 * Accept prefixed IRIs
	 */
	const VALIDATE_PREFIXED_IRI = 8;

	/**
	 * Accept native values
	 */
	const VALIDATE_NATIVE = 16;

	/**
	 * Accepts property paths
	 */
	const VALIDATE_PATH = 32;

	/**
	 * Accept functions
	 */
	const VALIDATE_FUNCTION = 64;

	/**
	 * Accept functions with variable assignments
	 */
	const VALIDATE_FUNCTION_AS = 128;

	/**
	 * @var RegexHelper
	 */
	private $regexHelper;

	public function __construct() {
		$this->regexHelper = new RegexHelper();
	}

	/**
	 * Validates the given expression and tracks it.
	 * VALIDATE_PREFIX won't track prefixes.
	 *
	 * @param string $expression
	 * @param int $options
	 * @throws InvalidArgumentException
	 */
	public function validate( $expression, $options ) {
		if ( !is_string( $expression ) ) {
			throw new InvalidArgumentException( '$expression has to be a string.' );
		}

		if ( !$this->matches( $expression, $options ) ) {
			throw new InvalidArgumentException( '$expression has to be a ' .
				implode( ' or a ', $this->getOptionNames( $options ) ) . ', got ' . $expression
			);
		}
	}

	private function getOptionNames( $options ) {
		$names = array(
			'variable' => self::VALIDATE_VARIABLE,
			'IRI' => self::VALIDATE_IRI,
			'prefix' => self::VALIDATE_PREFIX,
			'prefixed IRI' => self::VALIDATE_PREFIXED_IRI,
			'native' => self::VALIDATE_NATIVE,
			'path' => self::VALIDATE_PATH,
			'function' => self::VALIDATE_FUNCTION,
			'function with variable assignment' => self::VALIDATE_FUNCTION_AS
		);

		$names = array_filter( $names, function( $key ) use ( $options ) {
			return $options & $key;
		} );

		return array_keys( $names );
	}

	private function matches( $expression, $options ) {
		return $this->isVariable( $expression, $options ) ||
			$this->isIRI( $expression, $options ) ||
			$this->isPrefix( $expression, $options ) ||
			$this->isPrefixedIri( $expression, $options ) ||
			$this->isValue( $expression, $options ) ||
			$this->isPath( $expression, $options ) ||
			$this->isFunction( $expression, $options ) ||
			$this->isFunctionAs( $expression, $options );
	}

	private function isVariable( $expression, $options ) {
		return $options & self::VALIDATE_VARIABLE &&
			$this->regexHelper->matchesRegex( '\{variable}', $expression );
	}

	private function isIRI( $expression, $options ) {
		return $options & self::VALIDATE_IRI &&
			$this->regexHelper->matchesRegex( '\{iri}', $expression );
	}

	private function isPrefix( $expression, $options ) {
		return $options & self::VALIDATE_PREFIX &&
			$this->regexHelper->matchesRegex( '\{prefix}', $expression );
	}

	private function isPrefixedIri( $expression, $options ) {
		return $options & self::VALIDATE_PREFIXED_IRI &&
			$this->regexHelper->matchesRegex( '\{prefixed_iri}', $expression );
	}

	private function isValue( $expression, $options ) {
		return $options & self::VALIDATE_NATIVE &&
			$this->regexHelper->matchesRegex( '\{native}', $expression );
	}

	private function isPath( $expression, $options ) {
		return $options & self::VALIDATE_PATH &&
			$this->regexHelper->matchesRegex( '\{path}', $expression );
	}

	private function isFunction( $expression, $options ) {
		// @todo this might not be complete
		return $options & self::VALIDATE_FUNCTION &&
			$this->regexHelper->matchesRegex( '\{function}', $expression ) &&
			$this->checkBrackets( $expression );
	}

	private function checkBrackets( $expression ) {
		$expression = $this->regexHelper->escapeSequences( $expression );
		return substr_count( $expression, '(' ) === substr_count( $expression, ')' );
	}

	private function isFunctionAs( $expression, $options ) {
		return $options & self::VALIDATE_FUNCTION_AS &&
			$this->regexHelper->matchesRegex( '\(\{function} AS \{variable}\)', $expression );
	}

}
