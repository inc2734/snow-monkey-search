import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';

import {
	SelectControl,
	__experimentalInputControl as InputControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components';

import { useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import metadata from './block.json';

export default function ( { attributes, setAttributes } ) {
	const { label, controlType, min, max } = attributes;

	// Set default label.
	useEffect( () => {
		if ( null == label ) {
			setAttributes( { label: __( 'Period', 'snow-monkey-search' ) } );
		}
	}, [ label ] );

	const blockProps = useBlockProps( {
		className: 'sms-period-search sms-form-control',
	} );

	let pattern;
	switch ( controlType ) {
		case 'month':
			pattern = 'd{4}-d{2}';
			break;
		case 'date':
		default:
			pattern = 'd{4}-d{2}-d{2}';
			break;
	}

	return (
		<>
			<InspectorControls>
				<ToolsPanel
					label={ __( 'Block settings', 'snow-monkey-search' ) }
				>
					<div style={ { gridColumn: '1 / -1' } }>
						<span
							dangerouslySetInnerHTML={ {
								__html: sprintf(
									// translators: %1$s: <code>
									__(
										'If you want to include future posts in the search and display, use the %1$s filter hook.',
										'snow-monkey-search'
									),
									'<code>sms_includes_future_posts</code>'
								),
							} }
						/>
					</div>

					<ToolsPanelItem
						hasValue={ () =>
							controlType !==
							metadata.attributes.controlType.default
						}
						isShownByDefault
						label={ __( 'Type', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								controlType:
									metadata.attributes.controlType.default,
							} )
						}
					>
						<SelectControl
							label={ __( 'Type', 'snow-monkey-search' ) }
							value={ controlType }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									controlType: newAttribute,
								} );
							} }
							options={ [
								{
									label: __( 'Date', 'snow-monkey-search' ),
									value: 'date',
								},
								{
									label: __( 'Month', 'snow-monkey-search' ),
									value: 'month',
								},
							] }
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							min !== metadata.attributes.min.default
						}
						isShownByDefault
						label={ __( 'Minimum Date', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								min: metadata.attributes.min.default,
							} )
						}
					>
						<div className="sms-date-control">
							<InputControl
								label={ __(
									'Minimum Date',
									'snow-monkey-search'
								) }
								type={ controlType }
								pattern={ pattern }
								value={ min }
								onChange={ ( newAttribute ) => {
									setAttributes( {
										min: newAttribute,
									} );
								} }
							/>
						</div>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							max !== metadata.attributes.max.default
						}
						isShownByDefault
						label={ __( 'Maximum Date', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								max: metadata.attributes.max.default,
							} )
						}
					>
						<div className="sms-date-control">
							<InputControl
								label={ __(
									'Maximum Date',
									'snow-monkey-search'
								) }
								type={ controlType }
								pattern={ pattern }
								className="sms-date-control"
								value={ max }
								onChange={ ( newAttribute ) => {
									setAttributes( {
										max: newAttribute,
									} );
								} }
							/>
						</div>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="sms-period-search__header sms-form-control__header">
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

				<div className="sms-period-search__content sms-form-control__content">
					<div className="sms-period-search__start">
						<input
							type={ controlType }
							className="c-form-control"
							name="sms-period-start"
							pattern={ pattern }
							min={ min || undefined }
							disabled
						/>
					</div>
					<div className="sms-period-search__delimiter">
						{ __( '〜', 'snow-monkey-search' ) }
					</div>
					<div className="sms-period-search__end">
						<input
							type={ controlType }
							className="c-form-control"
							name="sms-period-end"
							pattern={ pattern }
							min={ max || undefined }
							disabled
						/>
					</div>
				</div>
			</div>
		</>
	);
}
