<?php

namespace Asparagus;

use InvalidArgumentException;
use OutOfBoundsException;

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
	 * @var string[] $prefixes
	 */
	public function __construct( array $prefixes = array() ) {
		$this->setPrefixes( $prefixes );
	}

	/**
	 * Sets the prefixes for the given IRIs.
	 *
	 * @param string[] $prefixes
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function setPrefixes( array $prefixes ) {
		foreach ( $prefixes as $prefix => $iri ) {
			// @todo better string validation
			if ( !is_string( $prefix ) || !is_string( $iri ) ) {
				throw new InvalidArgumentException( '$prefix and $iri have to be strings' );
			}

			if ( isset( $this->prefixes[$prefix] ) && $iri !== $this->prefixes[$prefix] ) {
				throw new OutOfBoundsException( 'Prefix ' . $prefix . ' is already used for <' . $this->prefixes[$prefix] . '>' );
			}

			$this->prefixes[$prefix] = $iri;
		}
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
