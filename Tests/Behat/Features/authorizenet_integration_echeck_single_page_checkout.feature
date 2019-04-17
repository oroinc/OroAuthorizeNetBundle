@regression
@ticket-ANET-45
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
Feature: AuthorizeNet integration eCheck single page checkout
  In order to have a fast and easy checkout
  As a Customer
  I want to have the ability to save information about bank account and reuse it on single page checkout with Authorize.Net

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
      | Name                      | AuthorizeNet                                                         |
      | Label                     | Authorize                                                            |
      | Short Label               | Au                                                                   |
      | Allowed Credit Card Types | Mastercard                                                           |
      | API Login ID              | qwer1234                                                             |
      | Transaction Key           | qwerty123456                                                         |
      | Client Key                | qwer12345                                                            |
      | Require CVV Entry         | true                                                                 |
      | Payment Action            | Authorize and Charge                                                 |
      | Status                    | Active                                                               |
      | Enable CIM                | true                                                                 |
      | CIM Websites              | Default                                                              |
      | Enable eCheck             | true                                                                 |
      | eCheck Label              | Bank Account                                                         |
      | eCheck Short Label        | Bank Account                                                         |
      | eCheck Confirm Text       | By clicking the button below, I authorize to charge my bank account. |
      | eCheck Account Types      | [Checking, Savings, Business Checking]                               |
    And I save and close form
    Then I should see "Integration saved" flash message
    And I should see AuthorizeNet in grid
    And I create payment rule with "Bank Account" payment method
    And I activate "Single Page Checkout" workflow

  Scenario: Checkout with bank account
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I am on homepage
    And I click "Account"
    And I click "Manage Payment Profiles"
    Then there is no records in "Authorize.NetGrid.eCheckProfile"
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Authorize.NetFormCheckoutEcheckPaymentProfileMethod" with:
      | Profile                   | New Bank Account          |
      | Account Type              | Checking                  |
      | Routing Number            | 091905444                 |
      | Account Number            | 123456789                 |
      | Name on Account           | Max Maxwell               |
      | Bank Name                 | Minnesota Lakes Bank      |
      | Save Profile              | true                      |
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    When I click "Account"
    And I click "Manage Payment Profiles"
    Then number of records in "Authorize.NetGrid.eCheckProfile" grid should be 1
    And number of records payment profiles in AuthorizeNet account should be 1

  Scenario: Create second default bank account
    Given I click "Add New Bank Account"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Name                      | Second bank account       |
      | Account Type              | Checking                  |
      | Routing Number            | 091905444                 |
      | Account Number            | 123450000                 |
      | Name on Account           | Max Maxwell               |
      | Bank Name                 | Minnesota Lakes Bank      |
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
    And number of records in "Authorize.NetGrid.eCheckProfile" grid should be 2

  Scenario: Checkout with existed cart
    Given I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    Then I should see that option "Second bank account (ends with 0000)" is selected in "Authorize.NetField.eCheckProfile" select
    And I should see "****6789 (ends with 6789)" for "Authorize.NetField.eCheckProfile" select
    When I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
