<?php

namespace Catalogue\resources;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface as ContainerInterface;
use Ebay\config\Credentials as Credentials;

/**
 * Description of Store
 *
 * @author James McGing <jamesmcging@gmail.com>
 */
class Store {
  protected $_objContainer = null;


  // constructor receives container instance
  public function __construct(ContainerInterface $objContainer) {
    $this->_objContainer = $objContainer;
  }
  
  public function getStoreData(Request $objRequest, Response $objResponse) {

    $arrData = array();

    try {
      $sQuery = "SELECT count(*) as skucount FROM product";
      $objStatement = $this->_objContainer->objDB->prepare($sQuery);

      if ($objStatement->execute()) {
        $sStatement = $this->_objContainer->objDB->query($sQuery);
        $arrCount = $sStatement->fetch(\PDO::FETCH_ASSOC);
        $arrData['nStoreSkuCount'] = $arrCount['skucount'];
      } else {
        throw new \Exception($sQuery.' failed in execution');
      }

      $sQuery = "SELECT * FROM config";
      $objStatement = $this->_objContainer->objDB->prepare($sQuery);

      if ($objStatement->execute()) {
        $sStatement = $this->_objContainer->objDB->query($sQuery);
        $arrResponse = $sStatement->fetch(\PDO::FETCH_ASSOC);

        $arrData['objStoreData'] = array();
        $arrData['objStoreData']['location'] = array();
        $arrData['objStoreData']['location']['address'] = array();
        $arrData['objStoreData']['location']['address']['addressLine1']     = $arrResponse['config_storeaddress1'];
        $arrData['objStoreData']['location']['address']['addressLine2']     = $arrResponse['config_storeaddress2'];
        $arrData['objStoreData']['location']['address']['city']             = $arrResponse['config_storecity'];
        $arrData['objStoreData']['location']['address']['country']          = $arrResponse['config_storecountry'];
        $arrData['objStoreData']['location']['address']['county']           = $arrResponse['config_storestate'];
        $arrData['objStoreData']['location']['address']['postalCode']       = $arrResponse['config_storezip'];      
        $arrData['objStoreData']['location']['address']['stateOrProvince']  = $arrResponse['config_storestate'];
        $arrData['objStoreData']['location']['geoCoordinates']['latitude']  = '';
        $arrData['objStoreData']['location']['geoCoordinates']['longitude'] = '';      
        $arrData['objStoreData']['name']                          = $arrResponse['config_storename'];
        $arrData['objStoreData']['phone']                         = $arrResponse['config_storephone'];
        $arrData['objStoreData']['locationWebUrl']                = '';
        $arrData['objStoreData']['locationInstructions']          = '';
        $arrData['objStoreData']['locationAdditionalInformation'] = '';
        $arrData['objStoreData']['locationTypes']                 = array('STORE');
        $arrData['objStoreData']['merchantLocationKey']           = '';
        $arrData['objStoreData']['merchantLocationStatus']        = 'ENABLED';

      } else {
        throw new \Exception($sQuery.' failed in execution');
      }    

    } catch (Exception $objException) {
      $objResponse->getBody()->write(json_encode($objException));
    }

    return $objResponse->withJson($arrData, 200);
  }
  
  public function getDataMappings(Request $objRequest, Response $objResponse) {
    $nResponseCode   = 400;
    $arrResponseData = array('saved_data_mappings' => null);
    
    // We save the data mappings into marketplace_categorization in json
    $sQuery = "SELECT marketplace_categorization "
            . "FROM marketplace "
            . "WHERE marketplace_type = 'ebay'";
    
    $arrData = $this->_objContainer->objDB->query($sQuery)->fetchAll();
    
    if ($arrData) {
      // The user token is a json string in marketplace_data
      $arrResponseData['saved_data_mappings'] = json_decode($arrData[0]['marketplace_categorization'], true);
      $nResponseCode = 200;
    }
    
    return $objResponse->withJson($arrResponseData, $nResponseCode);
  }
  
  public function setDataMappings(Request $objRequest, Response $objResponse) {  
    // We save the data mappings into marketplace_categorization in json
    $sQuery = "
      INSERT INTO marketplace(
        marketplace_type,
        marketplace_webstoreid,
        marketplace_data,
        marketplace_enabled,
        marketplace_nextrefresh, 
        marketplace_lastmessage, 
        marketplace_categorization)
      VALUES(
        'ebay', 
        '".Credentials::STORE_ID."',
        '',
        0,
        0,
        'Ebay field mappings saved',
        '".json_encode($arrRequestData['datamappings'])."'
      )
      ON DUPLICATE KEY UPDATE 
        marketplace_type           = 'ebay', 
        marketplace_webstoreid     = '".Credentials::STORE_ID."', 
        marketplace_lastmessage    = 'field mappings saved @".time()."',
        marketplace_categorization = '".json_encode($arrRequestData['datamappings'])."'
      ";
                   
    $objStatement = $this->_objContainer->objDB->prepare($sQuery);
    if($objStatement->execute()) {
      $arrResponseData['saved_data_mappings'] = $arrRequestData;
      $arrResponseData['query'] = $sQuery;
      $nResponseCode = 200;
    } else {
      $nResponseCode = 500;
    }       

    return $objResponse->withJson($arrResponseData, $nResponseCode);
  }
  
  public function getMarketplaceData(Request $objRequest, Response $objResponse) { 
    $sQuery = "SELECT * FROM marketplace WHERE marketplace_type='ebay'";
    try {
      $objStatement = $this->_objContainer->objDB->prepare($sQuery);

      if ($objStatement->execute()) {
        $arrData['data'] = $objStatement->fetch(\PDO::FETCH_ASSOC);
        $nResponseCode = 200;
      } else {
        throw new \Exception($sQuery.' failed in execution');
      }
    } catch (Exception $objException) {
      $arrData['message'] .= 'Exception while fetching marketplace data. Exception message: '.$objException->getMessage();
      $nResponseCode = 400;
    }
    $arrData['query'] = $sQuery; 

    return $objResponse->withJson($arrData, $nResponseCode);
  }
  
  public function getCatalogueData(Request $objRequest, Response $objResponse) {
    $sQuery = 'SELECT product_brandname, product_theme, product_departmentid, name, category_id, category_name '
            . 'FROM product '
            . 'LEFT JOIN department ON product_departmentid = id '
            . 'LEFT JOIN category ON product.category_link = category.category_id';

    try {
      $arrData = array(
        'objBrands'      => array(),
        'objThemes'      => array(),
        'objDepartments' => array(),
        'objCategories'  => array()
      );
      $objStatement = $this->_objContainer->objDB->prepare($sQuery);

      if ($objStatement->execute()) {
        while($arrRow = $objStatement->fetch(\PDO::FETCH_ASSOC)) {
          // Add any brand names
          if (!in_array($arrRow['product_brandname'], $arrData['objBrands'])
                  && strlen($arrRow['product_brandname']) > 0) {
            $arrData['objBrands'][] = $arrRow['product_brandname'];
          }
          // Add any themes
          if (!in_array($arrRow['product_theme'], $arrData['objThemes'])
                  && strlen($arrRow['product_theme']) > 0) {
            $arrData['objThemes'][] = $arrRow['product_theme'];
          } 
          // Add any departments
          if (!isset($arrData['objDepartments'][$arrRow['product_departmentid']])
                  && strlen($arrRow['product_departmentid']) > 0) {
            $arrData['objDepartments'][$arrRow['product_departmentid']] = $arrRow['name'];
          }         
          // Add any catagories
          if (!isset($arrData['objDepartments'][$arrRow['category_id']])
                  && strlen($arrRow['category_id']) > 0) {
            $arrData['objCategories'][$arrRow['category_id']] = $arrRow['category_name'];
          }
          // Build a tree of departments/ categories
          if (!isset($arrData['objStoreStructure'][$arrRow['product_departmentid']])
                  && strlen($arrRow['product_departmentid']) > 0) {
            $arrData['objStoreStructure'][$arrRow['product_departmentid']] = array(
              'department_id'   => $arrRow['product_departmentid'],
              'department_name' => $arrRow['name'],
              'department_link' => "/store/department/{$arrRow['product_departmentid']}/{$arrRow['name']}/",
              'children'        => array(),
            );
          }
          if (!isset($arrData['objStoreStructure'][$arrRow['product_departmentid']]['children'][$arrRow['category_id']])
                  && strlen($arrRow['category_id'])) {
            $arrData['objStoreStructure'][$arrRow['product_departmentid']]['children'][$arrRow['category_id']] = array(
              'category_id'   => $arrRow['category_id'],
              'category_name' => $arrRow['category_name'],
              'category_link' => "/store/category/{$arrRow['product_departmentid']}/{$arrRow['category_id']}/{$arrRow['category_name']}/"
            );
          }
        }

        // Sort the brand and theme arrays alphabetically
        sort($arrData['objBrands']);
        sort($arrData['objThemes']);
  //      asort($arrData['objDepartments']);
  //      ksort($arrData['objCategories']);

        $nResponseCode = 200;
      } else {
        throw new \Exception($sQuery.' failed in execution');
      }

    } catch (Exception $objException) {
      $arrData['message'] .= 'Exception while fetching catalogue data. Exception message: '.$objException->getMessage();
      $nResponseCode = 400;
    }
    $arrData['query'] = $sQuery; 

    return $objResponse->withJson($arrData, $nResponseCode);
  }
  
}
