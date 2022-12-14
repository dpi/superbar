import styles from "./menu.module.css"

const Menu = ({ children, title }) => (
  <div className={styles.menu}>
    <div className={styles.menu__title}>{title}</div>
    <div className={styles.menu__children}>
      <ul>{children}</ul>
    </div>
  </div>
)

export { Menu }
