import Content from './Content'

function Canvas() {
	return (
		<div className="jse-canvas">
			<div className="jse-header">
				<div className="jse-title">{ JSEngine.title }</div>
			</div>
			<Content />
		</div>
	)
}

export default Canvas
