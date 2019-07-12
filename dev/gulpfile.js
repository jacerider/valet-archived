/**
 * @file
 * Gulp task definition.
 */

"use strict";

var gulp = require('gulp');
var gutil = require('gulp-util');
var browserSync = require('browser-sync').create();
var fs = require('fs');
var extend = require('extend');
var config = require('./config/config.json');

// Include plugins.
var sass = require('gulp-sass');
var sassLint = require('gulp-sass-lint');
var plumber = require('gulp-plumber');
var notify = require('gulp-notify');
var autoprefix = require('gulp-autoprefixer');
var glob = require('gulp-sass-glob');
var sourcemaps = require('gulp-sourcemaps');
var babel = require('gulp-babel');
var uglify = require('gulp-uglify');
var eslint = require('gulp-eslint');

// If config.js exists, load that config for overriding certain values below.
function loadConfig() {
  if (fs.existsSync('./config/config.local.json')) {
    config = extend(true, config, require('./config/config.local'));
  }
  return config;
}
loadConfig();

// CSS.
gulp.task('css', function () {
  return gulp.src(config.css.src)
    .pipe(glob())
    .pipe(plumber({
      errorHandler: function (error) {
        notify.onError({
          title: "Gulp",
          subtitle: "Failure!",
          message: "Error: <%= error.message %>",
          sound: "Beep"
        })(error);
        this.emit('end');
      }
    }))
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'compressed',
      errLogToConsole: true,
      includePaths: config.css.includePaths
    }))
    .pipe(autoprefix('last 2 versions', '> 1%', 'ie 9', 'ie 10'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(config.css.dest))
    .on('finish', function () {
      gulp.src(config.css.src)
        .pipe(sassLint({
            configFile: 'config/.sass-lint.yml'
          }))
        .pipe(sassLint.format());
    })
    .pipe(config.browserSync.enabled ? browserSync.reload({
      stream: true,
      // once: true,
      match: '**/*.css'
    }) : gutil.noop());
});

// Javascript.
gulp.task('js', function () {
  return gulp.src(config.js.src)
    .pipe(plumber())
    .pipe(eslint({
      configFile: 'config/.eslintrc',
      useEslintrc: false
    }))
    .pipe(babel({
      presets: ['es2015']
    }))
    .pipe(eslint.format())
    .pipe(uglify())
    .pipe(gulp.dest(config.js.dest))
    .pipe(config.browserSync.enabled ? browserSync.reload({
      stream: true,
      // once: true,
      match: '**/*.js'
    }) : gutil.noop());
});

// Watch task.
gulp.task('watch', function () {
  gulp.watch(config.css.src, ['css']);
  gulp.watch(config.js.src, ['js']);
});

// Static Server + Watch.
gulp.task('serve', ['css', 'js', 'watch'], function () {
  if (config.browserSync.enabled) {
    browserSync.init({
      proxy: config.browserSync.proxy,
      port: config.browserSync.port,
      open: config.browserSync.openAutomatically,
      notify: config.browserSync.notify,
    });
  }
});

// Default Task.
gulp.task('default', ['serve']);
