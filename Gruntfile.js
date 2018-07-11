module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		less: {
			options: {
				compress: true,
				sourceMap: false,
			},
			main: {
				files: {
					"www/css/style.css": "www/less/style.less",
					"www/css/print.css": "www/less/print.less"
				}
			}
		},
        uglify: {
            options: {
                sourceMap: false,
                beautify: false
            },
            default: {
                files: {
                    'www/js/main.min.js': [
                        "www/js/libs/jquery-2.1.3.js",
						"www/js/libs/jquery.scrollTo-1.4.14.js",
						"www/js/libs/jquery.smooth-scroll-1.5.5.js",
						"www/js/libs/jquery.tooltipster-3.3.0.js",
						"www/js/libs/jquery.lazyload-rev-d14e809.js",
						"www/js/libs/lightbox-2.7.1.js",
						"www/js/libs/netteForms.js",
                        "www/js/main.js"
                    ],
                    'www/js/admin.min.js': [
                        "www/js/libs/netteForms.js",
                        "www/js/admin.js"
                    ]
                }
            }
        }

    });

	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');

	grunt.registerTask('default', ['less','uglify']);
};
