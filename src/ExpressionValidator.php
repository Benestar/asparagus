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
	const VALIDATE_ALL = 63;

	/**
	 * Accept variables
	 */
	const VALIDATE_VARIABLE = 1;

	/**
	 * Accept IRIs
	 */
	const VALIDATE_IRI = 2;

	/**
	 * Accept prefixed IRIs
	 */
	const VALIDATE_PREFIXED_IRI = 4;

	/**
	 * Accept prefixes
	 */
	const VALIDATE_PREFIX = 8;

	/**
	 * Accept functions
	 */
	const VALIDATE_FUNCTION = 16;

	/**
	 * Accept functions with variable assignments
	 */
	const VALIDATE_FUNCTION_AS = 32;

	/**
	 * @var string[] list of natively supported functions
	 */
	private static $functions = array(
		'COUNT', 'SUM', 'MIN', 'MAX', 'AVG', 'SAMPLE', 'GROUP_CONCAT', 'STR',
		'LANG', 'LANGMATCHES', 'DATATYPE', 'BOUND', 'IRI', 'URI', 'BNODE',
		'RAND', 'ABS', 'CEIL', 'FLOOR', 'ROUND', 'CONCAT', 'STRLEN', 'UCASE',
		'LCASE', 'ENCODE_FOR_URI', 'CONTAINS', 'STRSTARTS', 'STRENDS',
		'STRBEFORE', 'STRAFTER', 'YEAR', 'MONTH', 'DAY', 'HOURS', 'MINUTES',
		'SECONDS', 'TIMEZONE', 'TZ', 'NOW', 'UUID', 'STRUUID', 'MD5', 'SHA1',
		'SHA256', 'SHA384', 'SHA512', 'COALESCE', 'IF', 'STRLANG', 'STRDT',
		'sameTerm', 'isIRI', 'isURI', 'isBLANK', 'isLITERAL', 'isNUMERIC',
		'REGEX', 'SUBSTR', 'REPLACE', 'EXISTS', 'NOT EXISTS'
	);

	/**
	 * @var string regex to match variables
	 */
	private static $variable = '[?$](\w+)';

	/**
	 * @var string regex to match IRIs
	 */
	private static $iri = '\<((\\.|[^\\<])+)\>';

	/**
	 * @var string regex to match prefixes
	 */
	private static $prefix = '\w+';

	/**
	 * @var string regex to match names after prefixes
	 */
	private static $name = '\w+';

	/**
	 * @var string[]
	 */
	private $variables = array();

	/**
	 * @var string[]
	 */
	private $prefixes = array();

	/**
	 * Validates the given expression and tracks it.
	 * The default options accept IRIs, prefixed IRIs and variables.
	 * VALIDATE_PREFIX won't track prefixes.
	 *
	 * @param string $expression
	 * @param int $options
	 * @throws InvalidArgumentException
	 */
	public function validate( $expression, $options = -1 ) {
		if ( !is_string( $expression ) ) {
			throw new InvalidArgumentException( '$expression has to be a string.' );
		}

		if ( $options < 0 ) {
			$options = self::VALIDATE_IRI | self::VALIDATE_PREFIXED_IRI | self::VALIDATE_VARIABLE;
		}

		if ( !$this->matches( $expression, $options ) ) {
			// @todo better error message
			throw new InvalidArgumentException( '$expression has to be a ' .
				implode( ' or a ', $this->getOptionNames( $options ) )
			);
		}

		$this->trackVariables( $expression );
		$this->trackPrefixes( $expression );
	}

	private function getOptionNames( $options ) {
		$names = array(
			'variable' => self::VALIDATE_VARIABLE,
			'IRI' => self::VALIDATE_IRI,
			'prefixed IRI' => self::VALIDATE_PREFIXED_IRI,
			'prefix' => self::VALIDATE_PREFIX,
			'function' => self::VALIDATE_FUNCTION,
			'function with variable assignment' => self::VALIDATE_FUNCTION_AS
		);

		$names = array_filter( $names, function( $key ) use ( $options ) {
			return $options & $key;
		} );

		return array_keys( $names );
	}

	private function trackVariables( $expression ) {
		// negative look-behind
		if ( preg_match_all( '/(^|\W)(?<!AS )' . self::$variable . '/', $expression, $matches ) ) {
			foreach ( $matches[2] as $match ) {
				$this->variables[$match] = true;
			}
		}
	}

	private function trackPrefixes( $expression ) {
		if ( preg_match_all( '/(^|\W)(' . self::$prefix . '):' . self::$name . '/', $expression, $matches ) ) {
			foreach ( $matches[2] as $match ) {
				$this->prefixes[$match] = true;
			}
		}
	}

	private function matches( $expression, $options ) {
		return $this->isVariable( $expression, $options ) ||
			$this->isIRI( $expression, $options ) ||
			$this->isPrefixedIRI( $expression, $options ) ||
			$this->isPrefix( $expression, $options ) ||
			$this->isFunction( $expression, $options ) ||
			$this->isFunctionAs( $expression, $options );
	}

	private function isVariable( $expression, $options ) {
		return $options & self::VALIDATE_VARIABLE &&
			$this->matchesRegex( self::$variable, $expression );
	}

	private function isIRI( $expression, $options ) {
		return $options & self::VALIDATE_IRI &&
			( $expression === 'a' || $this->matchesRegex( self::$iri, $expression ) );
	}

	private function isPrefixedIRI( $expression, $options ) {
		return $options & self::VALIDATE_PREFIXED_IRI &&
			$this->matchesRegex( self::$prefix . ':' . self::$name, $expression );
	}

	private function isPrefix( $expression, $options ) {
		return $options & self::VALIDATE_PREFIX &&
			$this->matchesRegex( self::$prefix, $expression );
	}

	private function isFunction( $expression, $options ) {
		if ( !( $options & self::VALIDATE_FUNCTION ) ) {
			return false;
		}

		// @todo this might not be complete
		$allowed = array_merge( self::$functions, array( self::$iri, self::$prefix . ':', self::$variable ) );
		return $this->matchesRegex( '(' . implode( '|', $allowed ) . ').*', $expression ) &&
			$this->checkBrackets( $expression );
	}

	private function checkBrackets( $expression ) {
		$expression = preg_replace( '/"((\\.|[^\\"])*)"/', '', $expression );
		return substr_count( $expression, '(' ) === substr_count( $expression, ')' );
	}

	private function isFunctionAs( $expression, $options ) {
		return $options & self::VALIDATE_FUNCTION_AS &&
			$this->isFunction( $expression, self::VALIDATE_FUNCTION ) &&
			$this->matchesRegex( '.* AS ' . self::$variable, $expression );
	}

	private function matchesRegex( $regex, $expression ) {
		return preg_match( '/^' . $regex . '$/i', $expression );
	}

	/**
	 * Returns the list of varialbes which have been used.
	 *
	 * @return string[]
	 */
	public function getVariables() {
		return array_keys( $this->variables );
	}

	/**
	 * Returns the list of prefixes which have been used.
	 *
	 * @return string[]
	 */
	public function getPrefixes() {
		return array_keys( $this->prefixes );
	}

}
