import { Toolbar as ToolbarComponent } from "./Toolbar"
import { Button } from "../Button/Button"
import { Edit } from "../Icon/Icon"
import { Menu } from "../Menu/Menu"

export default {
  component: ToolbarComponent,
}

export const Toolbar = {
  args: {
    children: (
      <Button
        menu={
          <Menu title={"Menu"}>
            <li>Item</li>
            <li>Item</li>
          </Menu>
        }
      >
        <Edit />
      </Button>
    ),
  },
}
