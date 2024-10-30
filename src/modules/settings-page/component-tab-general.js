/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { useHomeUrl } from '@teydeastudio/utils/src/use-home-url.js';

/**
 * WordPress dependencies
 */
import { Panel, PanelBody, PanelRow, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { cleanForSlug } from '@wordpress/url';

/**
 * TabGeneral component
 *
 * @param {Object}   properties             Component properties object.
 * @param {Object}   properties.settings    Plugin settings.
 * @param {Function} properties.setSettings Function (callback) used to update the settings.
 *
 * @return {JSX} TabGeneral component.
 */
export const TabGeneral = ( { settings, setSettings } ) => {
	// Default value for the archives page slug.
	const DEFAULT_ARCHIVE_SLUG = 'jobs';

	// Default value for the single job post slug.
	const DEFAULT_POST_SLUG = 'job';

	// Get the home url.
	const homeUrl = useHomeUrl();

	/**
	 * Render the component
	 */
	return (
		<Fragment>
			<div>
				<p>{ __( 'Manage the general plugin settings in this section.', 'hiring-hub' ) }</p>
			</div>
			<Panel>
				<PanelBody>
					<PanelRow>
						<TextControl
							label={ __( 'Archives page slug', 'hiring-hub' ) }
							value={ settings?.general?.archiveSlug ?? DEFAULT_ARCHIVE_SLUG }
							help={ sprintf(
								// Translators: optional hint content.
								__( 'Slug of the job archives page; must not be empty. %s', 'hiring-hub' ),
								(
									( null === homeUrl || '' === settings?.general?.archiveSlug )
										? ''
										: sprintf(
											// Translators: %1$s - home url, %2$s - archives page slug.
											__( 'With the currently set value, the job archives page would be accessible under the %1$s/%2$s/ url.', 'hiring-hub' ),
											homeUrl,
											settings?.general?.archiveSlug ?? DEFAULT_ARCHIVE_SLUG,
										)
								)
							) }

							/**
							 * Update the license key
							 *
							 * @param {string} value Updated license key.
							 *
							 * @return {void}
							 */
							onChange={ ( value ) => {
								// Slug can not be empty.
								if ( '' === value ) {
									value = DEFAULT_ARCHIVE_SLUG;
								}

								setSettings( {
									...settings,
									general: {
										...settings.general ?? {},
										archiveSlug: cleanForSlug( value ),
									},
								} );
							} }
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							label={ __( 'Single job post slug', 'hiring-hub' ) }
							value={ settings?.general?.postSlug ?? DEFAULT_POST_SLUG }
							help={ sprintf(
								// Translators: optional hint content.
								__( 'Slug of the single job post; must not be empty. %s', 'hiring-hub' ),
								(
									( null === homeUrl || '' === settings?.general?.postSlug )
										? ''
										: sprintf(
											// Translators: %1$s - home url, %2$s - single job post slug.
											__( 'With the currently set value, the job offers would be accessible under the %1$s/%2$s/{single-job-slug} url.', 'hiring-hub' ),
											homeUrl,
											settings?.general?.postSlug ?? DEFAULT_POST_SLUG,
										)
								)
							) }

							/**
							 * Update the license key
							 *
							 * @param {string} value Updated license key.
							 *
							 * @return {void}
							 */
							onChange={ ( value ) => {
								// Slug can not be empty.
								if ( '' === value ) {
									value = DEFAULT_POST_SLUG;
								}

								setSettings( {
									...settings,
									general: {
										...settings.general ?? {},
										postSlug: cleanForSlug( value ),
									},
								} );
							} }
						/>
					</PanelRow>
				</PanelBody>
			</Panel>
		</Fragment>
	);
};

/**
 * Props validation
 */
TabGeneral.propTypes = {
	settings: PropTypes.object.isRequired,
	setSettings: PropTypes.func.isRequired,
};
