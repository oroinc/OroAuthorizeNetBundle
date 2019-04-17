@regression
@ticket-BB-13976
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
Feature: AuthorizeNet integration guest Checkout
  In order to purchase goods using Authorize.Net payment system
  As a Guest customer
  I want to enter and complete checkout without registration with payment via Authorize.Net

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

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

  Scenario: Enable guest shopping list setting
    Given I go to System/ Configuration
    And I follow "Commerce/Sales/Shopping List" on configuration sidebar
    And uncheck "Use default" for "Enable guest shopping list" field
    And I check "Enable guest shopping list"
    And I save form
    Then I should see "Configuration saved" flash message
    And the "Enable guest shopping list" checkbox should be checked

  Scenario: Enable guest checkout setting
    Given I follow "Commerce/Sales/Checkout" on configuration sidebar
    And uncheck "Use default" for "Enable Guest Checkout" field
    And I check "Enable Guest Checkout"
    When I save form
    Then the "Enable Guest Checkout" checkbox should be checked

  Scenario: Prepare the first shopping list for order from under an unauthorized user
    Given I proceed as the User
    And I am on homepage
    And type "SKU123" in "search"
    And I click "Search Button"
    And I click "product1"
    When I click "Add to Shopping List"
    Then I should see "Product has been added to" flash message
    When I open page with shopping list "Shopping List"
    Then I should see "product1"

  Scenario: Successful first order payment with AuthorizeNet
    Given I click "Create Order"
    And I click "Continue as a Guest"
    And I fill form with:
      | First Name      | Tester1          |
      | Last Name       | Testerson1       |
      | Email           | tester1@test.com |
      | Street          | Fifth avenue     |
      | City            | Berlin           |
      | Country         | Germany          |
      | State           | Berlin           |
      | Zip/Postal Code | 10115            |
    And I click "Ship to This Address"
    And I click "Continue"
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I uncheck "Save my data and create an account" on the checkout page
    And I press "Submit Order"
    Then I should see "Thank You For Your Purchase!"

  Scenario: Prepare the second shopping list for order from under an unauthorized user
    Given I am on homepage
    And type "SKU123" in "search"
    And I click "Search Button"
    And I click "product1"
    And I click "Add to Shopping List"
    And I should see "Product has been added to" flash message
    When I click "Shopping List"
    Then I should see "product1"

  Scenario: Second successful order payment with AuthorizeNet after first order payment
    Given I click "View Shopping List Details"
    And I click "Create Order"
    And I click "Continue as a Guest"
    And I fill form with:
      | First Name      | Tester2          |
      | Last Name       | Testerson2       |
      | Email           | tester2@test.com |
      | Street          | Fifth avenue     |
      | City            | Berlin           |
      | Country         | Germany          |
      | State           | Berlin           |
      | Zip/Postal Code | 10115            |
    And I click "Ship to This Address"
    And I click "Continue"
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I uncheck "Save my data and create an account" on the checkout page
    And I press "Submit Order"
    Then I should see "Thank You For Your Purchase!"
