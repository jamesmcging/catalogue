<?php

namespace Catalogue\resources;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Description of Homepage
 *
 * @author James McGing <jamesmcging@gmail.com>
 */
class Homepage {
  
  public function getHomePage(Request $objRequest, Response $objResponse) {  

    $sHTML = <<<HTML
      <!DOCTYPE html>
      <html lang="en">
            
        <head>
          <meta charset="utf-8">
          <title>eBay Project - ebay interface</title>
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <meta name="description" content="Ebay prototype for Masters in Software Development at CIT">
          <meta name="author" content="jamesmcging@gmail.com">
        </head>
            
        <body>
          <h1>Catalogue Interface Resource</h1>
          <p>This is a service that interfaces with the local store. List of resources offered:</p>
          <ul>
            <li>GET <a href="/">/</a>This page</li>
            <li>Items</li>
            <ul>
              <li>GET <a href="/items">/items</a> - Retrieve item data from the store catalogue</li>
              <li>GET <a href="/items/1">/items/{itemID}</a> - Retrieve item data for a specific item from the store catalogue</li>
            </ul>
            <li>Catalogue</li>
            <ul>
              <li>GET <a href="/store/storedata">/store/storedata</a> - returns general data about the store</li>
              <li>GET <a href="/store/datamappings">/store/datamappings</a> - returns existing data mappings used by the app</li>
              <li>POST /store/datamappings - saves the datamappings to the DB</li>
              <li>GET <a href="/store/marketplace">/store/marketplace</a> - gets the content of the ebay marketplace record (DEV ONLY)</li>
              <li>GET <a href="/store/cataloguedata">/store/cataloguedata</a> - gets the brands, themes, departments and categories of the store catalogue</li>
            </ul>
          </ul>
        </body>
            
      </html>
HTML;
  
    $objResponse->getBody()->write($sHTML);

    return $objResponse;
  }
}
