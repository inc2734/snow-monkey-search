import {
	InnerBlocks,
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';

import { useEntityProp, store as coreStore } from '@wordpress/core-data';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function ( { attributes, setAttributes, clientId } ) {
	const { relatedPostType, displayArea } = attributes;

	const hasInnerBlocks = useSelect(
		( select ) =>
			!! select( blockEditorStore ).getBlock( clientId )?.innerBlocks
				?.length,
		[ clientId ]
	);

	const allPostTypes = useSelect( ( select ) => {
		const { getPostTypes } = select( coreStore );

		const _allPostTypes = getPostTypes( { per_page: -1 } ) || [];

		return _allPostTypes.filter(
			( type ) =>
				type.viewable &&
				! type.hierarchical &&
				'media' !== type.rest_base
		);
	} );

	const currentPostType = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		currentPostType,
		'meta'
	);

	// Set initial display area to meta.
	useEffect( () => {
		if ( ! meta?.sms_related_post_type || ! meta?.sms_display_area ) {
			setMeta( {
				...meta,
				sms_related_post_type: ! meta?.sms_related_post_type
					? relatedPostType
					: undefined,
				sms_display_area: ! meta?.sms_display_area
					? displayArea
					: undefined,
			} );
		}
	}, [] );

	const blockProps = useBlockProps( {
		className: 'sms-search-box',
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'sms-search-box__content',
		},
		{
			renderAppender: hasInnerBlocks
				? InnerBlocks.DefaultBlockAppender
				: InnerBlocks.ButtonBlockAppender,
		}
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) }>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Post Type', 'snow-monkey-search' ) }
						help={ __(
							'Custom post archives displaying this search box',
							'snow-monkey-search'
						) }
						value={ relatedPostType || '' }
						onChange={ ( newAttribute ) => {
							setAttributes( {
								relatedPostType: newAttribute,
							} );

							setMeta( {
								...meta,
								sms_related_post_type: newAttribute,
							} );
						} }
						options={ [
							{
								label: '',
								value: '',
							},
							...allPostTypes.map( ( postType ) => ( {
								label: postType.name,
								value: postType.slug,
							} ) ),
						] }
					/>

					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Display Area', 'snow-monkey-search' ) }
						value={ displayArea }
						onChange={ ( newAttribute ) => {
							setAttributes( {
								displayArea: newAttribute,
							} );

							setMeta( {
								...meta,
								sms_display_area: newAttribute,
							} );
						} }
						options={ [
							{
								label: __( 'Main', 'snow-monkey-search' ),
								value: 'main',
							},
							{
								label: __( 'Sidebar', 'snow-monkey-search' ),
								value: 'sidebar',
							},
						] }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div { ...innerBlocksProps } />

				<div className="sms-search-box__action">
					<button className="sms-search-box__clear" type="button">
						{ __( 'Clear Filter', 'snow-monkey-search' ) }
					</button>

					<button
						className="c-btn sms-search-box__submit"
						type="button"
					>
						{ __( 'Apply Filter', 'snow-monkey-search' ) }
					</button>
				</div>
			</div>
		</>
	);
}
