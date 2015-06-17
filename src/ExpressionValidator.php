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
	const VALIDATE_ALL = 0b1111;

	/**
	 * Accept variables
	 */
	const VALIDATE_VARIABLE = 0b1;

	/**
	 * Accept IRIs
	 */
	const VALIDATE_IRI = 0b10;

	/**
	 * Accept prefixed IRIs
	 */
	const VALIDATE_PREFIXED_IRI = 0b100;

	/**
	 * Accept prefixes
	 */
	const VALIDATE_PREFIX = 0b1000;

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
	public function validateExpression( $expression, $options = -1 ) {
		if ( !is_string( $expression ) ) {
			throw new InvalidArgumentException( '$expression has to be a string.' );
		}

		if ( $options < 0 ) {
			$options = self::VALIDATE_IRI | self::VALIDATE_PREFIXED_IRI | self::VALIDATE_VARIABLE;
		}

		if ( ( $options & self::VALIDATE_VARIABLE ) && $this->isVariable( $expression ) ) {
			$this->variables[substr( $expression, 1 )] = true;
			return;
		}

		if ( ( $options & self::VALIDATE_IRI ) && $this->isIRI( $expression ) ) {
			return;
		}

		if ( ( $options & self::VALIDATE_PREFIXED_IRI ) && $this->isPrefixedIRI( $expression ) ) {
			$this->prefixes[substr( $expression, 0, strpos( $expression, ':' ) )] = true;
			return;
		}

		if ( ( $options & self::VALIDATE_PREFIX ) && $this->isPrefix( $expression ) ) {
			$this->prefixes[substr( $expression, 0, strpos( $expression, ':' ) )] = true;
			return;
		}

		throw new UnexpectedValueException( '$expression has to match ' . $options );
	}

	private function isVariable( $expression ) {
		return preg_match( '/^[?$][A-Za-z_]+$/', $expression );
	}

	private function isIRI( $expression ) {
		return preg_match( '/^\<((\\.|[^\\<])+)\>$/', $expression );
	}

	private function isPrefixedIRI( $expression ) {
		return preg_match( '/^[A-Za-z_]+:[A-Za-z_0-9:]*$/', $expression );
	}

	private function isPrefix( $expression ) {
		return preg_match( '/^[A-Za-z_]+$/', $expression );
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
