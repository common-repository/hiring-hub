/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Panel, PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Import styles
 */
import './styles.scss';

/**
 * SettingsSidebar component
 *
 * @param {Object} properties        Component properties object.
 * @param {string} properties.plugin Plugin key.
 *
 * @return {JSX} Settings component.
 */
export const SettingsSidebar = ( { plugin } ) => {
	// Collect the necessary data.
	const { helpLinks } = window.teydeaStudio[ plugin ].settingsPage;
	const { slug } = window.teydeaStudio[ plugin ].plugin;

	/**
	 * Render component
	 */
	return (
		<div className="tsc-settings-sidebar">
			<Panel>
				{
					/**
					 * Render help links, if provided
					 */
					( 0 < helpLinks.length ) && (
						<PanelBody
							title={ __( 'Help & support', 'hiring-hub' ) }
							initialOpen={ true }
							className="tsc-settings-sidebar__panel"
						>
							<ul>
								{
									helpLinks.map( ( { url, title }, index ) => (
										<li key={ index }>
											<a
												href={ url }
												target="_blank"
												rel="noreferrer noopener"
											>
												{ title }
											</a>
										</li>
									) )
								}
							</ul>
						</PanelBody>
					)
				}
				{
					/**
					 * Slot for the "upsell" panel
					 *
					 * @param {JSX} panel The "upsell" panel.
					 */
					applyFilters( 'hiring_hub__upsell_panel', <Fragment /> )
				}
				{
					/**
					 * Slot for the "promoted plugins" panel
					 *
					 * @param {JSX} panel The "promoted plugins" panel.
					 */
					applyFilters( 'hiring_hub__promoted_plugins_panel', <Fragment /> )
				}
				<PanelBody
					title={ __( 'Write a review', 'hiring-hub' ) }
					initialOpen={ false }
					className="tsc-settings-sidebar__panel"
				>
					<p>
						{ __( 'If you like this plugin, share it with your network and write a review on WordPress.org to help others find it. Thank you!', 'hiring-hub' ) }
					</p>
					<a
						className="components-button is-secondary is-compact"
						href={ `https://wordpress.org/support/plugin/${ slug }/reviews/#new-post` }
						rel="noopener noreferrer"
						target="_blank"
					>
						{ __( 'Write a review', 'hiring-hub' ) }
					</a>
				</PanelBody>
				<PanelBody
					title={ __( 'Share your feedback', 'hiring-hub' ) }
					initialOpen={ false }
					className="tsc-settings-sidebar__panel"
				>
					<p>
						{ __( 'We\'re eager to hear your feedback, feature requests, suggestions for improvements etc; we\'re waiting for a message from you!', 'hiring-hub' ) }
					</p>
					<a
						className="components-button is-secondary is-compact"
						href="mailto:hello@teydeastudio.com"
						rel="noopener noreferrer"
						target="_blank"
					>
						{ __( 'Contact us', 'hiring-hub' ) }
					</a>
				</PanelBody>
			</Panel>
		</div>
	);
};

/**
 * Props validation
 */
SettingsSidebar.propTypes = {
	plugin: PropTypes.string.isRequired,
};
