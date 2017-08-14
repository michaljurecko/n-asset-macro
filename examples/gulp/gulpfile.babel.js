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
