import { Button } from "../Button/Button"
import { User } from "../Icon/Icon"
import { Menu } from "../Menu/Menu"
import { Toolbar } from "../Toolbar/Toolbar"
import { useToolbarItems } from "../ToolbarItemsProvider/ToolbarItemsProvider"

const UserMenu = () => {
  const { items } = useToolbarItems()
  return (
    <>
      {items?.currentUser && (
        <Button
          menu={
            <Menu title={"User actions"}>
              {items.currentUser.view && (
                <li>
                  <a href={items.currentUser.view}>View</a>
                </li>
              )}
              {items.currentUser.edit && (
                <li>
                  <a href={items.currentUser.edit}>Edit</a>
                </li>
              )}
              <li>
                <a href={"/user/logout"}>Logout</a>
              </li>
            </Menu>
          }
        >
          <User />
        </Button>
      )}
    </>
  )
}

export { UserMenu }
