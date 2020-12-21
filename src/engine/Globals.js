export const post = ( args ) => {
	const inHeaders = args.headers || {}
	const headers = { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }
	Object.assign( headers, inHeaders )

	const body = Object.keys( args.body ).map( k => `${ encodeURIComponent( k ) }=${ encodeURIComponent( args.body[ k ] ) }` ).join( '&' )
	return fetch( args.url, { method: 'POST', headers, body } ).then( r => r.ok ? r.json() : Promise.reject( r ) )
}
