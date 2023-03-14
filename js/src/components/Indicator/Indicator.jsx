import styles from "./indicator.module.css"

const Indicator = ({ children, color }) => (
  <div className={styles.indicator__wrapper}>
    <div className={styles.indicator}>
      <svg
        width={"8"}
        height={"8"}
        viewBox={`0 0 8 8`}
        xmlns="http://www.w3.org/2000/svg"
      >
        <circle cx={"4"} cy={"4"} r={"4"} fill={color}></circle>
      </svg>
      {children}
    </div>
  </div>
)

export { Indicator }
