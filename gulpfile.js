'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var cssPrefix = require('gulp-css-prefix');
var cssmin = require('gulp-cssmin');
var rename = require('gulp-rename');

gulp.task('sass', function () {
    return gulp.src('./assets/style/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cssPrefix('my-'))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('./assets/style'));
});

gulp.task('compile:watch', function () {
    gulp.watch('./assets/style/*.scss', ['sass']);
});
