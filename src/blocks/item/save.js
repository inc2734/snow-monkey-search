import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export default function ( { attributes } ) {
	const { flexGrow, flexShrink, flexBasis } = attributes;

	return (
		<div
			{ ...useInnerBlocksProps.save(
				useBlockProps.save( {
					className: 'sms-item',
					style: {
						'--sms--flex-grow': String( Number( flexGrow ) ),
						'--sms--flex-shrink': String( Number( flexShrink ) ),
						'--sms--flex-basis': flexBasis || undefined,
					},
				} )
			) }
		/>
	);
}
