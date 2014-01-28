<?php

/*
 *    Copyright (c) 2012 Zuora, Inc.
 *    
 *    Permission is hereby granted, free of charge, to any person obtaining a copy of 
 *    this software and associated documentation files (the "Software"), to use copy, 
 *    modify, merge, publish the Software and to distribute, and sublicense copies of 
 *    the Software, provided no fee is charged for the Software.  In addition the
 *    rights specified above are conditioned upon the following:
 *    
 *    The above copyright notice and this permission notice shall be included in all
 *    copies or substantial portions of the Software.
 *    
 *    Zuora, Inc. or any other trademarks of Zuora, Inc.  may not be used to endorse
 *    or promote products derived from this Software without specific prior written
 *    permission from Zuora, Inc.
 *    
 *    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *    FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 *    ZUORA, INC. BE LIABLE FOR ANY DIRECT, INDIRECT OR CONSEQUENTIAL DAMAGES
 *    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *    ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */


/* * * * * * * * * *
* Zuora Credentials *
 *    (Required)     *
  * * * * * * * * * * */
 
$username = 'xxx@xxx.com';
$password = 'xxx';
$endpoint = 'https://www.zuora.com/apps/services/a/38.0';

/* * * * * * * * * *
* Additional Config *
 * * * * * * * * * * */

$wsdl='zuora.a.38.0.wsdl';


/* * * * * * * * * *
* Z-Payments Page   *
 *    (Required)     *
  * * * * * * * * * * */

$pageId = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$tenantId = "xxxx";
$apiSecurityKey = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=";
$appUrl = "https://www.zuora.com";

/* * * * * * * * * *
* SFDC Credentials  *
 * * * * * * * * * * */

//Enable this option to create a Salesforce account using the credentials below
$makeSfdcAccount = false;

$SfdcUsername = "xxxxxxxxxxxxxxxxxx";
$SfdcPassword = "xxxxxxxxx";
$SfdcSecurityToken = "xxxxxxxxxxxxxxxxxx";
$SfdcWsdl = "sfdc/enterprise.wsdl.xml"; // Use "sfdc/enterprise.wsdl.xml" for Production/Developmer (login.salesforce) or "sfdc/enterprise-sandbox.wsdl.xml" for Sandbox (test.salesforce)

/* * * * * * * * * * * * *
* Product Select Options  *
 * * * * * * * * * * * * * */

//Show All Products/Show subset
$showAllProducts=true;
//Separate Product list by field:
$groupingField="zillacloudcompany__c";
//Number of groups of above classification to show
$groupingFieldValues= array("Base Product", "Add-On Product");
//Locally cache the product catalog at this location to reduce load times.
$cachePath = "catalogCache.txt";

/* * * * * * * * * * * *
* New Account Defaults  *
 * * * * * * * * * * * * */

//New accounts are created in Zuora with the following default values
$defaultAutopay = true;
$defaultCurrency = "USD";
$defaultPaymentTerm = "Due Upon Receipt";
$defaultBatch = "Batch8";


?>