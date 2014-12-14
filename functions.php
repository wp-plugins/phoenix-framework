<?php
	function phoenixFilterMultiSelectValue( array &$val ) {
		$output = array();
		foreach ( $val as $itemKey => $itemVal ) {
			if ( $itemVal == 'checked' ) {
				$output[ ] = $itemKey;
			}
		}
		$val = $output;
	}

	function phoenixGetVersion(){
		return Phoenix_Framework::$version;
	}