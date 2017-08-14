module.exports = (grunt) => {
  // Config
  const config = {
    publicDir: 'www',
    buildDir: 'dist',
    manifest: 'manifest.json',
    jsEntrypoint: 'app/scripts/index.js',
    sassEntrypoint: 'app/styles/main.scss',
  };

  require('load-grunt-tasks')(grunt);
  grunt.initConfig({
    // JS
    babel: {
      options: { sourceMap: true },
      dist: {
        files: {
          [`${config.publicDir}/${config.buildDir}/js/app.js`]: config.jsEntrypoint,
        }
      }
    },
    // SASS
    sass: {
      options: { sourceMap: true, sourceMapContents: true },
      dist: {
        files: {
          [`${config.publicDir}/${config.buildDir}/css/app.css`]: config.sassEntrypoint,
      }
      }
    },
    // Revision manifest
    filerev: {
      options: {
        algorithm: 'md5',
        length: 8
      },
      scripts: {
        files: [{
          src: [
            `${config.publicDir}/${config.buildDir}/**/*.js`,
          ]
        }]
      },
      styles: {
        files: [{
          src: [
            `${config.publicDir}/${config.buildDir}/**/*.css`,
          ]
        }]
      },
    },
    filerev_assets: {
      dist: {
        options: {
          dest: `${config.publicDir}/${config.buildDir}/${config.manifest}`,
          cwd: `${config.publicDir}/`,
          prettyPrint: true,
        }
      }
    },
    // Clean
    clean: {
      manifest: [`${config.publicDir}/${config.buildDir}/${config.manifest}`],
      scripts:  [`${config.publicDir}/${config.buildDir}/js`],
      styles:   [`${config.publicDir}/${config.buildDir}/css`],
    },
  });


  grunt.loadNpmTasks('grunt-filerev');
  grunt.loadNpmTasks('grunt-filerev-assets');

  grunt.registerTask('scripts', ['clean:scripts', 'babel', 'filerev:scripts', 'filerev_assets']);
  grunt.registerTask('styles',  ['clean:styles',  'sass',  'filerev:styles',  'filerev_assets']);
  grunt.registerTask('default', ['styles', 'scripts']);
};
