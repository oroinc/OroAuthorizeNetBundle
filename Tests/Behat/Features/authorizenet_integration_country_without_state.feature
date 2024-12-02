@regression
@ticket-BB-16129
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
@behat-test-env
Feature: AuthorizeNet integration Country without State
  In order to purchase goods using Authorize.Net payment system
  As a Customer
  I want to complete checkout without setting a State on billing/shipping information steps

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Create new AuthorizeNet Integration
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/Integrations/Manage Integrations
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
    And I save and close form
    Then I should see "Integration saved" flash message
    And I should see AuthorizeNet in grid
    And I create payment rule with "AuthorizeNet" payment method

  Scenario: Successful order payment with AuthorizeNet
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 1
    And I click "Create Order"
    And I click "Add Address"
    And I fill "New Address Popup Form" with:
      | Label                  | Address without a State |
      | First Name             | Tester1                 |
      | Last Name              | Testerson1              |
      | Email                  | tester1@test.com        |
      | Street                 | Fifth avenue            |
      | City                   | City                    |
      | Country                | American Samoa          |
      | Zip/Postal Code        | 10115                   |
    And I click "Add Address" in modal window
    And I click "Continue"
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
