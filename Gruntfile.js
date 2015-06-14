
module.exports = function (grunt) {
    require('load-grunt-tasks')(grunt); // npm install --save-dev load-grunt-tasks
    grunt.initConfig({
        sass: {
            dist: {
                files: {
                    'app/main.css': 'app/main.scss'
                }
            }
        }
    });

    grunt.registerTask('default', ['sass']);
}