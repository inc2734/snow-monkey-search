const uniqBy = ( args, key ) => {
	const valueSet = new Set();
	return args.filter( ( arg ) => {
		const value = key ? arg[ key ] : arg;
		if ( valueSet.has( value ) ) {
			return false;
		}
		valueSet.add( value );
		return true;
	} );
};

export function optionsToJsonArray( text ) {
	const preOptionsArray = text.replace( /\r?\n/g, '\n' ).split( '\n' );

	const optionsMapArray = uniqBy(
		preOptionsArray.map( ( element ) => {
			const optionMap = ( () => {
				try {
					return JSON.parse( `{ ${ element } }` );
				} catch ( error ) {
					return { [ element ]: element };
				}
			} )();

			return {
				value: Object.keys( optionMap )[ 0 ],
				label: Object.values( optionMap )[ 0 ],
			};
		} ),
		'value'
	);

	return optionsMapArray.filter(
		( element ) => null != element.value && null != element.label
	);

	// return optionsMapArray.map( ( element ) => {
	// 	const o = {};
	// 	o[ element.value ?? '' ] = element.label;
	// 	return o;
	// } );
}
