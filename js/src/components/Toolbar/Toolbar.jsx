import PropTypes from "prop-types"
import styles from "./toolbar.module.css"

const Toolbar = ({ children }) => (
  <div className={styles.toolbar}>{children}</div>
)

Toolbar.propTypes = {}

export { Toolbar }
