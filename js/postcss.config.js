const path = require("path")

const isDevelopment = process.env.NODE_ENV === "development"

module.exports = {
  plugins: {
    "postcss-preset-env": {
      stage: 0,
    },
    ...(!isDevelopment && { cssnano: {} }),
  },
}
