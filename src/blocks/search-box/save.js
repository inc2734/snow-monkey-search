import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export default function ( { attributes } ) {
	return <div { ...useInnerBlocksProps.save( useBlockProps.save() ) } />;
}
