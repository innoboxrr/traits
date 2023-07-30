<?php 

namespace Innoboxrr\Traits;

trait ArrayOperations
{

	public function isNotEmpty(array $array) : bool
	{

		$keys = array_keys($array);

		foreach($keys as $key) {

			if($array[$key] != null) return true;

		}

		return false;

	}

	public function wrapImplode( $array, $before = '', $after = '', $separator = '' ){

        if( ! $array )  return '';

        return $before . implode("{$after}{$separator}{$before}", $array ) . $after;

    }

}