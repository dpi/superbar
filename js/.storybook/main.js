module.exports = {
  stories: ["../src/**/*.stories.*"],
  addons: [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    {
      name: "@storybook/addon-postcss",
      options: {
        postcssLoaderOptions: {
          // Use Postcss 8
          // @see https://storybook.js.org/addons/@storybook/addon-postcss
          implementation: require("postcss"),
        },
      },
    },
  ],
  framework: {
    name: "@storybook/react-vite",
    options: {},
  },
}
