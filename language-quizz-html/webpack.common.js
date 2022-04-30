const path = require("path");

const HtmlWebpackPlugin = require("html-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyPlugin = require("copy-webpack-plugin");

const buildPath = path.resolve(__dirname, "dist");

const pages = ["landing"];

module.exports = {
  // https://webpack.js.org/configuration/mode/
  // mode: 'production',
  mode: "development",

  // This option controls if and how source maps are generated.
  // https://webpack.js.org/configuration/devtool/
  // devtool: "source-map",

  // https://webpack.js.org/concepts/entry-points/#multi-page-application
  entry: {
    index: "./src/js/main.js",
  },

  // how to write the compiled files to disk
  // https://webpack.js.org/concepts/output/
  output: {
    filename: "js/[name].js",//"js/[name].[contenthash].js",
    path: buildPath,
    clean: true,
  },

  // https://webpack.js.org/concepts/loaders/
  module: {
    rules: [
      {
        // https://webpack.js.org/loaders/babel-loader/#root
        test: /\.m?js$/i,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ['@babel/preset-env']
          },
        },
      },
      {
        // https://webpack.js.org/loaders/css-loader/#root
        test: /\.css$/i,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              importLoaders: 1,
            },
          },
          {
            loader: "postcss-loader",
          },
        ],
      },
      {
        // https://webpack.js.org/guides/asset-modules/#resource-assets
        test: /\.(png|jpe?g|gif|svg)$/i,
        use: [
          {
            loader: "file-loader",
            options: {
              name: "[name].[ext]",
              outputPath: "assets",
              publicPath: "assets",
            },
          },
        ],
      },
      {
        // https://webpack.js.org/guides/asset-modules/#replacing-inline-loader-syntax
        resourceQuery: /raw/,
        type: "asset/source",
      },
      {
        // https://webpack.js.org/loaders/html-loader/#usage
        resourceQuery: /template/,
        loader: "html-loader",
      },
    ],
  },

  // https://webpack.js.org/concepts/plugins/
  plugins: [
    new MiniCssExtractPlugin({
      filename: "styles/[name].css",//"styles/[name].[contenthash].css",
      chunkFilename: "styles/[id].css",//"styles/[id].[contenthash].css",
    }),
    new CopyPlugin({
      patterns: [
        //open when have new assets and vendors
        { from: "./src/img", to: "assets/" },
        { from: "./src/vendors", to: "vendors/" },
      ],
      options: {
        concurrency: 100,
      },
    }),
  ]
    .concat(
      pages.map(
        (page) =>
          new HtmlWebpackPlugin({
            template: `./src/layouts/${page}/tmpl.html`,
            inject: true,
            chunks: ["index"],
            filename: `${page}.html`,
          })
      )
    ),
  // [
  //   new HtmlWebpackPlugin({
  //     template: './src/page-index/tmpl.html',
  //     inject: true,
  //     chunks: ['index'],
  //     filename: 'index.html'
  //   }),
  //   new HtmlWebpackPlugin({
  //     template: './src/page-about/tmpl.html',
  //     inject: true,
  //     chunks: ['about'],
  //     filename: 'about.html'
  //   }),
  //   new HtmlWebpackPlugin({
  //     template: './src/page-contacts/tmpl.html',
  //     inject: true,
  //     chunks: ['contacts'],
  //     filename: 'contacts.html'
  //   }),
  //   new MiniCssExtractPlugin({
  //     filename: '[name].[contenthash].css',
  //     chunkFilename: '[id].[contenthash].css'
  //   })
  // ],
};
