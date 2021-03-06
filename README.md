   Copyright (c) 2012 Zuora, Inc.
   
   Permission is hereby granted, free of charge, to any person obtaining a copy of 
   this software and associated documentation files (the "Software"), to use copy, 
   modify, merge, publish the Software and to distribute, and sublicense copies of 
   the Software, provided no fee is charged for the Software.  In addition the
   rights specified above are conditioned upon the following:
   
   The above copyright notice and this permission notice shall be included in all
   copies or substantial portions of the Software.
   
   Zuora, Inc. or any other trademarks of Zuora, Inc.  may not be used to endorse
   or promote products derived from this Software without specific prior written
   permission from Zuora, Inc.
   
   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
   ZUORA, INC. BE LIABLE FOR ANY DIRECT, INDIRECT OR CONSEQUENTIAL DAMAGES
   (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
   LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
   ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
   (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
   SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


# Zuora Storefront Library

   Copyright (c) 2012 Zuora, Inc.
   
   Permission is hereby granted, free of charge, to any person obtaining a copy of 
   this software and associated documentation files (the "Software"), to use copy, 
   modify, merge, publish the Software and to distribute, and sublicense copies of 
   the Software, provided no fee is charged for the Software.  In addition the
   rights specified above are conditioned upon the following:
   
   The above copyright notice and this permission notice shall be included in all
   copies or substantial portions of the Software.
   
   Zuora, Inc. or any other trademarks of Zuora, Inc.  may not be used to endorse
   or promote products derived from this Software without specific prior written
   permission from Zuora, Inc.
   
   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
   ZUORA, INC. BE LIABLE FOR ANY DIRECT, INDIRECT OR CONSEQUENTIAL DAMAGES
   (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
   LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
   ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
   (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
   SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

## Installation & Configuration

1.	Deploy the contents of the “store” folder on either a local Apache server such as MAMP, XAMP or WAMP, or a hosted server, running PHP version 5.3 or higher.
2.	Set up a Hosted Payments Page to collect credit card information.
  1. In your Zuora tenant, navigate to Settings > Z-Payments Settings >  Set Up Hosted Pages. 
  2. Click create new hosted page.
  3. Provide a name for your page.
  4. Set the Hosted Domain field to the root directory of the location at which your store is hosted. This should begin with “http://” or “https://”. For example, if your store is hosted at “http://zillacloudcompany.com/store”, your Hosted Domain would be “http://zillacloudcompany.com”
  5. Set the Hosted Domain field to the location of the included “HPMCallback.php” file. For example, if the callback page is hosted at “http://zillacloudcompany.com/store/HPMCallback.php”, your Hosted Domain would be “/store/HPMCallback.php”
  6. On the page component, check the Enabled button for all fields except Email Address.
  7. To match the style of the provided subscribe form, copy the CSS from HPMStyle.css into the CSS window on the HPM configuration page.
3.	Navigate to store/backend/config.php, and use a text editor to modify the file with the following settings:
  1. Set username, password, and endpoint for Zuora tenant. Make sure this is account is a specially designated API-only account whose password will not change, as this will disable the integration.
  2. Set Page ID, Tenant ID, API Security Key and App URL for Hosted Payments Page configured in step 2. The API Security Key and Page ID can be access from the Hosted Payments Page List, and the Tenant ID can be access from Administration Settings. If you are using a production Zuora instance, the app URL will be https://www.zuora.com For a sandbox tenant, it will be https://apisandbox.zuora.com
  3. If integrating with Salesforce.com, set the makeSfdcAccount flag to true, and enter Salesforce Username, Password and Security token. Make sure this is account is a specially designated API account whose password and security token will not change.
  4. If displaying all products in the tenant, set showAllProducts flag to true. Otherwise, set it to false, and configure which field and values products should be grouped by.
4.	Whenever changes are made to the Product Catalog, you can refresh the catalog cache from your Zuora tenant, by navigating to /store/admin.html and visiting the Refresh Catalog link. This will send a request to update product and rate plan information, so that the next user to view the store will see an updated list of products. Before running this refresh, ensure that the zApi tests on the admin.html page pass.
  1. Note: By default, a list of charges and pricing is not displayed to the customer on the product select page. If pricing information is to be shown on the product select page, prices can be added to the description within the Zuora catalog in your tenant. Product descriptions and rate plan descriptions will each be displayed on the product select page, and the account view page. HTML is supported in these description fields as well, including bold and italics, lists, line breaks, images, etc.

## Testing

1.	Visit <your hosted directory>/store/admin.html in your browser
2.	In the testing panel, check the box for zApi and click Test to validate that a connection can be made to your Zuora tenant.
3.	If the tests do not complete, ensure the PHP setting for Display Errors is enabled on your server to assist with troubleshooting. Some potential environment errors include:
  1. “Configuration file could not be read.” Ensure the config.php file is located at /backend/config.php
  2. “User name and password do not match.” Ensure a Zuora username and password are correct, and your endpoint points to the correct URL (https://apisandbox.zuora.com/apps/services/a/38.0 for sandbox or https://www.zuora.com/apps/services/a/38.0 for production)
  3. “Fatal error: Class 'SoapClient' not found.” Enable the PHP Extension, php_soap. 
  4. “SSL support is not available in this build.” Enable the PHP Extension, php_openssl.
  5. “Parse error: syntax error, unexpected $end.” Enable the PHP Setting, Short Open Tag.
  6. When clicking the Subscribe button on subscribe.html, nothing happens. This is often due the callback page in the Hosted Page configuration being set incorrectly. See Step 2 in Installation and Configuration for details on setting up a hosted page.


### User Perspective Use Cases

The four primary functions of a B2C web portal are Product Selector, Subscribe, Account Detail, and Amendments.

Product Selector

1. Show Product Catalog from Zuora.
2. User can add an item from the catalog to their cart. For catalog items that are billed by quantity, a quantity can be entered before adding the item.
3. User can remove an item from their cart.
4. User can clear their current cart.
5. After selecting products, user clicks a button to continue to the Subscribe page.

### Subscribe

1. User can preview invoice amount of their selected products.
2. User enters their contact information.
3. User clicks subscribe.
  1. If there is an input error, User is asked to fix it and resubmit their information.
4. User's account is created and they are logged in and redirected to the account detail page.

### Account Detail

1. User arrives at this page through login, or immediately after subscribing.
2. User can view Account information, including:
  1. Account name
  2. Last payment date and amount
  3. Last invoice date
  4. Current account balance
  5. The PDF version of their latest invoice
3. User can manage the credit cards associated with this account.
  1. Display Credit Card holder name, Masked Credit Card number, and Expiration date of each card on file
  2. Add a new credit card, card will be validated as soon as it's submitted
  3. Switch to a different credit card
  4. Remove an existing credit card
4. User can manage Contact information.
  1. View Contact details associated with the account
  1. Update Contact details
5. User can view subscription information.
  1. View current subscription, including expiration dates for plans that will be removed in the future
  2. User can click a button to continue to the Amendments page

### Amend

1. Display Product Catalog and user's current subscription.
2. User can select a product from the catalog to add to their current subscription.
3. User can preview the amount of the additional charge they will incur before adding the new product to their subscription.
4. User can remove a product from their subscription effective at the end of the bill cycle.


## Technical Design Structure

 
The sample implementation is made up of four layers:

### User-Facing HTML

The files that an end user will see when using the store contained in the front-end user-facing HTML files. These pages use Javascript and AJAX to collect information and trigger events from the backend based on different user actions, by making GET calls to a backend URL. For each call, a listener is set up to capture the response from the response, which should contain all the data necessary to re-render components of the page.

### REST-Based PHP Event Dispatch Layer

In the backend folder is a file called index.php. Calls are made to this file from the front end to perform various actions. This page is used by accessing it via GET request with a type of action. For example, to retrieve the product catalog, you might call backend/index.php?type=GetProductCatalog. Based on the type passed, a different method of index.php will be called. This method will usually contain a call to one of the specialized PHP controllers to gather the information or perform the action requested. This controller will return a response value that indicates the success of the performed action. At the end of the execution of index.php, the data of the response is encoded in JSON and passed back to the HTML page that called it to re-render the page based on the result.

### Use Case Specialized PHP Controllers

These six classes contain methods to handle a number of common business cases necessary to integrate a B2C commerce platform with Zuora. These classes contain libraries of static methods that interface with Zuora, and perform other backend functions. These classes each return a response denoting the success of the operation, or an error description if something goes wrong. 

### Zuora/SFDC API Wrapper Classes

Whenever one of the specialized controls needs to access data from Zuora or Salesforce, methods from the zApi and sApi classes are called. The credentials to log in and use these services should be specified in the configuration file upon installation.

The sApi class is not a full extension of all Salesforce API services, but rather a set of methods that are useful when managing a B2C storefront with Zuora. The only integration requirement is that whenever a billing account is created in Zuora, a callout is made to create a customer account in Salesforce and retrieving the CRM Id, so that the CRM Id can be associated with the new billing account. After this step, all records relating to that billing account can be synced to SFDC using Zuora’s native integration and no additional integration is necessary.

The zApi class spans the full range of API calls supported by Zuora, including create, query, update, delete, amend, and subscribe. Each of these methods will take in and return a PHP object that directly correlates to the XML requests and responses associated with their soap calls. Details of all of these objects and fields are described in the Zuora API documentation, located at http://knowledgecenter.zuora.com/D_Zuora_API_Developers_Guide. 

