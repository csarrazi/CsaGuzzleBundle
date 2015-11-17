var gulp = require('gulp')
  , uglify = require('gulp-uglify')
  , uglifycss = require('gulp-uglifycss')
  , sass = require('gulp-sass')
  , concat = require('gulp-concat')
;

gulp.task('default', ['js', 'css']);

gulp.task('js', ['js-guzzle', 'js-legacy']);

gulp.task('css', ['css-screen', 'css-legacy']);

gulp.task('watch', function () {
  gulp.watch('assets-src/js/modules/*.js', ['js']);
  gulp.watch('assets-src/js/legacy.js', ['js']);
  gulp.watch('assets-src/sass/**/*.sass', ['css']);
  gulp.watch('assets-src/sass/*.sass', ['css']);
});

gulp.task('js-legacy', function () {
  gulp.src([
    'assets-src/js/legacy.js'
  ])
    .pipe(concat('legacy.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('src/Resources/public/js'))
});

gulp.task('js-guzzle', function () {
  gulp.src([
    'bower_components/prism/components/prism-core.js',
    'bower_components/prism/components/prism-markup.js',
    'bower_components/prism/plugins/line-numbers/prism-line-numbers.js',
    'assets-src/js/modules/*.js'
  ])
    .pipe(concat('guzzle.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('src/Resources/public/js'))
});

gulp.task('css-legacy', function () {
  gulp.src([
    'assets-src/sass/legacy.sass'
  ])
    .pipe(sass())
    .pipe(concat('legacy.min.css'))
    .pipe(uglifycss())
    .pipe(gulp.dest('src/Resources/public/css'))
});

gulp.task('css-screen', function () {
  gulp.src([
    'assets-src/sass/main.sass',
    'bower_components/prism/themes/prism-okaidia.css'
  ])
    .pipe(sass())
    .pipe(concat('screen.min.css'))
    .pipe(uglifycss())
    .pipe(gulp.dest('src/Resources/public/css'))
});
