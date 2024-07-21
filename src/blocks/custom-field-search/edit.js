import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';

import {
	SelectControl,
	TextareaControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';

import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import apiFetch from '@wordpress/api-fetch';

import { optionsToJsonArray } from './helper';

import metadata from './block.json';

export default function ( { attributes, setAttributes, context } ) {
	const {
		label,
		key,
		postType,
		controlType,
		options,
		compare,
		type,
		flow,
		itemMinWidth,
	} = attributes;

	const [ keys, setKeys ] = useState( [] );
	const [ finalControlType, setFinalControlType ] = useState( controlType );
	const [ finalOptions, setFinalOptions ] = useState( [] );

	// Reset the key once the post type has been switched in the search box.
	useEffect( () => {
		if ( context[ 'snow-monkey-search/relatedPostType' ] !== postType ) {
			setAttributes( { key: undefined, label: null } );
		}
	}, [ context[ 'snow-monkey-search/relatedPostType' ], postType ] );

	// Get post meta keys with post type.
	useEffect( () => {
		setKeys( [] );

		apiFetch( {
			path: `/snow-monkey-search/v1/post-meta-keys/${ context[ 'snow-monkey-search/relatedPostType' ] }`,
			method: 'GET',
		} ).then( ( v ) => {
			setKeys( v );
		} );
	}, [ context[ 'snow-monkey-search/relatedPostType' ] ] );

	// Set default label.
	useEffect( () => {
		if ( null == label ) {
			setAttributes( {
				label: key,
			} );
		}
	}, [ key ] );

	// Set final control type.
	useEffect( () => {
		if ( 'text' === controlType ) {
			switch ( type ) {
				case 'numeric':
					setFinalControlType( 'number' );
					break;
				case 'date':
					setFinalControlType( 'date' );
					break;
				case 'datetime':
					setFinalControlType( 'datetime-local' );
					break;
				case 'time':
					setFinalControlType( 'time' );
					break;
				default:
					setFinalControlType( 'text' );
			}
		} else {
			setFinalControlType( controlType );
		}
	}, [ controlType, type ] );

	// Set default options
	useEffect( () => {
		if (
			'checks' === finalControlType ||
			'radios' === finalControlType ||
			'select' === finalControlType
		) {
			if ( '' === options ) {
				setAttributes( {
					options: 'value1\n"value2" : "label2"\n"value3" : "label3"',
				} );
			}
		} else {
			setAttributes( {
				options: '',
			} );
		}
	}, [ finalControlType, options ] );

	// Set final options.
	useEffect( () => {
		if ( !! options ) {
			setFinalOptions( optionsToJsonArray( options ) );
		} else {
			setFinalOptions( [] );
		}
	}, [ options ] );

	const blockProps = useBlockProps( {
		className: 'sms-custom-field-search sms-form-control',
	} );

	return (
		<>
			<InspectorControls>
				<ToolsPanel
					label={ __( 'Block settings', 'snow-monkey-search' ) }
				>
					<ToolsPanelItem
						hasValue={ () =>
							key !== metadata.attributes.key.default
						}
						isShownByDefault
						label={ __( 'Key', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								key: metadata.attributes.key.default,
								label: metadata.attributes.label.default,
								postType: metadata.attributes.postType.default,
							} )
						}
					>
						<SelectControl
							label={ __( 'Key', 'snow-monkey-search' ) }
							value={ key }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									key: newAttribute,
									label: undefined,
									postType:
										context[
											'snow-monkey-search/relatedPostType'
										],
								} );
							} }
							options={ [
								{
									label: '',
									value: undefined,
								},
								...keys.map( ( v ) => ( {
									label: v,
									value: v,
								} ) ),
							] }
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							controlType !==
							metadata.attributes.controlType.default
						}
						isShownByDefault
						label={ __( 'Control Type', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								controlType:
									metadata.attributes.controlType.default,
							} )
						}
					>
						<SelectControl
							label={ __( 'Control Type', 'snow-monkey-search' ) }
							help={ __(
								'For "Text," depending on the "Type," it will automatically change to the appropriate control. For "Checkbox," "Radio Button," and "Select Box," you need to set the choices.',
								'snow-monkey-search'
							) }
							value={ controlType }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									controlType: newAttribute,
								} );
							} }
							options={ [
								{
									label: __( 'Text', 'snow-monkey-search' ),
									value: 'text',
								},
								{
									label: __( 'Checks', 'snow-monkey-search' ),
									value: 'checks',
								},
								{
									label: __( 'Radios', 'snow-monkey-search' ),
									value: 'radios',
								},
								{
									label: __( 'Select', 'snow-monkey-search' ),
									value: 'select',
								},
							] }
						/>
					</ToolsPanelItem>

					{ [ 'checks', 'radios' ].includes( controlType ) && (
						<ToolsPanelItem
							hasValue={ () =>
								flow !== metadata.attributes.flow.default
							}
							isShownByDefault
							label={ __( 'Flow', 'snow-monkey-search' ) }
							onDeselect={ () =>
								setAttributes( {
									flow: metadata.attributes.flow.default,
								} )
							}
						>
							<SelectControl
								label={ __( 'Flow', 'snow-monkey-search' ) }
								value={ flow }
								onChange={ ( newAttribute ) => {
									setAttributes( {
										flow: newAttribute,
									} );
								} }
								options={ [
									{
										label: __(
											'Inline',
											'snow-monkey-search'
										),
										value: 'inline',
									},
									{
										label: __(
											'Stack',
											'snow-monkey-search'
										),
										value: 'stack',
									},
									{
										label: __(
											'Grid',
											'snow-monkey-search'
										),
										value: 'grid',
									},
								] }
							/>
						</ToolsPanelItem>
					) }

					{ 'grid' === flow && (
						<ToolsPanelItem
							hasValue={ () =>
								itemMinWidth !==
								metadata.attributes.itemMinWidth.default
							}
							isShownByDefault
							label={ __(
								'Item Minimum Width',
								'snow-monkey-search'
							) }
							onDeselect={ () =>
								setAttributes( {
									itemMinWidth:
										metadata.attributes.itemMinWidth
											.default,
								} )
							}
						>
							<UnitControl
								label={ __(
									'Item Minimum Width',
									'snow-monkey-search'
								) }
								value={ itemMinWidth }
								onChange={ ( newAttribute ) => {
									setAttributes( {
										itemMinWidth: newAttribute,
									} );
								} }
							/>
						</ToolsPanelItem>
					) }

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
						{ ( 'checks' === finalControlType ||
							'radios' === finalControlType ||
							'select' === finalControlType ) && (
							<TextareaControl
								label={ __( 'options', 'snow-monkey-search' ) }
								value={ options }
								help={ sprintf(
									// translators: %1$s: line-break-char.
									__(
										'Required. Enter in the following format: "value" : "label"%1$s or value%1$s',
										'snow-monkey-search'
									),
									'\u21B5'
								) }
								onChange={ ( newAttribute ) => {
									setAttributes( {
										options: newAttribute,
									} );
								} }
							/>
						) }
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							type !== metadata.attributes.type.default
						}
						isShownByDefault
						label={ __( 'Type', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								type: metadata.attributes.type.default,
							} )
						}
					>
						<SelectControl
							label={ __( 'Type', 'snow-monkey-search' ) }
							value={ type }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									type: newAttribute,
								} );
							} }
							options={ [
								{
									label: __(
										'NUMERIC',
										'snow-monkey-search'
									),
									value: 'numeric',
								},
								{
									label: __( 'CHAR', 'snow-monkey-search' ),
									value: 'char',
								},
								{
									label: __( 'DATE', 'snow-monkey-search' ),
									value: 'date',
								},
								{
									label: __(
										'DATETIME',
										'snow-monkey-search'
									),
									value: 'datetime',
								},
								{
									label: __( 'TIME', 'snow-monkey-search' ),
									value: 'time',
								},
							] }
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							compare !== metadata.attributes.compare.default
						}
						isShownByDefault
						label={ __( 'Compare', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								compare: metadata.attributes.compare.default,
							} )
						}
					>
						<SelectControl
							label={ __( 'Compare', 'snow-monkey-search' ) }
							value={ compare }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									compare: newAttribute,
								} );
							} }
							options={ [
								{
									label: '=',
									value: '=',
								},
								{
									label: '!=',
									value: '!=',
								},
								{
									label: '>',
									value: '>',
								},
								{
									label: '>=',
									value: '>=',
								},
								{
									label: '<',
									value: '<',
								},
								{
									label: '<=',
									value: '<=',
								},
								{
									label: 'LIKE',
									value: 'LIKE',
								},
								{
									label: 'NOT LIKE',
									value: 'NOT LIKE',
								},
							] }
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="sms-custom-field-search__header sms-form-control__header">
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

				<div className="sms-custom-field-search__content sms-form-control__content">
					{ ( 'date' === finalControlType ||
						'datetime-local' === finalControlType ||
						'time' === finalControlType ) && (
						<div className="sms-date-control">
							<input
								type={ finalControlType }
								className="c-form-control"
								disabled
							/>
						</div>
					) }

					{ 'checks' === finalControlType && (
						<div
							className={ `sms-checkboxes sms-is-layout-${ flow }` }
							style={ {
								'--sms--item-min-width':
									itemMinWidth || undefined,
							} }
						>
							{ finalOptions.map( ( option ) => (
								<label key={ option.value }>
									<span className="c-checkbox">
										<input
											type="checkbox"
											className="c-checkbox__control"
											value={ option.value }
											disabled
										/>
										<span className="c-checkbox__label">
											{ option.label }
										</span>
									</span>
								</label>
							) ) }
						</div>
					) }

					{ 'radios' === finalControlType && (
						<div
							className={ `sms-radios sms-is-layout-${ flow }` }
							style={ {
								'--sms--item-min-width':
									itemMinWidth || undefined,
							} }
						>
							{ finalOptions.map( ( option ) => (
								<label key={ option.value }>
									<span className="c-radio">
										<input
											type="radio"
											className="c-radio__control"
											value={ option.value }
											disabled
										/>
										<span className="c-radio__label">
											{ option.label }
										</span>
									</span>
								</label>
							) ) }
						</div>
					) }

					{ 'select' === finalControlType && (
						<div className="sms-select">
							<div className="c-select">
								<select className="c-select__control" disabled>
									<option value=""></option>
									{ finalOptions.map( ( option ) => (
										<option
											key={ option.value }
											value={ option.value }
										>
											{ option.label }
										</option>
									) ) }
								</select>
								<span className="c-select__toggle"></span>
							</div>
						</div>
					) }

					{ ( 'text' === finalControlType ||
						'number' === finalControlType ) && (
						<input
							type={ finalControlType }
							className="c-form-control"
							disabled
						/>
					) }
				</div>
			</div>
		</>
	);
}
