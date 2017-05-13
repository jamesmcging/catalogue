<?php
namespace Catalogue\config;

/**
 * File that is to be ignored by git so that the credentials remain private
 */
class Credentials {
  const EBAY_SANDBOX_CLIENT_ID        = 'JamesMcG-MastersT-SBX-c45ed6035-11100b4a';
  const EBAY_SANDBOX_CLIENT_SECRET    = 'SBX-45ed6035660c-b34a-42d3-a945-d4d1';	
  const EBAY_SANDBOX_RU_NAME          = 'James_McGing-JamesMcG-Master-lxgbk';
  
  const EBAY_PRODUCTION_CLIENT_ID     = '';
  const EBAY_PRODUCTION_CLIENT_SECRET = '';
  const EBAY_PRODUCTION_RU_NAME       = '';
  
  const EBAY_OAUTH_AUTH_TOKEN         = 'v^1.1#i^1#f^0#p^1#I^3#r^0#t^H4sIAAAAAAAAAOVXa2wUVRTe7UsaWh8ReViI69AQoM7sndmdnd2R3WTLc7XbFrbWUqMwj7vLwOzMZO4d2sUQSxNBoxCCwR8aoVFCSGxiROKTavAHD01UjBIwPmswqDGNPFQSSLyzXcq2GqiwGhLnz+Sec+653/nOOfcBeqqq525csvH3Wu9NZX09oKfM62Unguqqyoaby8vurPSAIgNvX099T0Vv+al5SMrqlrgMIss0EPR1Z3UDiXlhlHJsQzQlpCHRkLIQiVgRU/Fkk8gxQLRsE5uKqVO+xIIoJYXCEYVVlLQSkWUIZSI1LvlsM6MUD2SJC7MhlpPDYSUSJnqEHJgwEJYMHKU4wAo0YGku0sbyIgBiMMSwHNdJ+dqhjTTTICYMoGJ5uGJ+rl2E9cpQJYSgjYkTKpaIL0q1xBMLFja3zfMX+YoVeEhhCTto9Gi+qUJfu6Q78MrLoLy1mHIUBSJE+WPDK4x2KsYvgbkG+HmqI4KcDoQIlUKQByAQKgmVi0w7K+Er43Almkqn86YiNLCGc1djlLAhr4YKLoyaiYvEAp/7W+pIupbWoB2lFjbGl8dbW6nYfe6aSWUxnZQQJmDb6FRjB60EeaiGQICnWZYFQA5KhYWGvRVoHrPSfNNQNZc05Gs2cSMkqOFoboIiX8QNMWoxWux4GruIiu2EAocgInS6SR3OooNXGW5eYZYQ4csPr56BkdkY25rsYDjiYawiTxFpK8vSVGqsMl+LhfLpRlFqFcaW6Pd3dXUxXQHGtDN+DgDW35FsSimrYFaiiK3b68P22tUn0Fo+FAWSmUgTcc4iWLpJrRIARoaKcSE+IIAC76NhxcZK/yIoitk/uiNK1SGyIEhBnmP5iCpJkJNL0SGxQpH6XRxQlnJ0VrLXQGzpkgJphdSZk4W2pooBPs0FwmlIq6FImg5G0mla5tUQzaYhBGRnlMnu939qlPGW+nxdI8o2UmolqfeS1foSkzCsjrfW/za0lGJasNXUNSX3n8Tm9vq44wvYaqtk41yjkyPjFNR18ruucJV8JleUaOMqWSL/Yc9cW+yahG+sqNlgmBcAB4Kh64uLXGhuqLgUM8u42zBjSxY2bYZAs3SIGBsi07HJBYxpcQ/lNnMNNMgWh21T16Hdzl4XC8ht5BuLh4oN+10XiPiQLG2YEsKN35RI9K5oRR60bzxGftnJMRkHIkyAqND+Fw54/+jnRsyT/9he7wDo9b5FXixAADTbAOZUlT9QUV5DIQ1DkltDlc1uRpPSDNIyBrlN25BZA3OWpNllVd7k908vf6zoodP3MJg68tSpLmcnFr17wPTLmkr2lim1rABYLsKSq3Qw1AlmXtZWsJMrJh3qGOK+dU4Nra1fKbbkcrftmSlsB7UjRl5vpaei1+vZvO7l2XWvartfe36w6rstn2bvOXDhfOrNZ6a9cfCJDyq/+XXv67OPp6o/n3PHV73ndh6y3qn74e3TteePrv+NaXo2Kk2q77/7kR2fDbw7YevqgUmLm+AZ8cO6DetD7Z4pZnhwamti5lmo7RJmTJ/ac7z/k+jOGT+dqGmcdbH31v0XdcVo5A8/tLVPefDCwRWCp+NAoqHh8B/v7xvsnPXkXvXHxXWvHDnx+PYDhxNra8p/vmvzL0cHt5x+Lzm098ixzLHM5ntPOhM3fPHSvj3LdsiT61949MVNSwduP7tx1wT6453supPTVi5srnqqsoXfPcNyLp5O1UQ+eu7rLwfoZPf955Zuy/Tj2Z7+Tbkh9szc4TT+CRg3o8yCDgAA';
  
  // Set to false if playing with the eBay sandbox
  const EBAY_PRODUCTION = false;

  // The scope of the application determines what it can do, this is hardbaked
  // into the application on the eBay developer interface
  const EBAY_SCOPE = 'https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/buy.order.readonly https://api.ebay.com/oauth/api_scope/buy.guest.order https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly';

  
  const STORE_ID = 599;
  
  // DB Credentials
  const DB_HOST = 'localhost';
  const DB_NAME = 'store_599';
  const DB_USER = 'jamesmcg';
  const DB_PASS = 'password';
}
