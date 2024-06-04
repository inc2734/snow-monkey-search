import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';

import {
	SelectControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components';

import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import apiFetch from '@wordpress/api-fetch';

import metadata from './block.json';

export default function ( { attributes, setAttributes, context } ) {
	const { label, key, postType, compare, type } = attributes;

	const [ keys, setKeys ] = useState( [] );

	// Reset the key once the post type has been switched in the search box.
	useEffect( () => {
		if ( context[ 'snow-monkey-search/relatedPostType' ] !== postType ) {
			setAttributes( { key: undefined, label: null } );
		}
	}, [ context[ 'snow-monkey-search/relatedPostType' ], postType ] );

	// Get post meta keys with post type.
	useEffect( () => {
		apiFetch( {
			path: `/snow-monkey-search/v1/post-meta-keys/${
				postType || context[ 'snow-monkey-search/relatedPostType' ]
			}`,
			method: 'GET',
		} ).then( ( v ) => {
			setKeys( v );
		} );
	}, [ context[ 'snow-monkey-search/relatedPostType' ], postType ] );

	// Set default label.
	useEffect( () => {
		if ( null == label ) {
			setAttributes( {
				label: key,
			} );
		}
	}, [ key ] );

	const blockProps = useBlockProps( {
		className: 'sms-custom-field-search sms-form-control',
	} );

	let controlType = 'text';
	switch ( type ) {
		case 'numeric':
			controlType = 'number';
			break;
		case 'date':
			controlType = 'date';
			break;
		case 'datetime':
			controlType = 'datetime-local';
			break;
		case 'time':
			controlType = 'time';
			break;
	}

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
					{ 'date' === controlType ||
					'datetime-local' === controlType ||
					'time' === controlType ? (
						<div className="sms-date-control">
							<input
								type={ controlType }
								className="c-form-control"
								disabled
							/>
						</div>
					) : (
						<input
							type={ controlType }
							className="c-form-control"
							disabled
						/>
					) }
				</div>
			</div>
		</>
	);
}
