import path from "path"
import resolve from "@rollup/plugin-node-resolve"
import commonjs from "@rollup/plugin-commonjs"
import replace from "@rollup/plugin-replace"
import postcss from "rollup-plugin-postcss"
import url from "@rollup/plugin-url"
import svgr from "@svgr/rollup"
import babel from "@rollup/plugin-babel"
import json from "@rollup/plugin-json"
import { terser } from "rollup-plugin-terser"

const isDevelopment = process.env.NODE_ENV === "development"

// eslint-disable-next-line
export default {
  input: "./src/superbar.js",
  output: {
    dir: "./dist",
    format: "iife",
  },
  plugins: [
    resolve({
      extensions: [".js", ".json", ".jsx"],
      preferBuiltins: false,
    }),
    // Ensures prod version of react-dom is loaded. Must be before commonjs().
    // @see https://github.com/rollup/rollup/issues/208
    replace({
      "process.env.NODE_ENV": JSON.stringify(process.env.NODE_ENV),
      "process.env.NODE_DEBUG": JSON.stringify(process.env.NODE_DEBUG),
      preventAssignment: true,
    }),
    commonjs({
      include: /node_modules/,
    }),
    postcss({
      modules: true,
    }),
    json(),
    url(),
    svgr(),
    babel({
      exclude: [/core-js/, /react-dom/],
      babelHelpers: "bundled",
    }),
    !isDevelopment &&
      terser({
        ecma: 12,
        keep_classnames: true,
      }),
  ],
  // Custom onwarn handler.
  // @see https://github.com/rollup/rollup/issues/1518#issuecomment-321875784
  onwarn(warning, warn) {
    if (
      warning.code === "THIS_IS_UNDEFINED" ||
      warning.code === "SOURCEMAP_ERROR"
    )
      return
    warn(warning)
  },
}
