module.exports = function (grunt) {
  grunt.initConfig({
	  flatdoc: {    
	    dist: {
	    	options: {
	    		folder: "docs/markdown"
	    	}
	    },
	  },
	});

  grunt.registerTask('dist', ['flatdoc']);


  grunt.registerTask('default', ['dist']);
  grunt.loadNpmTasks('grunt-flatdoc');


};


