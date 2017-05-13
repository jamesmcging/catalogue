<?php

error_reporting(E_ALL); ini_set('display_errors', 1);

require '../vendor/autoload.php';

$app = new \Slim\App(["settings" => array('displayErrorDetails' => true, 'addContentLengthHeader' => false)]);

$container = $app->getContainer();

$container['objDB'] = function () {
  return Catalogue\classes\DB::getInstance();
};

// The following enables CORS
$app->options('/{routes:.+}', function($request, $response, $args) {
  return $response;
});
// The following enables CORS
$app->add(function($request, $response, $next) {
  $response = $next($request, $response);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET');
});

$app->get('/', Catalogue\resources\Homepage::class . ':getHomepage');


$app->get('/items', Catalogue\resources\Items::class . ':getItems');

$app->get('/items/{nItemID}', Catalogue\Resources\Items::class . ':getItems');


$app->get('/store/storedata', Catalogue\Resources\Store::class . ':getStoreData');

$app->get('/store/datamappings', Catalogue\Resources\Store::class . ':getDataMappings');

$app->post('/store/datamappings', Catalogue\Resources\Store::class . ':setDataMappings');

$app->get('/store/marketplace', Catalogue\Resources\Store::class . ':getMarketplaceData');

$app->get('/store/cataloguedata', Catalogue\Resources\Store::class . ':getCatalogueData');


$app->run();
