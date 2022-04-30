const tailwindcss = require("tailwindcss");
const purgecss = require("@fullhuman/postcss-purgecss");
const cssnano = require("cssnano");
module.exports = {
  plugins: [
    tailwindcss,
    require("autoprefixer"),
    require("postcss-nested"),
    cssnano({
      preset: "default",
    }),
    purgecss({
      content: ["./src/layouts/**/*.html", "./src//.vue", "./src/**/.jsx"],
      defaultExtractor: (content) => content.match(/[\w-/:]+(?<!:)/g) || [],
    }),
  ],
};
