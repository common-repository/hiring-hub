/**
 * WordPress dependencies
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { createBlock, registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Register block type
 */
registerBlockType(
	'hiring-hub/query-loop-filtering-container',
	{
		/**
		 * The block edit function
		 *
		 * @return {JSX} Edit component.
		 */
		edit: () => {
			// Collect the necessary data.
			const blockProps = useBlockProps();
			const innerBlocksProps = useInnerBlocksProps( blockProps );

			/**
			 * Render the block
			 */
			return (
				<section { ...innerBlocksProps } />
			);
		},

		/**
		 * The block save function
		 *
		 * @return {JSX} Save component.
		 */
		save: () => {
			// Collect the necessary data.
			const blockProps = useBlockProps.save();
			const innerBlocksProps = useInnerBlocksProps.save( blockProps );

			/**
			 * Render the block
			 */
			return (
				<form { ...innerBlocksProps } />
			);
		},

		/**
		 * Block transforms
		 */
		transforms: {
			from: [
				{
					type: 'block',
					blocks: [ 'core/group' ],
					transform: ( attributes, innerBlocks ) => {
						return createBlock(
							'hiring-hub/query-loop-filtering-container',
							attributes,
							innerBlocks
						);
					},
				},
			],
		},
	},
);
