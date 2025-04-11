import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';

import {
	TextControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components';

import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

export default function ( { attributes, setAttributes } ) {
	const { label, placeholder } = attributes;

	// Set default label.
	useEffect( () => {
		if ( null == label ) {
			setAttributes( { label: __( 'Keywords', 'snow-monkey-search' ) } );
		}
	}, [ label ] );

	const blockProps = useBlockProps( {
		className: 'sms-keyword-search sms-form-control',
	} );

	return (
		<>
			<InspectorControls>
				<ToolsPanel
					label={ __( 'Block settings', 'snow-monkey-search' ) }
				>
					<ToolsPanelItem
						hasValue={ () =>
							placeholder !==
							metadata.attributes.placeholder.default
						}
						isShownByDefault
						label={ __( 'Placeholder', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								placeholder:
									metadata.attributes.placeholder.default,
							} )
						}
					>
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Placeholder', 'snow-monkey-search' ) }
							value={ placeholder }
							onChange={ ( newAttribute ) =>
								setAttributes( { placeholder: newAttribute } )
							}
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="sms-keyword-search__header sms-form-control__header">
					<RichText
						tagName="strong"
						value={ label }
						onChange={ ( newAttribute ) => {
							setAttributes( {
								label: newAttribute,
							} );
						} }
						placeholder={ __( 'Label…', 'snow-monkey-search' ) }
					/>
				</div>

				<div className="sms-keyword-search__content sms-form-control__content">
					<input
						type="text"
						className="c-form-control"
						name="s"
						placeholder={ placeholder }
						disabled
					/>
				</div>
			</div>
		</>
	);
}
