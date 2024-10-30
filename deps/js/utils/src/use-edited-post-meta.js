/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Use edited post meta
 *
 * @return {?Object} Post meta object, null if not yet resolved.
 */
export const useEditedPostMeta = () => useSelect( ( select ) => select( editorStore ).getEditedPostAttribute( 'meta' ), [] );
