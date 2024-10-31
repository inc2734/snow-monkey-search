import {
	InnerBlocks,
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';

import {
	ToggleControl,
	__experimentalUnitControl as UnitControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

export default function ( { attributes, setAttributes, clientId } ) {
	const { flexGrow, flexShrink, flexBasis, templateLock } = attributes;

	const hasInnerBlocks = useSelect(
		( select ) =>
			!! select( blockEditorStore ).getBlock( clientId )?.innerBlocks
				?.length,
		[ clientId ]
	);

	const blockProps = useBlockProps( {
		className: 'sms-item',
		style: {
			'--sms--flex-grow': String( Number( flexGrow ) ),
			'--sms--flex-shrink': String( Number( flexShrink ) ),
			'--sms--flex-basis': flexBasis || undefined,
		},
	} );

	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		templateLock,
		renderAppender: hasInnerBlocks
			? InnerBlocks.DefaultBlockAppender
			: InnerBlocks.ButtonBlockAppender,
	} );

	return (
		<>
			<InspectorControls>
				<ToolsPanel
					label={ __( 'Block settings', 'snow-monkey-search' ) }
				>
					<ToolsPanelItem
						hasValue={ () =>
							flexGrow !== metadata.attributes.flexGrow.default
						}
						isShownByDefault
						label={ __( 'Fill' ) }
						onDeselect={ () =>
							setAttributes( {
								flexGrow: metadata.attributes.flexGrow.default,
							} )
						}
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Fill' ) }
							help={ __(
								'Allows for stretching from the basic size.',
								'snow-monkey-search'
							) }
							checked={ flexGrow }
							onChange={ ( newValue ) => {
								setAttributes( {
									flexGrow: newValue,
								} );
							} }
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							flexShrink !==
							metadata.attributes.flexShrink.default
						}
						isShownByDefault
						label={ __( 'Fit' ) }
						onDeselect={ () =>
							setAttributes( {
								flexShrink:
									metadata.attributes.flexShrink.default,
							} )
						}
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Fit' ) }
							help={ __(
								'Allow shrinkage from the basic size.',
								'snow-monkey-search'
							) }
							checked={ flexShrink }
							onChange={ ( newValue ) => {
								setAttributes( {
									flexShrink: newValue,
								} );
							} }
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						hasValue={ () =>
							flexBasis !== metadata.attributes.flexBasis.default
						}
						isShownByDefault
						label={ __( 'Basic size', 'snow-monkey-search' ) }
						onDeselect={ () =>
							setAttributes( {
								flexBasis:
									metadata.attributes.flexBasis.default,
							} )
						}
					>
						<UnitControl
							label={ __( 'Basic size', 'snow-monkey-search' ) }
							help={ __(
								'Basic size of the flex item.',
								'snow-monkey-search'
							) }
							value={ flexBasis || '' }
							onChange={ ( newValue ) => {
								setAttributes( {
									flexBasis: newValue,
								} );
							} }
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div { ...innerBlocksProps } />
		</>
	);
}
