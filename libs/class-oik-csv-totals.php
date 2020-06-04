<?php
if ( !defined( 'CLASS_OIK_CSV_TOTALS_INCLUDED' ) ) {
	define( 'CLASS_OIK_CSV_TOTALS_INCLUDED', '0.0.1' );

	/**
	 * @copyright Bobbing Wide 2020
	 * @package oik-bob-bing-wide
	 *
	 * Produces a totals rows for the CSV.
	 *
	 * Usage:
	 * ```
	 * csv_totals = new oik_csv_totals( 'c,t,a' );
	 * foreach row {
	 *  csv_totals->row( array of column cells );
	 * }
	 * csv_totals->totals_row( 'Count:,Total:,Average:');
	 * ```
	 */
	class Oik_csv_totals {

		private $rows=0; // Row count
		private $totals=[]; // array of totalling actions - one for each column
		private $columns=[]; // Array of column totals - for 't' or 'a'

		/**
		 * Constructor for the oik_csv_totals class.
		 *
		 * @param string|array $totals Totalling actions for each column to be handled.
		 */
		function __construct( $totals ) {
			$this->set_totals( $totals );
			$this->columns=array_fill( 0, count( $this->totals ), 0 );
			$this->rows( null );
		}

		/**
		 * Sets the totalling method for each column.
		 *
		 * @param string|mixed $totals Totalling method for each column to be totalled.
		 */
		function set_totals( $totals ) {
			$this->totals=explode( ',', $totals );
			foreach ( $this->totals as $key=>$total ) {
				$total=trim( $total );
				$total=strtolower( $total );
				$total.=' ';
				$total=$total[0];
				switch ( $total ) {
					case 's':
						$total='t';
						break;
					case 't':
					case 'a':
					case '-':
					case 'c':
						break;

					default:
						$total='-';
				}
				$this->totals[ $key ]=$total;
			}
		}

		/**
		 * Incremements, resets or returns the rows.
		 *
		 * @param bool|null $inc Option to increment, reset or return the row.
		 */
		function rows( $inc=true ) {
			if ( $inc ) {
				$this->rows ++;
			} elseif ( null === $inc ) {
				$this->rows=0;
			}

			return $this->rows;
		}

		/**
		 * Accumulates another table row.
		 *
		 * @param array $tablerow Array of cells for the row.
		 */
		function row( $tablerow ) {
			$this->rows();
			$this->columns( $tablerow );
		}

		/**
		 * Accumulates column total when required.
		 *
		 * @param array $tablerow array of cells for the row.
		 */
		function columns( $tablerow ) {
			foreach ( $this->totals as $key=>$total ) {
				$cell=$tablerow[ $key ];
				switch ( $total ) {
					case 'a':
					case 't':
						if ( is_numeric( $cell ) ) {
							$this->columns[ $key ]+=$cell;
						}
				}
			}
		}

		function column( $key, $cell ) {
			if ( isset( $this->totals[ $key ] )) {
				$total=$this->totals[ $key ];
				switch ( $total ) {
					case 'a':
					case 't':
						if ( is_array( $cell )) {
							$cell = $cell[0];
						}
						if ( is_numeric( $cell ) ) {
							$this->columns[ $key ]+=$cell;
						}
				}
			}
		}


		/**
		 * Display the Totals row
		 *
		 * @param string| mixed $prefixes Optional prefixes for each cell in the totals row.
		 */
		function totals_row( $prefixes=null ) {
			$prefixes=bw_as_array( $prefixes );
			$totals  =[];
			foreach ( $this->totals as $key=>$total ) {
				$cell='';
				switch ( $total ) {
					case 't':
						$cell=$this->columns[ $key ];
						break;

					case 'a':
						$cell=$this->columns[ $key ] / $this->rows( false );
						break;

					case 'c':
						$cell=$this->rows( false );
						break;
				}
				$prefix=isset( $prefixes[ $key ] ) ? $prefixes[ $key ] . ' ' : '';

				$totals[]=$prefix . $cell;
			}
			bw_tablerow( $totals );
		}

	}

}
