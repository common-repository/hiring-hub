/**
 * Convert string to camelCase
 *
 * @param {string} string The input string.
 *
 * @return {string} The resulting string.
 */
export const toCamelCase = ( string ) => string
	.replace( '-', ' ' )
	.replace( /\s(.)/g, ( part ) => part.toUpperCase() )
	.replace( /\s/g, '' )
	.replace( /^(.)/, ( part ) => part.toLowerCase() );
