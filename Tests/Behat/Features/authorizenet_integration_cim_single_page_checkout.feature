@regression
@ticket-ANET-29
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
@behat-test-env
Feature: AuthorizeNet integration CIM single page checkout
  In order to have a fast and easy checkout
  As a Customer
  I want to have the ability to save information about payment card and reuse it on single page checkout with Authorize.Net

  Scenario: Feature Background
    Given sessions active:
      | Admin  |first_session |
      | Buyer  |second_session|

  Scenario: Create new AuthorizeNet Integration
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/Integrations/Manage Integrations
    And I click "Create Integration"
    And I select "Authorize.NET" from "Type"
    And I fill "Authorize.Net Form" with:
      | Name                      | AuthorizeNet         |
      | Label                     | Authorize            |
      | Short Label               | Au                   |
      | Allowed Credit Card Types | Mastercard           |
      | API Login ID              | qwer1234             |
      | Transaction Key           | qwerty123456         |
      | Client Key                | qwer12345            |
      | Require CVV Entry         | true                 |
      | Payment Action            | Authorize and Charge |
      | Status                    | Active               |
      | Enable CIM                | true                 |
      | CIM Websites              | Default              |
    And I save and close form
    Then I should see "Integration saved" flash message
    And I should see AuthorizeNet in grid
    And I create payment rule with "AuthorizeNet" payment method
    And I activate "Single Page Checkout" workflow

  Scenario: Checkout with new cart
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I am on homepage
    And I click "Account Dropdown"
    And I click "Manage Payment Profiles"
    Then there is no records in "Authorize.NetGridCreditCardProfile"
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "ORO, Fifth avenue, 10115 Berlin, Germany" from "Billing Address"
    And I select "ORO, Fifth avenue, 10115 Berlin, Germany" from "Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Authorize.NetFormCheckoutCreditCardPaymentProfileMethod" with:
      | Profile                        | New Card           |
      | Credit Card Number             | 5424000000001500   |
      | Month                          | 10                 |
      | CVV                            | 123                |
      | Save Profile                   | true               |
    Then I should not see "Invalid Expiration date."
    When I click "Submit Order"
    Then I should see "Invalid Expiration date."
    When I fill "Authorize.NetFormCheckoutCreditCardPaymentProfileMethod" with:
      | Year | 2027 |
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    And I click "Account Dropdown"
    And I click "Manage Payment Profiles"
    Then number of records in "Authorize.NetGridCreditCardProfile" grid should be 1
    And number of records payment profiles in AuthorizeNet account should be 1

  Scenario: Create second default cart
    Given I click "Add New Credit Card"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Name                      | Second credit card        |
      | Credit Card Number        | 5424000000000015          |
      | Month                     | 10                        |
      | Year                      | 2027                      |
      | CVV                       | 123                       |
      | First Name                | Max                       |
      | Last Name                 | Maxwell                   |
      | Street                    | 4576 Stonepot Road        |
      | Country                   | Germany                   |
      | City                      | Berlin                    |
      | State                     | Bayern                    |
      | Zip                       | 10115                     |
      | Profile Default           | true                      |
    And I submit form
    Then I should see "Payment profile has been saved successfully." flash message
    And number of records in "Authorize.NetGridCreditCardProfile" grid should be 2
    And number of records payment profiles in AuthorizeNet account should be 2

  Scenario: Checkout with existed cart
    Given I open page with shopping list List 1
    And I click "Create Order"
    And I select "ORO, Fifth avenue, 10115 Berlin, Germany" from "Billing Address"
    And I select "ORO, Fifth avenue, 10115 Berlin, Germany" from "Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Authorize.NetFormCheckoutCreditCardPaymentProfileMethod" with:
      | ProfileCVV | 123 |
    Then I should see that option "Second credit card (ends with 0015)" is selected in "Authorize.NetField.CreditCardProfile" select
    And I should see "****1500 (ends with 1500)" for "Authorize.NetField.CreditCardProfile" select
    When I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
