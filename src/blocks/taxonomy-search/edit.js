import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';

import {
	SelectControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';

import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

export default function ( { attributes, setAttributes, context } ) {
	const { label, controlType, postType, taxonomy, flow, itemMinWidth } =
		attributes;

	// Reset the taxonomy once the post type has been switched in the search box.
	useEffect( () => {
		if ( context[ 'snow-monkey-search/relatedPostType' ] !== postType ) {
			setAttributes( { taxonomy: undefined } );
		}
	}, [ context[ 'snow-monkey-search/relatedPostType' ], postType ] );

	const taxonomiesWithRelatedPostType = useSelect(
		( select ) => {
			const { getPostType, getTaxonomy } = select( coreStore );

			if ( ! context[ 'snow-monkey-search/relatedPostType' ] ) {
				return [];
			}

			const relatedPostType = getPostType(
				context[ 'snow-monkey-search/relatedPostType' ]
			);

			const loadedTaxonomies = relatedPostType?.taxonomies
				.map( ( _taxonomy ) => {
					const _taxonomyObj = getTaxonomy( _taxonomy );
					return _taxonomyObj?.visibility?.show_ui
						? _taxonomyObj
						: false;
				} )
				.filter( Boolean );

			return loadedTaxonomies || [];
		},
		[ context[ 'snow-monkey-search/relatedPostType' ] ]
	);

	const taxonomyAvailabled = taxonomiesWithRelatedPostType?.some(
		( value ) => value.slug === taxonomy
	);

	const terms = useSelect(
		( select ) => {
			return taxonomyAvailabled
				? select( coreStore ).getEntityRecords( 'taxonomy', taxonomy, {
						per_page: -1,
				  } ) || []
				: [];
		},
		[ taxonomy, taxonomyAvailabled ]
	);

	// Set default label.
	useEffect( () => {
		if ( null == label ) {
			setAttributes( {
				label: taxonomiesWithRelatedPostType?.filter(
					( value ) => value.slug === taxonomy
				)?.[ 0 ]?.name,
			} );
		}
	}, [ label, taxonomy, taxonomiesWithRelatedPostType ] );

	const blockProps = useBlockProps( {
		className: 'sms-taxonomy-search sms-form-control',
	} );

	return (
		<>
			<InspectorControls>
				<ToolsPanel
					label={ __( 'Block settings', 'snow-monkey-search' ) }
				>
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
							__nextHasNoMarginBottom
							label={ __( 'Control Type', 'snow-monkey-search' ) }
							value={ controlType }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									controlType: newAttribute,
								} );
							} }
							options={ [
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
								__nextHasNoMarginBottom
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
							taxonomy !== metadata.attributes.taxonomy.default
						}
						isShownByDefault
						label={ __( 'Taxonomy', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								taxonomy: metadata.attributes.taxonomy.default,
								label: metadata.attributes.label.default,
								postType: metadata.attributes.postType.default,
							} )
						}
					>
						<SelectControl
							__nextHasNoMarginBottom
							label={ __( 'Taxonomy', 'snow-monkey-search' ) }
							value={ taxonomy }
							onChange={ ( newAttribute ) => {
								setAttributes( {
									taxonomy: newAttribute,
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
								...( taxonomiesWithRelatedPostType || [] ).map(
									( value ) => ( {
										label: value.name,
										value: value.slug,
									} )
								),
							] }
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div { ...blockProps }>
				{ ( () => {
					const isAvailable = !! taxonomy;
					const isLoading =
						1 > taxonomiesWithRelatedPostType.lengt ||
						1 > terms.length;

					if ( ! isAvailable ) {
						return (
							<>
								{ __(
									'Taxonomy is not availabled.',
									'snow-monkey-search'
								) }
							</>
						);
					} else if ( isLoading ) {
						return <>{ __( 'Loading…', 'snow-monkey-search' ) }</>;
					}

					return (
						<>
							<div className="sms-taxonomy-search__header sms-form-control__header">
								<RichText
									tagName="strong"
									value={ label }
									onChange={ ( newAttribute ) => {
										setAttributes( {
											label: newAttribute,
										} );
									} }
									placeholder={ __(
										'Label…',
										'snow-monkey-search'
									) }
								/>
							</div>

							<div className="sms-taxonomy-search__content sms-form-control__content">
								{ 'checks' === controlType && (
									<div
										className={ `sms-checkboxes sms-is-layout-${ flow }` }
										style={ {
											'--sms--item-min-width':
												itemMinWidth || undefined,
										} }
									>
										{ terms.map( ( term ) => (
											<label key={ term.slug }>
												<span className="c-checkbox">
													<input
														type="checkbox"
														className="c-checkbox__control"
														value={ term.slug }
														disabled
													/>
													<span className="c-checkbox__label">
														{ term.name }
													</span>
												</span>
											</label>
										) ) }
									</div>
								) }

								{ 'radios' === controlType && (
									<div
										className={ `sms-radios sms-is-layout-${ flow }` }
										style={ {
											'--sms--item-min-width':
												itemMinWidth || undefined,
										} }
									>
										{ terms.map( ( term ) => (
											<label key={ term.slug }>
												<span className="c-radio">
													<input
														type="radio"
														className="c-radio__control"
														value={ term.slug }
														disabled
													/>
													<span className="c-radio__label">
														{ term.name }
													</span>
												</span>
											</label>
										) ) }
									</div>
								) }

								{ 'select' === controlType && (
									<div className="sms-select">
										<div className="c-select">
											<select
												className="c-select__control"
												disabled
											>
												<option value=""></option>
												{ terms.map( ( term ) => (
													<option
														key={ term.slug }
														value={ term.slug }
													>
														{ term.name }
													</option>
												) ) }
											</select>
											<span className="c-select__toggle"></span>
										</div>
									</div>
								) }
							</div>
						</>
					);
				} )() }
			</div>
		</>
	);
}
