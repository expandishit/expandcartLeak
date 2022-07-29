const path = require('path');

module.exports = {
  mode: "development",
  watch: true,
  entry: {
    app: "./src/index.js"
  },
  output: {
    filename: 'js/[name].bundle.js',
    path: path.resolve(__dirname, 'dist'), // base path where to send compiled assets
    publicPath: '/' // base path where referenced files will be look for
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader"
        }
      }
    ]
  }
};
