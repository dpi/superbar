import styles from "./button.module.css"
import { useState } from "react"

const Button = ({ children, menu }) => {
  const [open, setOpen] = useState()
  return (
    <div className={styles.button__wrapper}>
      <button className={styles.button} onClick={() => setOpen(!open)}>
        {children}
      </button>
      <div hidden={!open} className={styles.button__menu}>
        {menu}
      </div>
    </div>
  )
}

export { Button }
