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
import { store as editorStore } from '@wordpress/editor';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function ( { attributes, setAttributes, clientId } ) {
	const { relatedPostType } = attributes;

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
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		currentPostType,
		'meta'
	);

	useEffect( () => {
		// Meta is required for form calls.
		setMeta( {
			...meta,
			sms_related_post_type: relatedPostType,
		} );
	}, [ relatedPostType ] );

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
