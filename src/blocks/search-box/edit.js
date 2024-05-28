import {
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

import { BaseControl, PanelBody, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function ( { attributes, setAttributes } ) {
	const { postTypes, templateLock } = attributes;

	const allPostTypes = useSelect( ( select ) => {
		const { getPostTypes } = select( 'core' );

		const _allPostTypes = getPostTypes( { per_page: -1 } ) || [];

		return _allPostTypes.filter(
			( type ) =>
				type.viewable &&
				! type.hierarchical &&
				'media' !== type.rest_base
		);
	} );

	const blockProps = useBlockProps( {} );

	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		templateLock,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) }>
					<BaseControl
						label={ __(
							'Custom post archives displaying this search box',
							'snow-monkey-search'
						) }
					>
						{ allPostTypes.map( ( postType ) => (
							<ToggleControl
								key={ postType.slug }
								label={ postType.name }
								checked={ postTypes.includes( postType.slug ) }
								onChange={ ( newAttribute ) => {
									const newPostTypes = newAttribute
										? [ ...postTypes, postType.slug ]
										: postTypes.filter(
												( value ) =>
													value !== postType.slug
										  );

									setAttributes( {
										postTypes: Array.from(
											new Set( newPostTypes )
										),
									} );
								} }
							/>
						) ) }
					</BaseControl>
				</PanelBody>
			</InspectorControls>

			<div { ...innerBlocksProps } />
		</>
	);
}
