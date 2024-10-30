/**
 * Decode given JSON string
 *
 * @param {string} string   JSON string to decode.
 * @param {*}      fallback Fallback value to use in case of decode failure.
 *
 * @return {*} Decoded JSON value.
 */
export const decodeJSON = ( string, fallback = null ) => {
	try {
		return JSON.parse( string );
	} catch ( exception ) {
		return fallback;
	}
};

/**
 * Encode given value to JSON string
 *
 * @param {*} value Value to encode.
 *
 * @return {string} JSON string.
 */
export const encodeJSON = ( value ) => {
	return JSON.stringify( value );
};

/**
 * Check whether the given string is a valid JSON
 *
 * @param {string} string String to validate.
 *
 * @return {boolean} Whether the given string is a valid JSON.
 */
export const isValidJSON = ( string ) => {
	try {
		JSON.parse( string );
	} catch ( exception ) {
		return false;
	}

	return true;
};
