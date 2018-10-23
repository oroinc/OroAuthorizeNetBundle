# OroAuthorizeNetBundle

OroAuthorizeNetBundle adds the [Authorize.Net](https://www.authorize.net/) [integration](https://github.com/oroinc/platform/tree/master/src/Oro/Bundle/IntegrationBundle) to OroCommerce applications.

The bundle allows admin users to enable and configure the Authorize.Net payment method for customer orders, which allows customers to pay for orders with credit and debit cards or eChecks using Authorize.Net Payment Gateway.

## Setting Up the Connection

First of all, a new Integration with type "Authorize.Net" must be created.

Go to the "System -> Integrations -> Manage integrations" and click "Create Integration". Select "Authorize.Net" as the integration type and fill all required fields.

"API Login ID", "Transaction Key" and "Client key" credentials can be obtained while registering your application at [Authorize.Net](https://www.authorize.net/)

"Test mode" means that all requests will be directed to "Authorize.Net" sandbox (test) server instead of production. See below, how to get test server credentials.

After that you need to create a payment rule for this integration. Go to "System -> Payment Rules" and click "Create payment Rule". Fill required fields and choose previously created integration as a "Payment Method Configuration".

Your customers can now choose "Authorize.Net" as a payment method at checkout. Please note that Authorize.Net payment method will only be available when your site is accessed via the secure "HTTPS" protocol.

## eCheck payments

In addition to regular credit card payments, Authorize.Net allows to process [eCheck transactions](https://www.authorize.net/payments/echeck/).

Before enabling eCheck payment option in the integration settings, please make sure that it is enabled for your Authorize.Net merchant account.

Enabling eCheck option in the integration settings turns on eCheck payment option for Payment Rules and allows to manage eCheck payment profiles, if [CIM](#customer-information-management) is enabled.

eCheck transactions are placed with the "Authorize and Charge" payment action, which is the only available option.

## Customer Information Management

To simplify checkout process for registered customers, [CIM](https://www.authorize.net/our-features/secure-customer-data/) can be enabled in the integration settings. It allows customers to store and manage their payment profiles (credit cards or eCheck) and pay with a saved profile during checkout.

In case of a multi-website setup, you should also choose which websites this CIM integration will be used for.

If CIM is enabled for the integration, the new "Manage payment profiles" section is added under "My Account". Both Credit Card and eCheck profiles can be added, updated or removed there.
For payment profiles, in addition to payment data, billing address data is collected and sent as well. It can be used on Authorize.Net side for additional verifications.
Each profile can have a name defined by the customer which will be listed in the profile dropdown at checkout. Also, payment profile can be marked with default flag. Default profiles are pre-selected in the profiles dropdown at checkout. 

On the checkout, registered customer users have an option to save payment data for the later use. If corresponding checkbox is selected, a payment profile is created in Authorize.Net and becomes available for future payments.

Sensitive payment data (credit card number, cvv, eCheck account number etc.) is neither passed nor stored in the application and is securely transferred to Authorize.Net using [Accept.js](https://developer.authorize.net/api/reference/features/acceptjs.html). 

## Test settings

Authorize.Net has a [Sandbox test server](https://sandbox.authorize.net/) where you can register a test account and try it for free. To do this, please proceed to [Developers site](https://developer.authorize.net/) and follow instructions.

After registration, you will get an "API Login ID", "Transaction Key" and "Client key" credentials for Authorize.Net sandbox server. Use them when you create an integration, but remember to select the "Test mode" checkbox.

To be able to test CIM integration within sandbox account, the sandbox account must be switched to the "Live" mode. 
