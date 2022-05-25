<?php

define('AppStartTime', microtime(true));
define('DS', '/');
define('Root', str_replace('\\', '/', dirname(__FILE__) . DS) . '..' . DS);
define('System', Root . 'System' . DS);
define('Globals', System . 'Globals' . DS);
define('Library', System . 'Library' . DS);
define('Preload', System . 'Preload' . DS);
define('Includes', System . 'Includes' . DS);
define('App', Root . 'App' . DS);
set_include_path($IncludePath = (Includes . PATH_SEPARATOR . get_include_path())); //  Why ??????????


/* *
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  require_once realpath(__DIR__ . '/vendor/autoload.php');
  /* */

foreach (glob(Globals . '*.php') as $F)
    require ($F);
foreach (glob(Preload . '*.php') as $F)
    require ($F);

foreach (glob(System . 'App' . DS . '*.php') as $F)
    require_once ($F);
spl_autoload_register(function ($ClassName) {
    if (!empty($ClassName)) {
        if (file_exists(Library . $ClassName . '.php'))
            require (Library . $ClassName . '.php');

        if (file_exists(App . 'Models' . DS . $ClassName . '.php'))
            require (App . 'Models' . DS . $ClassName . '.php');
    }
});

/* *
  foreach (glob(Library . '*.php') as $F)
  require ($F);

  foreach (glob(System . 'App' . DS . '*.php') as $F)
  require_once ($F);
  foreach (glob(App . 'Models' . DS . '*.php') as $F)
  require_once ($F);
  /* */
echo '<pre>';
(new DMVC())->Run();
/* /

  if ($url == '/') {

  // This is the home page
  // Initiate the home controller
  // and render the home view

  require_once App . 'Models/index_model.php';
  require_once App . 'Controllers/index.php';
  require_once App . 'Views/index_view.php';

  $indexModel = New IndexModel();
  $indexController = New IndexController($indexModel);
  $indexView = New IndexView($indexController, $indexModel);

  print $indexView->index();
  } else {

  // This is not home page
  // Initiate the appropriate controller
  // and render the required view
  //The first element should be a controller
  $requestedController = $url[0];

  // If a second part is added in the URI,
  // it should be a method
  $requestedAction = isset($url[1]) ? $url[1] : '';

  // The remain parts are considered as
  // arguments of the method
  $requestedParams = array_slice($url, 2);

  // Check if controller exists. NB:
  // You have to do that for the model and the view too
  $ctrlPath = App . 'Controllers/' . $requestedController . '.php';

  if (file_exists($ctrlPath)) {
  new DMVC();
  /* require_once App . 'Models/' . $requestedController . '_model.php';
  require_once App . 'Controllers/' . $requestedController . '.php';
  require_once App . 'Views/' . $requestedController . '_view.php';

  $modelName = ucfirst($requestedController) . 'Model';
  $controllerName = ucfirst($requestedController) . 'Controller';
  $viewName = ucfirst($requestedController) . 'View';

  $controllerObj = new $controllerName(new $modelName);
  $viewObj = new $viewName($controllerObj, new $modelName);

  // If there is a method - Second parameter
  if ($requestedAction != '') {
  // then we call the method via the view
  // dynamic call of the view
  print $controllerObj->$requestedAction($requestedParams);
  } */
/*
  } else {
  

header('HTTP/1.1 404 Not Found');
die('404 - The file - ' . $ctrlPath . ' - not found');
//require the 404 controller and initiate it
//Display its view
}
}
/**/