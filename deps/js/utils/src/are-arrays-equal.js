/**
 * Compare two arrays
 *
 * @param {Array} array1 First array to compare.
 * @param {Array} array2 Second array to compare.
 *
 * @return {boolean} Comparison result: "true" if arrays are equal.
 */
export const areArraysEqual = ( array1, array2 ) => JSON.stringify( array1 ) === JSON.stringify( array2 );
