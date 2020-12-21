import { useStore, EngineStore } from '../Store'
import { post } from '../Globals'

const { __ } = wp.i18n
const { ToggleControl, SelectControl, Button, Spinner, Notice } = wp.components

function Settings() {
	const { engineOptions: options, saving } = useStore( EngineStore );
	const [ status, setStatus ] = React.useState( false )

	const downloadState = { installed: JSEngine.installed, message: __( 'Downloading JS Engine essentials', 'jsengine' ), status: JSEngine.installed ? false : 'info' }
	const [ download, setInstall ] = React.useState( downloadState )

	const downloadNow = () => {
		setInstall( downloadState )
		const body = { action: 'jse_download', nonce: JSEngine.dnonce }
		post( { url: JSEngine.ajaxUrl, method: 'POST', body } ).then( res => {
			if ( res.success === true ) {
				setInstall( { installed: true, message: res.data, status: 'success' } )
				JSEngine.installed = '1'
				setTimeout( () => setInstall( { status: false } ), 3000 )
			} else {
				setInstall( { installed: false, message: res.data, status: 'error' } )
			}
		} ).catch( err => {
			setInstall( { installed: false, message: __( 'Download failed', 'jsengine' ) , status: 'error' } )
		} ).finally( () => localStorage.setItem( 'jseDownload', '1' ) )
	}

	const ErrorMessage = () => (
		<div className="jse-error">
			<span>{ __( 'JS Engine binary file download failed.', 'jsengine' ) }</span>
			<span className="retry" onClick={ downloadNow }>{ __( 'Retry', 'jsengine' ) }</span>
			<div className="manual">OR <a href={ `${ JSEngine.apiHome }/docs/js-engine/manual-download/` } target="_blank">{ __( 'Download manually as explained here', 'jsengine' ) }</a></div>
		</div>
	)

	React.useEffect( () => {
		if ( download.installed ) return

		const tried = localStorage.getItem( 'jseDownload' )
		if ( null !== tried ) {
			setInstall( { installed: false, message: <ErrorMessage />, status: 'warning' } )
			return
		}

		downloadNow()
	}, [] )

	const getTemplates = () => {
		const templates = [ { value: 'default', label: 'No change' } ]
		JSEngine.themes.forEach( ( theme ) => {
			if ( [ options.theme, options.parent ].includes( theme.value ) ) {
				theme.templates.forEach( ( template ) => {
					templates.push( { value: template, label: template } )
				} )
			}
		} )
		return templates
	}
	const [ templates, setTemplates ] = React.useState( getTemplates() )

	React.useEffect( () => {
		setTemplates( getTemplates() )
	}, [ options.theme, options.parent ] )

	const setOption = ( k, v ) => {
		EngineStore.setOption( k, v )
	}

	const reset = () => EngineStore.reset()

	const save = () => {
		EngineStore.setSaving( true )

		const body = { action: 'jse_save', options: JSON.stringify( options ), nonce: JSEngine.nonce }
		post( { url: JSEngine.ajaxUrl, method: 'POST', body } ).then( res => {
			if ( true === res ) {
				setStatus( { type: 'success', message: __( 'Saved successfully', 'jsengine' ) } )
				setTimeout( () => setStatus( false ), 3000 )
			} else if ( 2 === res ) {
				setStatus( { type: 'warning', message: __( 'No changes', 'jsengine' ) } )
				setTimeout( () => setStatus( false ), 3000 )
			} else {
				setStatus( { type: 'error', message: __( 'Error saving options', 'jsengine' ) } )
			}
		} ).catch( err => {
			setStatus( { type: 'error', message: __( 'Error saving options', 'jsengine' ) } )
		} ).finally( () => EngineStore.setSaving( false ) )
	}

	return (
		<div className="jse-settings">
			{ !! download.status && (
				<Notice status={ download.status } onRemove={ () => setInstall( { status: false } ) }>
					<div className="install-notice">
						{ download.message }
						{ ( download.status === 'info' ) && ( <Spinner /> ) }
					</div>
				</Notice>
			) }
			{ ! JSEngine.canExec && (
				<Notice status="warning" >
					<div className="exec-notice">
						{ __( 'Your server does not allow running of JS engine binary. Functionalities like Server side rendering, build, etc, will not work. Please manually fix the issue.', 'jsengine' ) }
					</div>
				</Notice>
			) }
			<ToggleControl
				label={ __( 'Enable JS Engine', 'jsengine' ) }
				help={ `${ __( 'Currently all the JS Engine functionalities are set to:', 'jsengine' ) } ${ options.enabled ? __( 'enabled', 'jsengine' ) : __( 'disabled', 'jsengine' ) }` }
				checked={ options.enabled }
				onChange={ v => setOption( 'enabled', v ) }
			/>
			<SelectControl
				label={ __( 'Load theme', 'jsengine' ) }
				value={ options.theme }
				options={ JSEngine.themes }
				onChange={ v => { setOption( 'theme', v ) } }
			/>
			<SelectControl
				label={ __( 'Load secondary theme as parent theme', 'jsengine' ) }
				value={ options.parent }
				options={ JSEngine.themes }
				onChange={ v => { setOption( 'parent', v ) } }
			/>
			<SelectControl
				label={ __( 'Force every request to render following template', 'jsengine' ) }
				value={ options.template }
				options={ templates }
				onChange={ v => { setOption( 'template', v ) } }
			/>
			<div className="jse-actions">
				{ !! status && (
					<Notice status={ status.type } onRemove={ () => setStatus( false ) }>
						<div>{ status.message }</div>
					</Notice>
				) }
				<Button isSecondary onClick={ reset }>{ __( 'Reset', 'jsengine' ) }</Button>
				<Button isPrimary disabled={ saving } onClick={ save } className="jse-save-button">
					{ saving && <Spinner /> }
					<span>{ __( 'Save', 'jsengine' ) }</span>
				</Button>
			</div>
		</div>
	)
}

export default Settings
