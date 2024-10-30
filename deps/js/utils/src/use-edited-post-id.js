/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Use edited post meta
 *
 * @return {?number} Edited post ID, null if not yet resolved.
 */
export const useEditedPostId = () => useSelect( ( select ) => select( editorStore ).getCurrentPostId(), [] );
