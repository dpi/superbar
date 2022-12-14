import { Menu as MenuComponent } from "./Menu"

export default {
  component: MenuComponent,
}

export const Menu = {
  args: {
    title: "Menu",
    children: (
      <>
        <li>Item</li>
        <li>Item</li>
      </>
    ),
  },
}
