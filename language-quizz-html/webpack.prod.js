const HtmlMinimizerPlugin = require("html-minimizer-webpack-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserWebpackPlugin = require("terser-webpack-plugin");
const { merge } = require("webpack-merge");
const common = require("./webpack.common.js");

module.exports = merge(common, {
  mode: "production",
  performance: {
    hints: false,
    maxEntrypointSize: 512000,
    maxAssetSize: 512000
  },

  // https://webpack.js.org/configuration/optimization/
  optimization: {
    minimize: true,
    minimizer: [
      // https://webpack.js.org/plugins/terser-webpack-plugin/
      new TerserWebpackPlugin({
        parallel: true,
      }),
      // https://webpack.js.org/plugins/mini-css-extract-plugin/#minimizing-for-production
      new CssMinimizerPlugin(),
    ],
  },
});
