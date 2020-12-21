import Settings from './Settings'

const { TabPanel } = wp.components

function Content() {
	return (
		<TabPanel className="jse-content"
				  initialTabName="settings"
				  tabs={ [
					  {
						  name: 'settings',
						  title: 'Settings',
					  },
				  ] }>
			{
				( tab ) => {
					if ( 'settings' === tab.name ) {
						return <Settings />
					} else {
						return null
					}
				}
			}
		</TabPanel>
	)
}

export default Content
