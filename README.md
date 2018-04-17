# OroAuthorizeNetBundle

OroAuthorizeNetBundle adds the [Authorize.net](https://www.authorize.net/) [integration](https://github.com/oroinc/platform/tree/master/src/Oro/Bundle/IntegrationBundle) to OroCommerce applications.

The bundle allows admin users to enable and configure the Authorize.net payment method for customer orders, which allows customers to pay for orders with credit and debit cards using Authorize.Net Payment Gateway.

## Setting Up the Connection

First of all, a new Integration with type "Authorize.Net" must be created.

Go to the "System -> Integrations -> Manage integrations" and click "Create Integration" button. Select "Authorize.NET" as a integration's type and fill all required fields.

"API Login ID", "Transaction Key" and "Client key" credentials can be obtained while registering your application at [Authorize.Net](https://www.authorize.net/)

"Test mode" means that all requests will be directed to "Authorize.NET" sandbox (test) server instead of production. See below, how to get test server credentials.

After that you need to create a payment rule for this integration. Go to "System -> Payment Rules" and click "Create payment Rule". Fill required fields and choose previously created integration as a "Payment Method Configuration".

From now, you customers can choose "Authorize.NET" as a payment method in checkout. Please note, that "Authorize.NET" payment method will be available only in case when your site is accessed via secured "HTTPS" protocol.
 
## Test settings

Authorize.NET have a [Sandbox test server](https://sandbox.authorize.net/) where you can register a test account and try it for free. To do this, please proceed to [Developers site](https://developer.authorize.net/) and follow instructions.

After registration, you will get an "API Login ID", "Transaction Key" and "Client key" credentials for Authorize.NET sandbox server. Use them when you create an integration, but don't forget to check "Test mode" checkbox.
