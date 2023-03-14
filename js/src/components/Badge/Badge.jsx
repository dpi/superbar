import styles from "./badge.module.css"

const Badge = ({ children }) => (
  <div className={styles.badge__wrapper}>
    <div className={styles.badge}>{children}</div>
  </div>
)

export { Badge }
