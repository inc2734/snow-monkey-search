import { registerBlockType } from '@wordpress/blocks';
import { layout } from '@wordpress/icons';

import metadata from './block.json';
import edit from './edit';
import save from './save';

import config from '../config';

registerBlockType( metadata.name, {
	icon: {
		foreground: config.iconColor,
		src: layout,
	},
	edit,
	save,
} );
