import { Button as ButtonComponent } from "./Button"
import { Edit } from "../Icon/Icon"

export default {
  component: ButtonComponent,
}

export const Button = {
  args: {
    children: (
      <>
        <Edit /> Hi
      </>
    ),
  },
}
