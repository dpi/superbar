import PropTypes from "prop-types"
import { EnvIndicator } from "../components/EnvIndicator/EnvIndicator"
import { WorkflowBadge } from "../components/WorkflowBadge/WorkflowBadge"
import { UserMenu } from "../components/UserMenu/UserMenu"
import { EditMenu } from "../components/EditMenu/EditMenu"
import { ToolbarItemsProvider } from "../components/ToolbarItemsProvider/ToolbarItemsProvider"
import { ReactQueryProvider } from "../components/ReactQueryProvider/ReactQueryProvider"
import { Toolbar } from "../components/Toolbar/Toolbar"

const ToolbarApp = ({ apiUrl, path }) => (
  <ReactQueryProvider>
    <ToolbarItemsProvider path={path} apiUrl={apiUrl}>
      <Toolbar>
        <EditMenu />
        <UserMenu />
        <WorkflowBadge />
        <EnvIndicator />
      </Toolbar>
    </ToolbarItemsProvider>
  </ReactQueryProvider>
)

ToolbarApp.propTypes = {
  apiUrl: PropTypes.string.isRequired,
  path: PropTypes.string.isRequired,
}

export { ToolbarApp }
