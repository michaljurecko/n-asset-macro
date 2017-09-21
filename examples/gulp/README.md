# Gulp example

This example is based on a slightly modified  [nette/sandbox](https://github.com/nette/sandbox).

In this file, you'll find instructions [how to run this example](#example) or [how to integrate](#integration-into-the-project) n-asset-macro into the project.

**Requirements**
* PHP 5.6+
* [composer](https://getcomposer.org/)
* [node](https://nodejs.org)
* [npm](https://www.npmjs.com/) 3+

## Example

Examples are [automatically tested](https://github.com/webrouse/n-asset-macro/blob/master/tests/ExamplesTests/gulp_tests.sh) and there is an attempt to keep them up-to-date. 

In case of problems or suggestions, create an [issue](https://github.com/webrouse/n-asset-macro/issues).

**Clone repository**
```sh
git clone https://github.com/webrouse/n-asset-macro
mv n-asset-macro/examples/gulp gulp-example
rm -Rf n-asset-macro
cd gulp-example
```

**Install the `gulp` command**

```sh
sudo npm install --global gulp-cli
```

**Install JS dependencies from `package.json`**
```
npm install
```

**Install PHP dependencies from `composer.json`**
```sh
composer update
```

**Compile assets using `gulp`**

Gulp compiles `SASS` styles using [LibSass](https://github.com/sass/libsass) and `ES2015` scripts using [Babel](https://babeljs.io), see [gulpfile.babel.js](https://github.com/webrouse/n-asset-macro/blob/master/examples/gulp/gulpfile.babel.js) for details:

```sh
gulp
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
<link rel="stylesheet" href="/dist/css/app.c58b9c2d58.css">
...
<script src="/dist/js/app.8653984ffd.js"></script>
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
  "dist/css/app.css": "dist/css/app.c58b9c2d58.css",
  "dist/js/app.js": "dist/js/app.8653984ffd.js"
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

**Install the `gulp` command**
```sh
sudo npm install --global gulp-cli
```

**Initialize `package.json` (if not)**
```
npm init
```

**Install the JS packages**

```sh
npm install --save-dev babel-register babel-preset-env gulp gulp-babel gulp-sass gulp-rev gulp-rev-format gulp-concat gulp-sourcemaps stream-semaphore del run-sequence
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

**Create `gulpfile.babel.js`**

```js
import gulp from 'gulp';
import sass from 'gulp-sass';
import babel from 'gulp-babel';
import sourcemaps from 'gulp-sourcemaps';
import rev from 'gulp-rev';
import revFormat from 'gulp-rev-format';
import concat from 'gulp-concat';
import semaphore from 'stream-semaphore';
import del from 'del';
import runSequence from 'run-sequence';

// Config
const config = {
  publicDir: 'www',
  buildDir: 'dist',
  manifest: 'manifest.json',
  jsEntrypoint: 'app/scripts/index.js',
  sassEntrypoint: 'app/styles/main.scss',
};

// This function can be reused to save asset revisions from different (concurrent) tasks
function store(file, stream) {
  return stream
    .pipe(concat({path: `${config.buildDir}/${file}`, cwd: ''}))
    .pipe(rev())
    .pipe(revFormat({prefix: '.'}))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(config.publicDir))
    .pipe(semaphore.lockStream('manifest', file))
    .pipe(rev.manifest({
      path: `${config.publicDir}/${config.buildDir}/${config.manifest}`,
      merge: true
    }))
    .pipe(gulp.dest('.'))
    .pipe(semaphore.unlockStream('manifest', file));
}

// Sample task to process SASS
gulp.task('styles', () =>
  store('css/app.css', gulp.src(config.sassEntrypoint)
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass().on('error', sass.logError))
  ));

// Sample task to process JS
gulp.task('scripts', () =>
  store('js/app.js', gulp.src(config.jsEntrypoint)
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(babel())
  ));

// Clean task
gulp.task('clean', () => del([`${config.publicDir}/${config.buildDir}/**/*`, '!.*']));

// Default task
gulp.task('default', (cb) => {
  runSequence(
    'clean',
    ['scripts', 'styles'],
    cb,
  );
});
```

**Run PHP server**

Start the webserver and then visit http://localhost:8888 in your browser:
```sh
php -S localhost:8888 -t www
```

## Recommendations

This is just a simple example and inspiration for using gulp with asset macro. 

In practice, it is useful to add additional plugins: 
* [gulp-autoprefixer](https://www.npmjs.com/package/gulp-autoprefixer)
* [gulp-watch](https://www.npmjs.com/package/gulp-watch)
* [gulp-livereload](https://www.npmjs.com/package/gulp-livereload)
* [gulp-sass-lint](https://www.npmjs.com/package/gulp-sass-lint)
* [gulp-eslint](https://www.npmjs.com/package/gulp-eslint)
* [gulp-nittro](https://www.npmjs.com/package/gulp-nittro)
* [vinyl-ftp](https://www.npmjs.com/package/vinyl-ftp)







