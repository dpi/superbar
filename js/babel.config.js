module.exports = {
  presets: [
    [
      "@babel/preset-env",
      {
        bugfixes: true,
        useBuiltIns: "usage",
        corejs: "3.21",
      },
    ],
    [
      "@babel/preset-react",
      {
        runtime: "automatic",
      },
    ],
  ],
  env: {
    test: {
      presets: [
        "@babel/preset-env",
        [
          "@babel/preset-react",
          {
            runtime: "automatic",
          },
        ],
      ],
    },
  },
}
