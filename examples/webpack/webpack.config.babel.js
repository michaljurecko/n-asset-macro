import ExtractTextPlugin from 'extract-text-webpack-plugin';
import ManifestPlugin from 'webpack-manifest-plugin';
import CleanWebpackPlugin from 'clean-webpack-plugin';

// Config
const config = {
  publicDir: 'www',
  buildDir: 'dist',
  manifest: 'manifest.json',
  jsEntrypoint: './app/scripts/index.js',
  sassEntrypoint: './app/styles/main.scss',
};

module.exports = {
  entry: {
    [`${config.buildDir}/app`]: [config.jsEntrypoint, config.sassEntrypoint],
  },
  output: {
    filename: '[name].[chunkhash:8].js',
    path: `${__dirname}/${config.publicDir}`,
  },
  devtool: 'source-map',
  module: {
    rules: [
      // JS
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
        }
      },
      // SASS
      {
        test: /\.scss$/,
        loader: ExtractTextPlugin.extract(['css-loader?sourceMap', 'sass-loader?sourceMap'])
      }
    ]
  },
  plugins: [
    new ExtractTextPlugin({
      filename: '[name].[contenthash:8].css',
      allChunks: true,
    }),
    new ManifestPlugin({
      fileName: `${config.buildDir}/${config.manifest}`,
    }),
    new CleanWebpackPlugin(`${config.publicDir}/${config.buildDir}`, { verbose: false, watch: true }),
  ]
};
