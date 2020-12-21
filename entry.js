if ( process.argv.length < 3 ) return

const entry = require( process.argv[3] )

if ( Object.keys( entry ).length !== 0 ) {
	console.log( entry )
}
