# Webpack example

This example is based on a slightly modified  [nette/sandbox](https://github.com/nette/sandbox).

In this file, you'll find instructions [how to run this example](#example) or [how to integrate](#integration-into-the-project) n-asset-macro into the project.

**Requirements**
* PHP 5.6+
* [composer](https://getcomposer.org/)
* [node](https://nodejs.org)
* [npm](https://www.npmjs.com/) 3+

## Example

Examples are [automatically tested](https://github.com/webrouse/n-asset-macro/blob/master/tests/ExamplesTests/gulp_tests.sh) and there are attempt to keep them up-to-date. 

In case of problems or suggestions, create an [issue](https://github.com/webrouse/n-asset-macro/issues).

**Clone repository**
```sh
git clone https://github.com/webrouse/n-asset-macro
mv n-asset-macro/examples/webpack webpack-example
rm -Rf n-asset-macro
cd webpack-example
```

**Install the `webpack` command**

```sh
sudo npm install --global webpack
```

**Install JS dependencies from `package.json`**
```
npm install
```

**Install PHP dependencies from `composer.json`**
```sh
composer update
```

**Compile assets using `webpack`**

Webpack compiles `SASS` styles using [LibSass](https://github.com/sass/libsass) and `ES2015` scripts using [Babel](https://babeljs.io), see [webpack.config.babel.js](https://github.com/webrouse/n-asset-macro/blob/master/examples/webpack/webpack.config.babel.js) for details:

```sh
webpack
```

**Run PHP server**

Start the webserver and then visit http://localhost:8888 in your browser:
```sh
php -S localhost:8888 -t www
```

**Check the HTML code**

In the HTML code, you can see the generated paths to assets:
```html
...
<link rel="stylesheet" href="/dist/app.c58b9c2d.css">
...
<script src="/dist/app.7979a8f5.js"></script>
...
```

The HTML code has been generated based on this template:
```latte
{* app/presenters/templates/@layout.latte *}
...
<link rel="stylesheet" href="{asset dist/app.css}">
...
<script src="{asset dist/app.js}"></script>
...
```

Asset paths are generated in the `www/dist/manifest.json`:
 ```json
{
  "dist/app.css": "dist/app.c58b9c2d.css",
  "dist/app.css.map": "dist/app.c58b9c2d.css.map",
  "dist/app.js": "dist/app.7979a8f5.js",
  "dist/app.js.map": "dist/app.7979a8f5.js.map"
}
 ```

## Integration into the project

Here's a brief description how to integrate macro along with webpack to new or existing project based on [nette/sandbox](https://github.com/nette/sandbox).

**Install the PHP `n-asset-macro` package**

```sh
composer require webrouse/n-asset-macro
```

**Register the extension in configuration**
```yaml
# app/config/config.neon
extensions:
    assetMacro: Webrouse\AssetMacro\DI\Extension
```

**Create asset entry points**

Create entry points for javascripts and styles:
* `app/styles/main.scss`
* `app/scripts/index.js`

**Add macro to `@layout.latte`**
```latte
{* app/presenters/templates/@layout.latte *}
...
<link rel="stylesheet" href="{asset dist/app.css}">
...
<script src="{asset dist/app.js}"></script>
...
```

**Install the `webpack` command**
```sh
sudo npm install --global webpack
```

**Initialize `package.json` (if not)**
```
npm init
```

**Install the JS packages**

```sh
npm install --save-dev babel-loader babel-register babel-preset-env webpack extract-text-webpack-plugin webpack-manifest-plugin clean-webpack-plugin css-loader node-sass sass-loader 
```

**Add babel `env` preset to `package.json`**

For further configuration options, see [babel-preset-env](https://github.com/babel/babel-preset-env).

```json
  "babel": {
    "presets": [
      ["env", {
        "targets": {
          "browsers": ["last 2 versions", "> 2%"]
        }
      }]
    ]
  }
```

**Create `webpack.config.babel.js`**

```js
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
```

**Run PHP server**

Start the webserver and then visit http://localhost:8888 in your browser:
```sh
php -S localhost:8888 -t www
```

## Recommendations

This is just a simple example and inspiration for using webpack with asset macro. 

In practice, it is useful to add additional plugins: 
* [postcss-loader](https://www.npmjs.com/package/postcss-loader)
* [webpack-livereload-plugin](https://www.npmjs.com/package/webpack-livereload-plugin)
* [sasslint-webpack-plugin](https://www.npmjs.com/package/sasslint-webpack-plugin)
* [webpack-eslint-plugin](https://www.npmjs.com/package/webpack-eslint-plugin)







