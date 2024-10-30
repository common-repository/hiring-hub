/**
 * Ensure that a given array key is included in or excluded from a given array
 *
 * @param {Array}   array   Array to operate on.
 * @param {string}  key     Key to include in or exclude from array.
 * @param {boolean} include Whether to include in (true) or exclude from (false) array.
 *
 * @return {Array} Updated array.
 */
export const arrayIncludeExclude = ( array, key, include = true ) => {
	// Clone the array and returns a reference to a new array.
	const result = array.slice();

	if ( ! include ) {
		const index = result.indexOf( key );

		if ( -1 !== index ) {
			result.splice( index, 1 );
		}
	} else {
		result.push( key );
	}

	return result;
};
