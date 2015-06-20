<?php

namespace Asparagus;

use InvalidArgumentException;

/**
 * Package-private class to build the prefixes of a SPARQL query.
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryPrefixBuilder {

	/**
	 * @var string[] map of prefixes to IRIs
	 */
	private $prefixes = array();

	/**
	 * @var ExpressionValidator
	 */
	private $expressionValidator;

	/**
	 * @var UsageValidator
	 */
	private $usageValidator;

	/**
	 * @param string[] $prefixes
	 */
	public function __construct( array $prefixes, UsageValidator $usageValidator ) {
		$this->expressionValidator = new ExpressionValidator();
		$this->usageValidator = $usageValidator;
		$this->setPrefixes( $prefixes );
	}

	/**
	 * Sets the prefixes for the given IRIs.
	 *
	 * @param string[] $prefixes
	 * @throws InvalidArgumentException
	 */
	private function setPrefixes( array $prefixes ) {
		foreach ( $prefixes as $prefix => $iri ) {
			$this->expressionValidator->validate( $prefix, ExpressionValidator::VALIDATE_PREFIX );
			$this->expressionValidator->validate( $iri, ExpressionValidator::VALIDATE_IRI );

			$this->prefixes[$prefix] = $iri;
		}

		$this->usageValidator->trackDefinedPrefixes( array_keys( $this->prefixes ) );
	}

	/**
	 * @return string[]
	 */
	public function getPrefixes() {
		return $this->prefixes;
	}

	/**
	 * Returns the plain SPARQL string of these prefixes.
	 *
	 * @return string
	 */
	public function getSPARQL() {
		return implode( array_map( function( $prefix, $iri ) {
			return 'PREFIX ' . $prefix . ': <' . $iri . '> '; 
		}, array_keys( $this->prefixes ), $this->prefixes ) );
	}

}
