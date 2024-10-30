/**
 * Make a string's first character uppercase
 *
 * @param {string} string The input string.
 *
 * @return {string} The resulting string.
 */
export const ucfirst = ( string ) => string.charAt( 0 ).toUpperCase() + string.slice( 1 );
