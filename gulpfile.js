////////////////////////////////////////////////////////////////////////////////
// Tasks
////////////////////////////////////////////////////////////////////////////////

// Gulp
var gulp = require('gulp');
var gutil = require('gulp-util');
var notify = require('gulp-notify');
var rename = require('gulp-rename');
var shell = require('gulp-shell');
var plumber = require('gulp-plumber');
var browserSync = require('browser-sync').create();
var fs = require('fs');
var config = {
  compileScss: true,
  compileJs: true,
  compressImages: true,
  purgeRenderCache: false,
  browserSync: {
    enable: false,
    hostname: null,
    port: 8080,
    openAutomatically: false
  },
  drush: {
    css_js: 'drush cc css-js',
    render: 'drush cc render',
    cr: 'drush cr'
  }
};

// SCSS/CSS
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var lint = require('gulp-scss-lint');
var prefix = require('gulp-autoprefixer');
var clean = require('gulp-clean-css');

// JavaScript
var eslint = require('gulp-eslint');
var uglify = require('gulp-uglify');

// Images
var svgmin = require('gulp-svgmin');
var imagemin = require('gulp-imagemin');

// Other
var size = require('gulp-size');

// If config.js exists, load that config for overriding certain values below.
function loadConfig() {
  if (fs.existsSync(__dirname + "/./config.js")) {
    config = require("./config");
  }

  return config;
}
loadConfig();

////////////////////////////////////////////////////////////////////////////////
// Compile SCSS
////////////////////////////////////////////////////////////////////////////////

gulp.task('sassLint', function (){
  gulp.src(['./dev/scss/*.scss', './dev/scss/**/*.scss'])
    .pipe(plumber())
    .pipe(lint());
});

gulp.task('sass', function (){
  var s = size({showTotal: false});

  gulp.src(['./dev/scss/*.scss'])
    .pipe(plumber())
    .pipe(s)
    .pipe(sourcemaps.init())
    .pipe(sass({
      noCache: true,
      outputStyle: 'compressed',
      lineNumbers: false,
      includePaths: ['./dev/scss']
    })).on('error', function(error) {
      gutil.log(error);
      this.emit('end');
    })
    .pipe(prefix(
      "last 2 versions", "> 1%", "ie 8", "ie 7"
      ))
    .pipe(gulp.dest('./css'))
    .pipe(clean())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'))
    .pipe(config.browserSync.enable ? browserSync.stream({match: '**/*.css'}) : gutil.noop())
    .pipe(notify({
      title: "SASS Compiled",
      message: () => `All SASS files have been recompiled to CSS. Total size ${s.prettySize}`,
      onLast: true
    }));
});

////////////////////////////////////////////////////////////////////////////////
// Compile Javascript
////////////////////////////////////////////////////////////////////////////////

gulp.task('jsLint', function (){
  return gulp.src(['./dev/js/*.js', './dev/js/**/*.js'])
    .pipe(eslint())
    .pipe(eslint.format());
});

gulp.task('js', function (){
  gulp.src(['./dev/js/*.js', './dev/js/**/*.js'])
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./js'))
    .pipe(browserSync.stream())
    .pipe(notify({
      title: "JS Minified",
      message: "All JS files have been minified.",
      onLast: true
    }));
});

gulp.task('js-watch', ['js'], browserSync.reload);

////////////////////////////////////////////////////////////////////////////////
// Compress Images
////////////////////////////////////////////////////////////////////////////////

gulp.task('svgmin', function() {
  gulp.src(['./dev/img/*.svg', './dev/img/**/*.svg'])
    .pipe(plumber())
    .pipe(svgmin())
    .pipe(gulp.dest('./images'));
});

gulp.task('imgmin', function () {
  gulp.src(['./dev/img/*', './dev/img/**/*'])
    .pipe(plumber())
    .pipe(imagemin())
    .pipe(browserSync.stream())
    .pipe(gulp.dest('./images'));
});

////////////////////////////////////////////////////////////////////////////////
// Browser Sync
////////////////////////////////////////////////////////////////////////////////

gulp.task('browser-sync', function() {
  browserSync.init({
    port: config.browserSync.port,
    proxy: config.browserSync.hostname,
    open: config.browserSync.openAutomatically
  });
});

////////////////////////////////////////////////////////////////////////////////
// Drush
////////////////////////////////////////////////////////////////////////////////

gulp.task('drush:css_js', function () {
  gulp.src('', {read: false})
    .pipe(plumber())
    .pipe(shell([
      config.drush.css_js
    ]))
    .pipe(browserSync.stream())
    .pipe(notify({
      title: "Cache cleared",
      message: "Drupal CSS/JS cache cleared.",
      onLast: true
    }));
});

gulp.task('drush:render', function () {
  gulp.src('', {read: false})
    .pipe(plumber())
    .pipe(shell([
      config.drush.render
    ]))
    .pipe(browserSync.stream())
    .pipe(notify({
      title: "Cache cleared",
      message: "Drupal render cache cleared.",
      onLast: true
    }));
});

gulp.task('drush:cr', function () {
  gulp.src('', {read: false})
    .pipe(plumber())
    .pipe(shell([
      config.drush.cr
    ]))
    .pipe(browserSync.stream())
    .pipe(notify({
      title: "Cache rebuilt",
      message: "Drupal cache rebuilt.",
      onLast: true
    }));
});

////////////////////////////////////////////////////////////////////////////////
// Default Task
////////////////////////////////////////////////////////////////////////////////

gulp.task('default', function(){

  if (config !== null) {
    if (config.browserSync.enable) {
      gulp.start(['browser-sync']);
    }

    if (config.compileScss) {
      gulp.watch(['./dev/scss/*.scss', './dev/scss/**/*.scss'], ['sassLint','sass']);
    }

    if (config.compileJs) {
      gulp.watch(['./dev/js/*.js', './dev/js/**/*.js'], ['jsLint', 'js']);
    }

    if (config.compressImages) {
      gulp.watch(['./dev/img/*', './dev/img/**/*'], ['svgmin','imgmin']);
    }

    if (config.purgeRenderCache) {
      gulp.watch(['./templates/**/*.html.twig'], ['drush:render']);
    }
  }
});
