import { Indicator } from "../Indicator/Indicator"
import { Toolbar as ToolbarComponent } from "./Toolbar"
import { Button } from "../Button/Button"
import { Edit } from "../Icon/Icon"
import { Menu } from "../Menu/Menu"
import { Badge } from "../Badge/Badge"

export default {
  component: ToolbarComponent,
}

export const Toolbar = {
  args: {
    children: (
      <>
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
        <Badge>Published</Badge>
        <Indicator color={"#FF0000"}>Prod</Indicator>
      </>
    ),
  },
}
