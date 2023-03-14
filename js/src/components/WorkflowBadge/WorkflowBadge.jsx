import { Badge } from "../Badge/Badge"
import { useToolbarItems } from "../ToolbarItemsProvider/ToolbarItemsProvider"

const WorkflowBadge = () => {
  const { items } = useToolbarItems()
  return (
    <>
      {items?.route?.currentStateLabel && (
        <Badge>{items.route.currentStateLabel}</Badge>
      )}
    </>
  )
}

export { WorkflowBadge }
