// gulp style
'use strict';
 
const gulp = require('gulp');

var sass = require("gulp-sass"),
    postcss = require("gulp-postcss"),
    autoprefixer = require("autoprefixer"),
    cssnano = require("cssnano");

var paths = {
  styles: {
    src: "public/scss/*.scss",
    dest: "public/css/"
  }
};

function style() {
  return gulp
  .src(paths.styles.src)
  .pipe(sass())
  .on("error", sass.logError)
  .pipe(postcss([autoprefixer(), cssnano()]))
  .pipe(gulp.dest(paths.styles.dest));
}

exports.style = style;

var build = gulp.parallel(style);

gulp.task('build', build);

gulp.task('default', build);