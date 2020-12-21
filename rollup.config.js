/**
 * Rollup config.
 *
 * @package greenlet
 */

import babel from '@rollup/plugin-babel';
import { terser } from 'rollup-plugin-terser'
import scss from 'rollup-plugin-scss'

const GLOBALS = {
	react: 'React',
	'react-dom': 'ReactDOM'
}

const EXTERNAL = [
	'react',
	'react-dom',
]

const paths = []

paths.push( {
	inputPath : 'src/engine/engine.js',
	outputPath: 'assets/js/engine.js',
	outputMin : 'assets/js/engine.min.js',
	outputCss : 'assets/css/engine.css'
} )

const config = paths.map( ( path ) => ( {
	input: path.inputPath,
	output: [ {
		sourcemap: true,
		format: 'iife',
		name: 'app',
		file: path.outputPath,
		globals: GLOBALS,
		banner: ( 'banner' in path ) ? path.banner : '',
	}, {
		sourcemap: false,
		format: 'iife',
		name: 'app',
		file: path.outputMin,
		globals: GLOBALS,
		plugins: [ terser() ]
	} ],
	external: EXTERNAL,
	plugins: [
		babel( {
			exclude: 'node_modules/**',
			babelHelpers: 'bundled',
		} ),
		scss( { output: path.outputCss } )
	]
} ) )

export default config
