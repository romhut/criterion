(function() {

  // Require.js allows us to configure shortcut alias
  require.config({
    baseUrl: "/js/",
    paths: {
      'jquery': 'libs/jquery.min',
      'jquery.timeago': 'libs/jquery.timeago'
    },
    shim: {
      //jquery plugins
      'bootstrap': ['jquery'],
      'jquery.timeago': {
        deps: ['jquery'],
        exports: 'jQuery.fn.timeago'
      }
    }

  });

  // Load jQuery plugins
  require(
    [
      'libs/jquery.min',
      'libs/jquery.timeago'
    ],
    function($) 
    {
      //boot the application
      require(['libs/Criterion'], function(app) 
      {
        Criterion.init();
      });
    }
  );

}).call(this);