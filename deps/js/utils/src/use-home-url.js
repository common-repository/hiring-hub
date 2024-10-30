/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Use home URL
 *
 * @return {?string} Home URL, null if not yet resolved.
 */
export const useHomeUrl = () => useSelect( ( select ) => {
	const site = select( coreStore ).getSite();
	return 'string' === typeof site?.url ? site.url : null;
}, [] );
