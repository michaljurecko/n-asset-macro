# Grunt example

This example is based on a slightly modified  [nette/sandbox](https://github.com/nette/sandbox).

In this file, you'll find instructions [how to run this example](#example) or [how to integrate](#integration-into-the-project) n-asset-macro into the project.

**Requirements**
* PHP 5.6+
* [composer](https://getcomposer.org/)
* [node](https://nodejs.org)
* [npm](https://www.npmjs.com/) 3+

## Example

Examples are [automatically tested](https://github.com/webrouse/n-asset-macro/blob/master/tests/ExamplesTests/grunt_tests.sh) and there is an attempt to keep them up-to-date. 

In case of problems or suggestions, create an [issue](https://github.com/webrouse/n-asset-macro/issues).

**Clone repository**
```sh
git clone https://github.com/webrouse/n-asset-macro
mv n-asset-macro/examples/grunt grunt-example
rm -Rf n-asset-macro
cd grunt-example
```

**Install the `grunt` command**

```sh
sudo npm install --global grunt-cli
```

**Install JS dependencies from `package.json`**
```
npm install
```

**Install PHP dependencies from `composer.json`**
```sh
composer update
```

**Compile assets using `grunt`**

Grunt compiles `SASS` styles using [LibSass](https://github.com/sass/libsass) and `ES2015` scripts using [Babel](https://babeljs.io), see [gruntfile.js](https://github.com/webrouse/n-asset-macro/blob/master/examples/grunt/gruntfile.js) for details:

```sh
grunt
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
<link rel="stylesheet" href="/dist/css/app.a22bb335.css">
...
<script src="/dist/js/app.aef33887.js"></script>
...
```

The HTML code has been generated based on this template:
```latte
{* app/presenters/templates/@layout.latte *}
...
<link rel="stylesheet" href="{asset dist/css/app.css}">
...
<script src="{asset dist/js/app.js}"></script>
...
```

Asset paths are generated in the `www/dist/manifest.json`:
 ```json
{
    "dist/css/app.css": "dist/css/app.a22bb335.css",
    "dist/css/app.css.map": "dist/css/app.a22bb335.css.map",
    "dist/js/app.js": "dist/js/app.aef33887.js",
    "dist/js/app.js.map": "dist/js/app.aef33887.js.map"
}
 ```

## Integration into the project

Here's a brief description how to integrate macro along with gulp to new or existing project based on [nette/sandbox](https://github.com/nette/sandbox).

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
<link rel="stylesheet" href="{asset dist/css/app.css}">
...
<script src="{asset dist/js/app.js}"></script>
...
```

**Install the `grunt` command**
```sh
sudo npm install --global grunt-cli
```

**Initialize `package.json` (if not)**
```
npm init
```

**Install the JS packages**

```sh
npm install --save-dev babel-preset-env grunt grunt-babel grunt-sass grunt-filerev grunt-filerev-assets grunt-contrib-clean load-grunt-tasks
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

**Create `gruntfile.js`**

```js
module.exports = (grunt) => {
  // Config
  const config = {
    publicDir: 'www',
    buildDir: 'dist',
    manifest: 'manifest.json',
    jsEntrypoint: 'app/scripts/index.js',
    sassEntrypoint: 'app/styles/main.scss',
  };

  require('load-grunt-tasks')(grunt);
  grunt.initConfig({
    // JS
    babel: {
      options: { sourceMap: true },
      dist: {
        files: {
          [`${config.publicDir}/${config.buildDir}/js/app.js`]: config.jsEntrypoint,
        }
      }
    },
    // SASS
    sass: {
      options: { sourceMap: true, sourceMapContents: true },
      dist: {
        files: {
          [`${config.publicDir}/${config.buildDir}/css/app.css`]: config.sassEntrypoint,
      }
      }
    },
    // Revision manifest
    filerev: {
      options: {
        algorithm: 'md5',
        length: 8
      },
      scripts: {
        files: [{
          src: [
            `${config.publicDir}/${config.buildDir}/**/*.js`,
          ]
        }]
      },
      styles: {
        files: [{
          src: [
            `${config.publicDir}/${config.buildDir}/**/*.css`,
          ]
        }]
      },
    },
    filerev_assets: {
      dist: {
        options: {
          dest: `${config.publicDir}/${config.buildDir}/${config.manifest}`,
          cwd: `${config.publicDir}/`,
          prettyPrint: true,
        }
      }
    },
    // Clean
    clean: {
      manifest: [`${config.publicDir}/${config.buildDir}/${config.manifest}`],
      scripts:  [`${config.publicDir}/${config.buildDir}/js`],
      styles:   [`${config.publicDir}/${config.buildDir}/css`],
    },
  });


  grunt.loadNpmTasks('grunt-filerev');
  grunt.loadNpmTasks('grunt-filerev-assets');

  grunt.registerTask('scripts', ['clean:scripts', 'babel', 'filerev:scripts', 'filerev_assets']);
  grunt.registerTask('styles',  ['clean:styles',  'sass',  'filerev:styles',  'filerev_assets']);
  grunt.registerTask('default', ['styles', 'scripts']);
};
```

**Run PHP server**

Start the webserver and then visit http://localhost:8888 in your browser:
```sh
php -S localhost:8888 -t www
```

## Recommendations

This is just a simple example and inspiration for using gulp with asset macro. 

In practice, it is useful to add additional plugins: 
* [grunt-autoprefixer](https://www.npmjs.com/package/grunt-autoprefixer)
* [grunt-livereload](https://www.npmjs.com/package/grunt-livereload)
* [grunt-sass-lint](https://www.npmjs.com/package/grunt-sass-lint)
* [grunt-eslint](https://www.npmjs.com/package/grunt-eslint)
* [grunt-nittro](https://www.npmjs.com/package/grunt-nittro)
* [grunt-ftp-push](https://www.npmjs.com/package/grunt-ftp-push)







