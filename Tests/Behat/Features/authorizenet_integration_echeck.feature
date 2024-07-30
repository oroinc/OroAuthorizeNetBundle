@regression
@ticket-ANET-45
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
@behat-test-env
Feature: AuthorizeNet integration eCheck
  In order to have a fast and easy checkout
  As a Customer
  I want to have the ability to save information about bank account and reuse it on checkout with Authorize.Net

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

  Scenario: Check "eCheck grid" when option eCheck disabled
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I am on homepage
    And I click "Account Dropdown"
    And I click "Manage Payment Profiles"
    Then I should not see an "Authorize.NetGrid.eCheckProfile" element
    And I should not see "Add New Bank Account"

  Scenario: Check "eCheck grid" when option eCheck enabled
    Given I proceed as the Admin
    And I go to System/Integrations/Manage Integrations
    And I click edit AuthorizeNet in grid
    And I fill "Authorize.Net Form" with:
      | Enable eCheck           | true                                                                 |
      | eCheck Label            | Bank Account                                                         |
      | eCheck Short Label      | Bank Account                                                         |
      | eCheck Confirm Text     | By clicking the button below, I authorize to charge my bank account. |
      | eCheck Account Types    | [Checking, Savings, Business Checking]                               |
    And I save and close form
    Then I should see "Integration saved" flash message
    When I create payment rule with "Bank Account" payment method
    And I proceed as the Buyer
    And I reload the page
    Then I should see "Add New Bank Account"
    And I should see an "Authorize.NetGrid.eCheckProfile" element
    And there is no records in "Authorize.NetGrid.eCheckProfile"

  Scenario: Create new bank account
    Given I click "Add New Bank Account"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Name                      | First bank account        |
      | Account Type              | Checking                  |
      | Routing Number            | 091905444                 |
      | Account Number            | 123456789                 |
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
    And number of records in "Authorize.NetGrid.eCheckProfile" grid should be 1
    And number of records payment profiles in AuthorizeNet account should be 1

  Scenario: Update bank account
    Given I click Edit "First bank account" in "Authorize.NetGrid.eCheckProfile"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Update Bank Account Information | true                      |
      | Account Type                    | Checking                  |
      | Routing Number                  | 091905444                 |
      | Account Number                  | 123450000                 |
      | Name on Account                 | Max Maxwell               |
      | Bank Name                       | Minnesota Lakes Bank      |
    And I submit form
    Then I should see "Payment profile has been saved successfully." flash message
    And 0000 must be first record in "Authorize.NetGrid.eCheckProfile"

  Scenario: Delete credit card
    Given I click "Delete" on row "First bank account" in "Authorize.NetGrid.eCheckProfile"
    And I click "Yes" in confirmation dialogue
    Then there is no records in "Authorize.NetGrid.eCheckProfile"
    And number of records payment profiles in AuthorizeNet account should be 0

  Scenario: Refresh grid and reset grid
    Given I click "Add New Bank Account"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Name                      | First bank account        |
      | Account Type              | Checking                  |
      | Routing Number            | 091905444                 |
      | Account Number            | 123456789                 |
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
    Then number of records in "Authorize.NetGrid.eCheckProfile" grid should be 1
    And number of records payment profiles in AuthorizeNet account should be 1
    When I remove last added payment profile from AuthorizeNet account
    And I refresh "Authorize.NetGrid.eCheckProfile" grid
    Then there is no records in "Authorize.NetGrid.eCheckProfile"

  Scenario: Checkout with new bank account
    Given I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Authorize.NetFormCheckoutEcheckPaymentProfileMethod" with:
      | Profile                   | New Bank Account          |
      | Account Type              | Checking                  |
      | Routing Number            | 091905444                 |
      | Account Number            | 123456789                 |
      | Name on Account           | Max Maxwell               |
      | Bank Name                 | Minnesota Lakes Bank      |
      | Save Profile              | true                      |
    And I click "Continue"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    And I click "Account Dropdown"
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

  Scenario: Checkout with existed bank account
    Given I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    Then I should see that option "Second bank account (ends with 0000)" is selected in "Authorize.NetField.eCheckProfile" select
    And I should see "****6789 (ends with 6789)" for "Authorize.NetField.eCheckProfile" select
    When I click "Continue"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
