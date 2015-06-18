<?php

namespace Asparagus;

use InvalidArgumentException;
use UnexpectedValueException;

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
	 *
	 * @param string $expression
	 * @param int $options
	 * @throws InvalidArgumentException
	 * @throws UnexpectedValueException
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
			throw new UnexpectedValueException( '$expression has to be a ' .
				implode( ' or a ', $this->getOptionNames( $options ) )
			);
		}
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

	private function matches( $expression, $options ) {
		if ( ( $options & self::VALIDATE_VARIABLE ) && $this->isVariable( $expression ) ) {
			$this->trackVariables( $expression );
			return true;
		}

		if ( ( $options & self::VALIDATE_IRI ) && $this->isIRI( $expression ) ) {
			return true;
		}

		if ( ( $options & self::VALIDATE_PREFIXED_IRI ) && $this->isPrefixedIRI( $expression ) ) {
			$this->trackPrefixes( $expression );
			return true;
		}

		if ( ( $options & self::VALIDATE_PREFIX ) && $this->isPrefix( $expression ) ) {
			$this->prefixes[$expression] = true;
			return true;
		}

		if ( ( $options & self::VALIDATE_FUNCTION ) && $this->isFunction( $expression ) ) {
			$this->trackVariables( $expression );
			$this->trackPrefixes( $expression );
			return true;
		}

		if ( ( $options & self::VALIDATE_FUNCTION_AS ) && $this->isFunctionAs( $expression ) ) {
			$this->trackVariables( $expression );
			$this->trackPrefixes( $expression );
			return true;
		}

		return false;
	}

	private function isVariable( $expression ) {
		return $this->matchesRegex( self::$variable, $expression );
	}

	private function isIRI( $expression ) {
		return $expression === 'a' || $this->matchesRegex( self::$iri, $expression );
	}

	private function isPrefixedIRI( $expression ) {
		return $this->matchesRegex( self::$prefix . ':' . self::$name, $expression );
	}

	private function isPrefix( $expression ) {
		return $this->matchesRegex( self::$prefix, $expression );
	}

	private function isFunction( $expression ) {
		// @todo also support expressions like ?a + ?b > 5 or ?x <= ?y
		// @todo this might not be complete
		// @todo check that opening brackets get closed
		$allowed = array_merge( self::$functions, array( self::$iri, self::$prefix . ':', self::$variable ) );
		return $this->matchesRegex( '(' . implode( '|', $allowed ) . ').*', $expression );
	}

	private function isFunctionAs( $expression ) {
		return $this->isFunction( $expression ) && $this->matchesRegex( '.* AS ' . self::$variable, $expression );
	}

	private function matchesRegex( $regex, $expression ) {
		return preg_match( '/^' . $regex . '$/i', $expression );
	}

	private function trackVariables( $expression ) {
		// negative look-behind
		if ( !preg_match_all( '/(^|\W)(?<!AS )' . self::$variable . '/', $expression, $matches ) ) {
			return;
		}

		foreach ( $matches[2] as $match ) {
			$this->variables[$match] = true;
		}
	}

	private function trackPrefixes( $expression ) {
		if ( !preg_match_all( '/(^|\W)(' . self::$prefix . '):/', $expression, $matches ) ) {
			return;
		}

		foreach ( $matches[2] as $match ) {
			$this->prefixes[$match] = true;
		}
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
