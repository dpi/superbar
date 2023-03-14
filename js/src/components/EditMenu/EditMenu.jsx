import { Button } from "../Button/Button"
import { Edit } from "../Icon/Icon"
import { Menu } from "../Menu/Menu"
import { Toolbar } from "../Toolbar/Toolbar"
import { useToolbarItems } from "../ToolbarItemsProvider/ToolbarItemsProvider"

const EditMenu = () => {
  const { items } = useToolbarItems()
  return (
    <>
      {items?.pathLinks && (
        <Button
          menu={
            <Menu title={"Local tasks"}>
              {items.pathLinks.map(link => (
                <li key={link.path}>
                  <a href={link.path}>{link.title}</a>
                </li>
              ))}
            </Menu>
          }
        >
          <Edit />
        </Button>
      )}
    </>
  )
}

export { EditMenu }
