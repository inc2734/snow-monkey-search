import { registerBlockType } from '@wordpress/blocks';
import { group } from '@wordpress/icons';

import metadata from './block.json';
import edit from './edit';
import save from './save';

import config from '../config';

import './style.scss';

registerBlockType( metadata.name, {
	icon: {
		foreground: config.iconColor,
		src: group,
	},
	edit,
	save,
} );
