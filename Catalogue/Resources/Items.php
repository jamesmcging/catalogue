<?php

namespace Catalogue\Resources;

use Interop\Container\ContainerInterface as ContainerInterface;

class Items {

  protected $objContainer;
  private $_objRequest   = null;
  private $_objResponse  = null;
  
  const MAX_RETURN_ITEMS = 100; 

  // constructor receives container instance
  public function __construct(ContainerInterface $objContainer) {
      $this->objContainer = $objContainer;
  }
   
  public function getHomepage($objRequest, $objResponse, $args) {
    $objResponse->getBody()->write(__METHOD__.'() called');
    return $objResponse;
  }
    
  /**
   * Function charged with fetching item rows from the DB. These rows can be 
   * filtered using the following parameters:
   *   objFilter         - comma seperated list of product table fields;
   *   sOrderByField     - product table field to sort return items by;
   *   sOrderByDirection - expect ASC or DESC [defualt DESC];
   *   nOffset           - int to be used in limit clause [default 0];
   *   nLimit            - int to be used in limit clause [default and max 100];
   */
  public function getItems($objRequest, $objResponse, $args) {
    
    $this->_objRequest = $objRequest;
    $this->_objResponse = $objResponse;
    
    $arrResponse = array();

    $nItemID = $objRequest->getAttribute('nItemID');
    
    $sSelectStatement  = $this->getSelectStatement();
    $sFromStatement    = $this->getFromStatement();
    $sJoinStatement    = $this->getJoinStatement();
    $sWhereStatement   = $this->getWhereStatement();
    $sOrderByStatement = $this->getOrderByStatement();
    $sLimitStatement   = $this->getLimitStatement();
    
    // First calculate the number of products that an unlimited version of this
    // query would return
    $sItemCountQuery = 'SELECT count(*) as itemcount ' . $sFromStatement . $sJoinStatement . $sWhereStatement;
    $arrResponse['itemcountquery'] = $sItemCountQuery;
    $objStatement = $this->objContainer->objDB->prepare($sItemCountQuery);
    if ($objStatement->execute(array(':product_id' => $nItemID))) {
      while($arrRow = $objStatement->fetch(\PDO::FETCH_ASSOC)) {
        $arrResponse['nItemCount'] = $arrRow['itemcount'];
      }
    }
    
    // Next retrieve the actual products
    $sItemQuery = $sSelectStatement . $sFromStatement . $sJoinStatement . $sWhereStatement . $sOrderByStatement . $sLimitStatement;
    $arrResponse['itemquery'] = $sItemQuery;
    
    $objStatement = $this->objContainer->objDB->prepare($sItemQuery);
    if ($objStatement->execute(array(':product_id' => $nItemID))) {
      while($arrRow = $objStatement->fetch(\PDO::FETCH_ASSOC)) {
        // add a url link to the product page
        $arrRow['product_url'] = "/store/product/{$arrRow['product_id']}/".urlencode($arrRow['product_name'])."/";        
        $arrResponse['arrItemData'][] = $arrRow;
      }
      $objResponse = $objResponse->withStatus(200);
    } else {
      $arrResponse['message'] = 'unable to find product identified with '.$objRequest->getAttribute('nItemID');
      $objResponse = $objResponse->withStatus(400);
    }
    
    return $objResponse->withJson($arrResponse);
  }

  private function getSelectStatement() {
    $sStatement = 'SELECT ';
    
    // If we are looking for specific fields then return the fields sought
    if (!empty($this->_objRequest->getAttribute('objFields'))) {

      // objFields may contain the string all
      if (strpos($this->_objRequest->getAttribute('objFields'), 'all') !== false) {
        $sStatement .= '* ';
        
      // or it may request particular fields
      } else {
        $arrFields = explode(',', $this->_objRequest->getAttribute('objFields'));
        array_walk($arrFields, "trim");
        $arrFields = array_filter($arrFields, 'santitizeProductFields');
        $sStatement .= join(', ', $arrFields);
      }
      
    // If a specific field isn't sought then return the following fields by 
    // default. These are the fields required to render the initial item listing
    } else {
      $sStatement .= 'product_id, product_code, product_name, product_stock, product_price, product_image, product_departmentid as department_id, name as department_name, category_id, category_name ';
    }
    return $sStatement;
  }
  
  private function getFromStatement() {
    return 'FROM product ';
  }
  
  private function getJoinStatement() {
    return  'LEFT JOIN department on product_departmentid = department.id LEFT JOIN category on product.category_link = category.category_id ';
  }
  
  private function getWhereStatement() {
    $arrQueryParams = $this->_objRequest->getQueryParams();
    $arrElements = array();
    
    /* We might be looking for a specific product */
//    $nProductID = (int)$arrQueryParams['nItemID'];//$this->_objRequest->getAttribute('nItemID');
//    if (!empty($nProductID)) {
//      $arrElements[] = 'product_id = :product_id OR product_code = :product_id OR product_name = :product_id';
//    }
    
    /* We might be filtering on a department */
    $nDeptID = (int)$arrQueryParams['nDeptID'];
    if ($nDeptID && is_numeric($nDeptID)) {
      $arrElements[] = 'product_departmentid = '.$nDeptID;
    }

    /* We might be filtering on a category */
    $nCatID = $arrQueryParams['nCatID'];
    if (is_numeric($nCatID)) {
      $arrElements[] = 'product.category_link = '.$nCatID;
    }

    /* We might be filtering on a brand */
    $sBrandName = $arrQueryParams['sBrandName'];
    if ($sBrandName && (strpos($sBrandName, 'all') === false) ) {
      $arrElements[] = 'product_brandname = "'.$sBrandName.'"';
    }

    /* We might be filtering on a theme */
    $sTheme = $arrQueryParams['sTheme'];
    if ($sTheme && (strpos($sTheme, 'all') === false) ) {
      $arrElements[] = 'product_theme = "'.$sTheme.'"';
    }
    
    $sWhereStatement = '';
    if (count($arrElements) > 0) {
      $sWhereStatement .= 'WHERE '.join(' AND ', $arrElements).' ';
    }
    
    return $sWhereStatement;
  }
  
  private function getOrderByStatement() {
    $sStatement = '';
    if (!empty($_GET['sOrderByField']) && $this->santitizeProductFields($_GET['sOrderByField'])) {
      $sStatement .= 'ORDER BY '.$_GET['sOrderByField'].' ';
    
      if (!empty($_GET['sOrderByDirection']) && in_array($_GET['sOrderByDirection'], array('ASC', 'DESC'))) {
        $sStatement .= $_GET['sOrderByDirection'].' ';
      }
    } else {
      $sStatement .= 'ORDER BY product_name ';
    }
    
    return $sStatement;
  }
  
  private function getLimitStatement() {
    $sStatement = '';
    
    // No need for a limit statement when we are returning a single product
    if (empty($_GET['id'])) {
      $sStatement .= ' LIMIT';
    
      // Deal with (optional) offset first
      if (!empty($_GET['nOffset'])) {
        $sStatement .= ' '.(int)$_GET['nOffset'].', ';
      }

      // Deal with limit
      if (!empty($_GET['nLimit']) 
              && ((int)$_GET['nLimit'] <= self::MAX_RETURN_ITEMS)) {
        $sStatement .= ' '.(int)$_GET['nLimit'];

      // Default limit
      } else {
        $sStatement .= ' 20';
      }
    }
    
    return $sStatement;
  }
  
  private function santitizeProductFields($sField) {
    $arrProductFields = array(
      '*',
      'product_id',
      'product_code',
      'product_name',
      'product_price',
      'product_image',
      'category_link',
      'product_desc',
      'product_modified',
      'product_stock',
      'product_pricea',
      'product_priceb',
      'product_pricec',
      'product_promotion',
      'product_specialoffer',
      'product_newproduct',
      'product_theme',
      'product_rating',
      'product_subdescription1',
      'product_subdescription2',
      'product_subdescription3',
      'product_leadtime',
      'product_lastreceived',
      'product_releasedate',
      'product_priority',
      'product_weight',
      'product_onorder',
      'product_orderdate',
      'product_restocklevel',
      'product_taxable',
      'product_itemtaxid',
      'product_brandname',
      'product_subcategory',
      'product_priceweb',
      'product_nowebsale',
      'product_shortdesc',
      'product_matrixid',
      'product_weblinxcustomnumber1',
      'product_weblinxcustomnumber2',
      'product_weblinxcustomnumber3',
      'product_weblinxcustomnumber4',
      'product_weblinxcustomnumber5',
      'product_weblinxcustomtext1',
      'product_weblinxcustomtext2',
      'product_weblinxcustomtext3',
      'product_weblinxcustomtext4',
      'product_weblinxcustomtext5',
      'product_weblinxcustomtext6',
      'product_weblinxcustomtext7',
      'product_weblinxcustomtext8',
      'product_weblinxcustomtext9',
      'product_weblinxcustomtext10',
      'product_weblinxcustomtext11',
      'product_weblinxcustomtext12',
      'product_weblinxcustomtext13',
      'product_weblinxcustomtext14',
      'product_weblinxcustomtext15',
      'product_weblinxcustomtext16',
      'product_weblinxcustomtext17',
      'product_weblinxcustomtext18',
      'product_weblinxcustomtext19',
      'product_weblinxcustomtext20',
      'product_notdiscountable',
      'product_qtydiscountid',
      'product_departmentid',
      'product_keywords',
      'checksum',
      'product_tagalong',
      'product_timestamp',
      'product_type'
    );
    return in_array($sField, $arrProductFields);
  }
}
