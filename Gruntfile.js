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
		}
	});

	grunt.loadNpmTasks('grunt-contrib-less');

	grunt.registerTask('default', ['less']);
};
