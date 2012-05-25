CHECKOUT BY AMAZON PLUGIN v1.0 FOR ZEN CART 1.3.9e
Author: Balachandar Muruganantham
Copyright: 2007-2010 Amazon Technologies, Inc
-----------------------------------------------
CONTENT SECTIONS (in order of appearance)
-----------------------------------------------
	INTRODUCTION
	RELEASE NOTES AND UPCOMING FEATURES
	IMPORTANT
	NOTICES FOR CERTAIN SOFTWARE COMPONENTS
	REQUIREMENTS
	SETUP ON ADMIN UI
	SUPPORT & PROJECT HOME
	LINKS
-----------------------------------------------
INTRODUCTION
-----------------------------------------------
	Please understand that by installing Checkout by Amazon Payment Module for Zen Cart, you are agreeing to understand and abide by the terms of the license, as written in LICENSE.txt.  
	Important links are grouped together in a separate section for your convenience.  The most current documentation on Checkout by Amazon can be found on its website. 

-----------------------------------------------
RELEASE NOTES AND UPCOMING FEATURES
-----------------------------------------------
	This is the first GA release for Checkout By Amazon Plugin v1.0 for Zen Cart and can be used in a production environment. We highly recommend the installation of this release by store owners who will be performing it themselves. 

	Following functionality of Checkout By Amazon is available in v1.0

	- Checkout with Amazon button on the Shopping Cart page
	- Amazon 1-Click, Express Checkout, and PayPhrase Checkout Integration
	- Signed / Unsigned Carts
	- Ability to add custom data required by merchants via custom data modules
	- Manage shipping rates, tax rates, promotions, and orders placed on Seller Central
	- Manage orders through your Zen Cart order management UI (using CBA feeds APIs)
	- Manage shipping and tax calculation dynamically when the order is being placed (using CBA Callback and Flexible Ship Options functionality)
	- Get instant notification as soon as the order is placed (using CBA IOPN functionality)

	Upcoming features
	- Alternate payment method listing

-----------------------------------------------
IMPORTANT
-----------------------------------------------
(1) Carefully follow all instructions in INSTALLATION_GUIDE.txt 
(2) You must have set up an Amazon Seller account & have your Merchant ID
(3) It is strongly encouraged to set up an AWS account, and have your AWS key ready to enable signed orders.  Unsigned orders are vulnerable to fraud.  
(4) Note that Checkout by Amazon standards do not allow the weight of products to be 0 at this time.  Thus, a weight tag = 0 will be removed from the XML cart before the order is processed
(5) The orders that are processed through the Checkout by Amazon Zen Cart plug-in should be managed *only* from within Zen Cart Order management page.  
Please do not use Seller Central to manage orders placed using the Zen Cart plug-in; if you are using Seller Central, then please manually update the same order in Zen Cart Order Management page
(6) The Checkout by Amazon Zen Cart plug-in allows you calculate tax rates or use shipping modules in Zen Cart and in Seller Central. You, the seller, will need to be sure that your desired tax rates and shipping modules are configured in Zen Cart and in Seller Central. The configured tax rates or shipping modules will be used to calculcate tax and shipping rates via Callbacks. If merchant's callback end point fails to respond within 5 seconds, settings from Seller Central are used.
(7) The price that is included in the XML carts sent to Checkout by Amazon from Zen Cart includes promotion prices set in Zen Cart; that is, the promotion
has already been applied to the item.  If you, the seller, also configure promotions in Seller Central, these will *also* be applied *in addition to* the promotion from Zen Cart.  
(8) Please note that the SKU, Title, & Description fields of products may be truncated when building the Checkout by Amazon cart.  This is to comply with the standards set by Checkout by Amazon.  Please see the Checkout by Amazon documentation in "LINKS" section below. 

-----------------------------------------------
LINKS and REFERENCES
-----------------------------------------------
	Know more about Checkout By Amazon
		https://payments.amazon.com/sdui/sdui/business?sn=cba/o&apaysccid=AP_ZENCART
	Checkout by Amazon Documentation
		https://sellercentral.amazon.com/gp/help/
	Amazon Web Services
		http://www.amazon.com/webservices 
	Amazon Seller Community Website
		http://www.amazonsellercommunity.com/forums/

-----------------------------------------------
NOTICES FOR CERTAIN SOFTWARE COMPONENTS
-----------------------------------------------
1.  Notices for Software Components Licensed Under the GNU General Public License. 

The following are notices for software components that are free software licensed under version 2 of the GNU General Public License (the "GPL"), the full text of which is set forth in the LICENSE.TXT file accompanying this package, as published by the Free Software Foundation (such software, collectively, the "GPL Software"). Portions of these software components have been modified by Amazon.com in order to function with Amazon's Checkout by Amazon service. 

a.  Modification Notice.  The GPL Software has been modified [2007-2008].

b.  Terms Applicable to the GPL.

The full text of the GPL is set forth in the LICENSE.TXT file accompanying this package.  You may redistribute the GPL Software and/or modify it under the terms of the GPL.  The GPL Software is distributed WITHOUT ANY WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE OR NON-INFRINGEMENT.  You may, for a period of up to three years, obtain, for a charge equal to the cost of physically performing source distribution, a complete machine-readable copy of the corresponding source code. See the terms of the GPL for more details.  You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA. 

c.	Name and Copyright Notice.

The following are the names of the GPL Software and the applicable copyright notices:

- Zen Cart (c) 2003-2010 Zen Ventures LLC
- XmlBuilder.php (c) 2006 Google Inc.
- XML Library, by Keith Devens, version 1.2b
- XML Library changes - osCommerce, Harald Ponce de Leon 2005-01-22; 
 
2.  Notices for Software Components Licensed Under the PHP license.

The following are notices for software components that are free software licensed under the PHP License version 3.0 (such software, collectively, the "PHP Software").

a.	Terms Applicable to the PHP Software.

The PHP Software includes PHP, freely available from http://www.php.net/. Redistribution and use of the PHP Software, in source and binary forms, with or without modification, is permitted provided that the following conditions are met: 

 1.  Redistributions of source code must retain the following copyright
     notice, this list of conditions and the following disclaimer.

     "Copyright (c) 1999 - 2006 The PHP Group. All rights reserved."
 
  2. Redistributions in binary form must reproduce the above copyright
     notice, this list of conditions and the following disclaimer in
     the documentation and/or other materials provided with the
     distribution.
 
  3. The name "PHP" must not be used to endorse or promote products
     derived from this software without prior written permission. For
     written permission, please contact group@php.net.
  
  4. Products derived from this software may not be called "PHP", nor
     may "PHP" appear in their name, without prior written permission
     from group@php.net.  You may indicate that your software works in
     conjunction with PHP by saying "Foo for PHP" instead of calling
     it "PHP Foo" or "phpfoo"
 
  5. The PHP Group may publish revised and/or new versions of the
     license from time to time. Each version will be given a
     distinguishing version number.
     
     Once covered code has been published under a particular version
     of the license, you may always continue to use it under the terms
     of that version. You may also choose to use such covered code
     under the terms of any subsequent version of the license
     published by the PHP Group. No one other than the PHP Group has
     the right to modify the terms applicable to covered code created
     under this License.

  6. Redistributions of any form whatsoever must retain the following
     acknowledgment:
     
     "This product includes PHP, freely available from <http://www.php.net/>".  

THIS SOFTWARE IS PROVIDED BY THE PHP DEVELOPMENT TEAM ``AS IS'' AND 
ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE PHP
DEVELOPMENT TEAM OR ITS CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
OF THE POSSIBILITY OF SUCH DAMAGE.

b.  Name and Copyright Notice.

The following is the names of the PHP Software and the relevant copyright notices.

- HMAC.php (c) 1997-2005 The PHP Group. All rights reserved. 
-----------------------------------------------
REQUIREMENTS
-----------------------------------------------
(1) Zen Cart 1.3.9e or 1.3.8a must be installed 
	We have not tested the plug-in with other versions of Zen Cart. Please use it at your own risk. 
(2) Seller account from Amazon.com
	Please see the Seller Central website in "LINKS" section of this document.
(2a)Your Merchant ID.
      - Log in to Seller Central (Please see Seller Central website in "LINKS" section of this document.)
      - Click Settings -> 'Checkout Pipeline Settings' tab
      - 'Checkout Pipeline Settings' page has section which displays your Merchant ID.

-----------------------------------------------
SET-UP ON ADMIN UI
-----------------------------------------------

1. General Options
------------------
  a. Enable the Checkout by Amazon Module: Set this to "true" to use Checkout by Amazon as an option on your website.
  b. Checkout by Amazon Merchant ID: Enter your Merchant ID to enable Checkout by Amazon on your website. You can find your Merchant ID in Seller Central by clicking Settings > Checkout Pipeline Settings.
  c. Operating Environment: Select your operating environment, Sandbox (testing) or Production (live orders).
  d. Checkout Button Size: Set the button size you want to use, either large (151 x 27 px) or medium (126 x 24 px).
  e. Button Style: Select the color of the button you want to place on your website.
  f. Sort Order Display: Select the order you want to display Amazon Payments relative to your other payment options, from lowest value (first position) to higher values (secondary positions).
  g. Cart Expiration Time: (Optional) Set the time interval before a customer's cart expires and is reset. If you do not set this value, the cart will not expire.
  h. Cancellation Return Page: The page you want to send customers to when they cancel an order within the checkout pipeline (before they click the "Place Your Order" button). The default setting returns your customers to the catalog index page without clearing the shopping cart.
  i. Success Return Page: The page you want to send customers to when they successfully complete an order within the checkout pipeline (that is, they click the "Place Your Order" button). The default setting returns your customers to the catalog index page in your store.
  j. Enable Diagnostic Logging: If set to true, enables logging for debugging. The debugging log is located in your catalog/checkout_by_amazon/log/ directory, and is named checkout_by_amazon.log.

2. Signing Options
------------------
  a. Enable Order Signing: Select whether you want to accept signed orders (secure) or unsigned (insecure). As unsigned orders are vulnerable for fraud, we recommend you enable signed orders.
  b. AWS Access Key ID: Enter your Seller Central AWS Access Key ID. This key, paired with your Seller Central AWS Secret Key, is required for signed orders. You can get your Seller Central AWS Access Key in Seller Central by clicking Integration > Access Key.
  c. AWS Secret Key: Enter your Seller Central AWS Secret Access Key. This key, paired with your Seller Central AWS Access Key ID, is required for signed orders. Treat your secret key like a password. You can get your Seller Central AWS Access Key in Seller Central by clicking Integration > Access Key and then clicking +Show.

3. Order Management Options
-----------------------------
  a. Enable Order Management: Enable this feature to view and manage (ship, refund, or cancel) Checkout by Amazon orders within the Admin Order UI without logging in to Seller Central. Before you enable this feature, you must enable programmatic XML feeds. Please contact us to enable XML feeds.
  b. Checkout by Amazon Merchant Login ID: Enter your login ID (the e-mail address you use to log in to your Checkout by Amazon merchant account on Seller Central).
  c. Checkout by Amazon Merchant Password: Enter your password (the password you use to log in to your Checkout by Amazon merchant account on Seller Central).
  d. Checkout by Amazon Merchant Token: Enter your Merchant Token. You can get your Merchant Token in Seller Central by clicking Settings > Account Info and scrolling down to the Your Merchant Token section.
  e. Checkout by Amazon Merchant Name: Enter your Merchant Business Display Name. You can get your Merchant Business Display Name in Seller Central by clicking Settings > Account Info and scrolling down to the Your Business Information section.

4. Order Status Mapping
-------------------------
Order status mapping is required to map the amazon order status with your own custom status in this site. If the recommended status is *not* available, please add it by going to your order status page For example, http://mystore/admin/orders_status.php

  a. ReadyToShip Order Status: What should be the order status after Amazon processes it? This state will indicate that the order is pending shipment from your end. Recommended: Pending
  b. Delivered Order Status: What should be the order status after you deliver it? The order will move into this state when you click the button. Recommended: Delivered
  c. Refund Order Status: What should be the order status after you apply a refund on it? The order will move into this state when you click the button. Recommended: Refund
  d. Canceled Order Status: What should be the order status when the order gets canceled? The order will move into this state when you click the button or when the buyer or Amazon cancels it. Recommended: Canceled

5. Callback Options
-------------------
IMPORTANT: To enable dynamic shipping and tax calculation using callback, you should have a https enabled callback endpoint.

  a. Enable Callbacks: You can use the Callback API to dynamically calculate shipping (using USPS, FedEx, UPS or any *enabled* shipping modules) or taxes as calculated by your site when customers are in the checkout pipeline.
  b. Callback Page: Enter the URL on your site where Amazon sends the callback request for dynamic rate calculations. The URL should contain the host and port, and should be set to the path catalog/checkout_by_amazon.php. For example, https://mydomain.com/catalog/checkout_by_amazon.php.
  c. Enable Shipping Calculations: Enable dynamic shipping rate calculations (callbacks) from any of the shipping modules.. You can enable multiple shipping modules in your Modules > Shipping section in the Admin. 
  d. Enable Tax Calculations: Enable dynamic tax rate calculations (callbacks). Before you enable this feature, apply the correct tax classes and tax rates to your inventory items.
  e. Is Shipping and Handling Taxed: Select to specify whether to apply the tax rate to the shipping costs as part of the callback.

6. IOPN Setup
-------------------
IMPORTANT: To enable IOPN, you should have a https enabled IOPN endpoint.

  a. Your IOPN End point is https://www.yourdomain.com/checkout_by_amazon.php
  b. Go to Seller Central > Settings > Checkout Pipeline Settings > Instant Order Processing Notification Settings. Click the "Edit" button and update the Merchant URL.

Review your site 
-----------------
  You are strongly encouraged to test several orders in the "Sandbox" environment before enabling "Production" environment

-----------------------------------------------
SUPPORT & PROJECT HOME
-----------------------------------------------
	The latest documentation on Checkout by Amazon can be found at in the LINKS section below.
	The latest version of Checkout by Amazon for Zen Cart can be downloaded from the link below.   

