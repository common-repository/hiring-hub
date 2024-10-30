/**
 * External dependencies
 */
import { URLControl } from '@teydeastudio/components/src/url-control/index.js';
import { buildId } from '@teydeastudio/utils/src/build-id.js';

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { useApplyOnLink } from '../utils.js';

/**
 * Component
 *
 * @return {JSX} Post meta component.
 */
const Component = () => {
	// Update the post meta field value.
	const setPostMeta = ( meta ) => dispatch( 'core/editor' ).editPost( { meta } );

	// Collect the necessary data.
	const applyOnLink = useApplyOnLink();

	/**
	 * Ensure we only render the component after all dependencies are resolved
	 */
	if ( 'undefined' === typeof applyOnLink ) {
		return null;
	}

	/**
	 * Render the component
	 */
	return (
		<PluginDocumentSettingPanel
			title={ __( 'Apply on link', 'hiring-hub' ) }
			initialOpen={ true }
		>
			<URLControl
				label={ __( 'URL or email address', 'hiring-hub' ) }
				help={ __( 'Link to use for the "Apply" button, under which candidates can apply for this position.', 'hiring-hub' ) }
				value={ applyOnLink.value }

				/**
				 * Update the post meta
				 *
				 * @param {string} updatedValue Updated value.
				 *
				 * @return {void}
				 */
				onChange={ ( updatedValue ) => {
					setPostMeta( { [ applyOnLink.metaKey ]: updatedValue } );
				} }
			/>
		</PluginDocumentSettingPanel>
	);
};

/**
 * Register plugin
 */
domReady( () => {
	registerPlugin( buildId( 'hiring-hub', 'extensions-apply-on-link-post-meta', 'plugin' ), {
		render: Component,
	} );
} );
