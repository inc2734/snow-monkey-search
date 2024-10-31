import { useBlockProps } from '@wordpress/block-editor';
import { SelectControl, Placeholder } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

export default function ( { attributes, setAttributes, isSelected } ) {
	const { formId } = attributes;

	const forms = useSelect( ( select ) => {
		return (
			select( 'core' ).getEntityRecords(
				'postType',
				'snow-monkey-search',
				{
					per_page: -1,
				}
			) || []
		);
	} );

	const options = useMemo( () => {
		return forms.map( ( form ) => {
			return {
				value: form.id,
				label: `${ form.id }: ${ form.title.rendered }`,
			};
		} );
	}, [ forms ] );

	return (
		<div { ...useBlockProps() }>
			{ isSelected ? (
				<Placeholder
					icon="editor-ul"
					label={ __( 'Select a search box', 'snow-monkey-search' ) }
				>
					<SelectControl
						__nextHasNoMarginBottom
						value={ formId }
						options={ [
							{
								value: 0,
								label: __(
									'Select a search box',
									'snow-monkey-search'
								),
							},
							...options,
						] }
						onChange={ ( value ) =>
							setAttributes( { formId: parseInt( value ) } )
						}
					/>
				</Placeholder>
			) : (
				<ServerSideRender
					block="snow-monkey-search/snow-monkey-search"
					attributes={ attributes }
				/>
			) }
		</div>
	);
}
