module.exports = {
  compileScss: true,
  compileJs: true,
  purgeRenderCache: true,

  browserSync: {
    enable: true,
    hostname: "localhost",
    port: 8080,
    openAutomatically: true
  },

  drush: {
    css_js: 'drush cc css-js',
    render: 'drush cc render',
    cr: 'drush cr'
  }
};
