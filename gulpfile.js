var gulp = require('gulp')
  , uglify = require('gulp-uglify')
  , uglifycss = require('gulp-uglifycss')
  , sass = require('gulp-sass')
  , concat = require('gulp-concat')
;

gulp.task('default', ['js', 'css']);

gulp.task('js', function () {
  gulp.src([
    'bower_components/jquery/dist/jquery.js',
    'bower_components/bootstrap-sass-official/assets/javascripts/bootstrap/tab.js',
    'bower_components/bootstrap-sass-official/assets/javascripts/bootstrap/collapse.js',
    'bower_components/bootstrap-sass-official/assets/javascripts/bootstrap/transition.js',
    'bower_components/prism/components/prism-core.js',
    'bower_components/prism/components/prism-markup.js',
    'bower_components/prism/plugins/line-numbers/prism-line-numbers.js',
    'assets-src/js/*.js'
  ])
    .pipe(concat('profile.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('src/Resources/public/js'))
});

gulp.task('css', function () {
  gulp.src([
    'assets-src/sass/main.sass'
  ])
    .pipe(sass())
    .pipe(concat('screen.min.css'))
    .pipe(uglifycss())
    .pipe(gulp.dest('src/Resources/public/css'))
});
