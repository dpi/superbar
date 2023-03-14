import { Indicator } from "../Indicator/Indicator"
import { useToolbarItems } from "../ToolbarItemsProvider/ToolbarItemsProvider"

const EnvIndicator = () => {
  const { items } = useToolbarItems()
  return (
    <>
      {items?.site?.environment?.id && (
        <Indicator color={items.site.environment.colorPrimary}>
          {items.site.environment.id}
        </Indicator>
      )}
    </>
  )
}

export { EnvIndicator }
