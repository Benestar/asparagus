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
		$this->prefixes( $prefixes );
	}

	/**
	 * Adds a prefix for the given IRI.
	 *
	 * @param string[] $prefixes
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function prefixes( array $prefixes ) {
		foreach ( $prefixes as $prefix => $iri ) {
			// @todo better string validation
			if ( !is_string( $prefix ) || !is_string( $iri ) ) {
				throw new InvalidArgumentException( '$prefix and $iri have to be strings' );
			}

			if ( isset( $this->prefixes[$prefix] ) ) {
				throw new OutOfBoundsException( 'Prefix ' . $prefix . ' is already set.' );
			}

			$this->prefixes[$prefix] = $iri;
		}
	}

	/**
	 * Returns the plain SPARQL string of these prefixes.
	 *
	 * @return string
	 */
	public function getPrefixes() {
		return implode( array_map( function( $prefix, $iri ) {
			return 'PREFIX ' . $prefix . ': <' . $iri . '> '; 
		}, $this->prefixes ) );
	}

}
