module.exports = function (grunt) {
    grunt.initConfig({
        vars: {
            sourceDir: 'assets-src',
            buildDir: 'build',
            distDir: 'dist',
            resourcesDir: '<%= vars.distDir %>/Resources',
            vendorDir: 'vendor',
            twigResource: 'views/Collector/icon.html.twig'
        },
        pkg: grunt.file.readJSON('package.json'),
        'imagemagick-convert': {
            dist: {
                args: [
                    '-resize', '30',
                    '-background', 'none',
                    '<%= vars.sourceDir %>/img/icon.svg',
                    '<%= vars.buildDir %>/img/icon.png'
                ],
                fatals: true
            }
        },
        base64: {
            '<%= vars.buildDir %>/img/icon.b64': '<%= vars.buildDir %>/img/icon.png'
        },
        preprocess: {
            twig: {
                src: '<%= vars.sourceDir %>/<%= vars.twigResource %>',
                dest: '<%= vars.resourcesDir %>/<%= vars.twigResource %>'
            }
        },
        sass: {
            options: {
                style: 'compressed',
                precision: 10
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: '<%= vars.sourceDir %>/sass',
                    src: ['main.sass'],
                    dest: '<%= vars.buildDir %>/public/css',
                    ext: '.css'
                }]
            }
        },
        concat: {
            options: {
                separator: ';'
            },
            js: {
                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/bootstrap-sass-official/vendor/assets/javascripts/bootstrap/tab.js',
                    'bower_components/bootstrap-sass-official/vendor/assets/javascripts/bootstrap/collapse.js',
                    'bower_components/bootstrap-sass-official/vendor/assets/javascripts/bootstrap/transition.js',
                    'bower_components/prism/components/prism-core.js',
                    'bower_components/prism/components/prism-markup.js',
                    'bower_components/prism/plugins/line-numbers/prism-line-numbers.js',
                    '<%= vars.sourceDir %>/js/*.js'
                ],
                dest: '<%= vars.buildDir %>/js/profile.js'
            }
        },
        uglify: {
            options: {
                mangle: false
            },
            js: {
                files: {
                    '<%= vars.resourcesDir %>/public/js/profile.min.js': '<%= vars.buildDir %>/js/profile.js'
                }
            }
        },
        cssmin: {
            combine: {
                files: {
                    '<%= vars.resourcesDir %>/public/css/screen.min.css': [
                        '<%= vars.buildDir %>/public/css/*.css',
                        'bower_components/prism/themes/prism-coy.css'
                    ]
                }
            }
        },
        watch: {
            icon: {
                files: ['<%= vars.sourceDir %>/img/icon.svg'],
                tasks: ['icon'],
                options: {
                    livereload: true
                }
            },
            sass: {
                files: [
                    '<%= vars.sourceDir %>/sass/*.sass',
                    '<%= vars.sourceDir %>/sass/*.scss'
                ],
                tasks: ['sass', 'cssmin'],
                options: {
                    livereload: true
                }
            },
            js: {
                files: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/bootstrap-sass-official/vendor/assets/javascripts/bootstrap/tab.js',
                    'bower_components/bootstrap-sass-official/vendor/assets/javascripts/bootstrap/collapse.js',
                    'bower_components/bootstrap-sass-official/vendor/assets/javascripts/bootstrap/transition.js',
                    'bower_components/prism/components/prism-core.js',
                    'bower_components/prism/components/prism-markup.js',
                    '<%= vars.sourceDir %>/js/*.js'
                ],
                tasks: ['concat', 'uglify'],
                options: {
                    livereload: true
                }
            }
        },
        shell: {
            options: {
                failOnError: true
            },
            mkdir: {
                command: 'mkdir -p <%= vars.buildDir %>/views/Collector <%= vars.buildDir %>/img'
            },
            clean: {
                command: 'rm -rf build'
            }
        },
        phpunit: {
            classes: {
                dir: '<%= vars.distDir %>/Tests/'
            },
            options: {
                bin: '<%= vars.vendorDir %>/bin/phpunit',
                colors: true,
                bootstrap: '<%= vars.vendorDir %>/autoload.php'
            }
        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-imagemagick');
    grunt.loadNpmTasks('grunt-base64');
    grunt.loadNpmTasks('grunt-preprocess');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.registerTask('icon', ['shell:mkdir', 'imagemagick-convert', 'base64', 'preprocess']);
    grunt.registerTask('assets', ['sass', 'cssmin', 'concat', 'uglify']);
    grunt.registerTask('default', ['icon', 'assets', 'shell:clean']);
};

