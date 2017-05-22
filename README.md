# ebay-service-catalogue

## Masters in Software Development @ CIT 2017
This service forms part of a collection of micro-services that make up the Alaname application. This web application exists to push items from a web DB to eBay and pull orders arising from these back to the web DB. When deployed as part of an omnichannel ecommerce platform this application offers eBay as a channel.

### Service Overview
This service is charged with managing the communication between the app and the local store. It is an API offering access to items on the local store. The app is envisaged to form a channel in an omnichannel retail solution. In this scenario the authentication of the user to the app is managed by the solution - for the purposes of this exercise this aspect is simplified and the user is assumed to have permission to access the local store.

### PHP
To make for fast prototyping in PHP, it makes sense to use:
 - a dependency manager called composer (https://getcomposer.org/);
 - PHP standards (http://www.php-fig.org/psr/);
 - a framework, in this case a lightweight REST interface called Slim (https://www.slimframework.com/);
 - namespacing;
 - autoloaders;
 
When deployed the only visible directory is the public/ directory with its apache htaccess file and index.php using a front controller pattern to funnel all traffic through the same controller. The controller then usually has route files that handle the various aspects of the app. in this case the number of calls is limited so I have the controller call resource classes directly.

In terms of the REST maturity model, this app: 
 - uses HTTP verbs correctly;
 - offers resources as end points;
 - allows manipulation of calls using paramters;
 
 What the app does not do is structure the return data according to a third party convention such as HAL.
