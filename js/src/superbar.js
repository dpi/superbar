import { createRoot } from "react-dom/client"
import { ToolbarApp } from "./app/ToolbarApp"

Drupal.behaviors["superbar"] = {
  attach(context) {
    const [element] = once("superbar", `#superbar`, context)
    if (element) {
      const root = createRoot(element, { identifierPrefix: `superbar:` })
      const props = drupalSettings?.superbar ?? {}
      root.render(<ToolbarApp {...props} />)
    }
  },
}
