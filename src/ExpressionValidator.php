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
	const VALIDATE_ALL = 127;

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
	 * Accepts property paths
	 */
	const VALIDATE_PATH = 16;

	/**
	 * Accept functions
	 */
	const VALIDATE_FUNCTION = 32;

	/**
	 * Accept functions with variable assignments
	 */
	const VALIDATE_FUNCTION_AS = 64;

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
	private static $iri = '[^\s<>"{}|\\\\^`]+';

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
			'prefix' => self::VALIDATE_PREFIX,
			'prefixed IRI' => self::VALIDATE_PREFIXED_IRI,
			'path' => self::VALIDATE_PATH,
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
			$this->isPrefix( $expression, $options ) ||
			$this->isPrefixedIRI( $expression, $options ) ||
			$this->isPath( $expression, $options ) ||
			$this->isFunction( $expression, $options ) ||
			$this->isFunctionAs( $expression, $options );
	}

	private function isVariable( $expression, $options ) {
		return $options & self::VALIDATE_VARIABLE &&
			$this->matchesRegex( self::$variable, $expression );
	}

	private function isIRI( $expression, $options ) {
		return $options & self::VALIDATE_IRI &&
			$this->matchesRegex( self::$iri, $expression );
	}

	private function isPrefix( $expression, $options ) {
		return $options & self::VALIDATE_PREFIX &&
			$this->matchesRegex( self::$prefix, $expression );
	}

	private function isPrefixedIRI( $expression, $options ) {
		return $options & self::VALIDATE_PREFIXED_IRI &&
			$this->matchesRegex( $this->getPrefixedIRIRegex(), $expression );
	}

	private function isPath( $expression, $options ) {
		// (?1) means the first subpattern (ie. the enclosing "()" brackets)
		$prefixedIRI = '[\^!]*(a|' . $this->getPrefixedIRIRegex() . '|\((?1)\))(\?|\*|\+)?';
		return $options & self::VALIDATE_PATH &&
			$this->matchesRegex( '(' . $prefixedIRI . '([\/\|]' . $prefixedIRI . ')*)', $expression );
	}

	private function getPrefixedIRIRegex() {
		return '(' . self::$prefix . ':' . self::$name . '|\<' . self::$iri . '\>)';
	}

	private function isFunction( $expression, $options ) {
		// @todo this might not be complete
		return $options & self::VALIDATE_FUNCTION &&
			$this->matchesRegex( $this->getFunctionRegex(), $expression ) &&
			$this->checkBrackets( $expression );
	}

	private function checkBrackets( $expression ) {
		$expression = preg_replace( '/"((\\.|[^\\"])*)"/', '', $expression );
		return substr_count( $expression, '(' ) === substr_count( $expression, ')' );
	}

	private function isFunctionAs( $expression, $options ) {
		return $options & self::VALIDATE_FUNCTION_AS &&
			$this->matchesRegex( $this->getFunctionRegex() . ' AS ' . self::$variable, $expression );
	}

	private function getFunctionRegex() {
		$allowed = array_merge( self::$functions, array( '\<' . self::$iri . '\>', self::$prefix . ':', self::$variable ) );
		return '(' . implode( '|', $allowed ) . ').*';
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
