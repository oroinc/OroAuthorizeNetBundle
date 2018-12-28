@regression
@ticket-ANET-29
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroAuthorizeNetBundle:AuthorizeNetFixture.yml
Feature: AuthorizeNet integration CIM
  In order to have a fast and easy checkout
  As a Customer
  I want to have the ability to save information about payment card and reuse it on checkout with Authorize.Net

  Scenario: Create different window session
    Given sessions active:
      | Admin  |first_session |
      | Buyer  |second_session|

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

  Scenario: Check "Manage Payment Profiles" when CIM disabled
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I am on homepage
    And I click "Account"
    Then I should not see "Manage Payment Profiles"

  Scenario: Check "Manage Payment Profiles" when CIM enabled
    Given I proceed as the Admin
    And I go to System/Integrations/Manage Integrations
    And I click edit AuthorizeNet in grid
    And I fill "Authorize.Net Form" with:
      | Enable CIM    | true     |
      | CIM Websites  | Default  |
    And I save and close form
    Then I should see "Integration saved" flash message
    When I proceed as the Buyer
    And I reload the page
    Then I should see "Manage Payment Profiles"
    When I click "Manage Payment Profiles"
    Then there is no records in "Credit Card Profile Grid"
    And I should see "Add New Credit Card"

  Scenario: Create new credit card
    Given I click "Add New Credit Card"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Name                      | First credit card         |
      | Credit Card Number        | 5424000000000015          |
      | Month                     | 11                        |
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
    And number of records in "Credit Card Profile Grid" grid should be 1
    And I have 1 payment profiles in AuthorizeNet account

  Scenario: Update credit card
    Given I click Edit "First credit card" in grid
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Update Credit Card Information | true               |
      | Credit Card Number             | 5424000000001500   |
      | Month                          | 10                 |
      | Year                           | 2027               |
      | CVV                            | 123                |
    And I submit form
    Then I should see "Payment profile has been saved successfully." flash message
    And 1500 must be first record in "Credit Card Profile Grid"

  Scenario: Delete credit card
    Given I click "Delete" on row "First credit card" in grid
    And I click "Yes" in confirmation dialogue
    Then there is no records in "Credit Card Profile Grid"
    And I have 0 payment profiles in AuthorizeNet account

  Scenario: Refresh grid and reset grid
    Given I click "Add New Credit Card"
    And I fill "Authorize.NetForm.PaymentProfile" with:
      | Name                      | First credit card         |
      | Credit Card Number        | 5424000000000015          |
      | Month                     | 11                        |
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
    Then number of records in "Credit Card Profile Grid" grid should be 1
    And I have 1 payment profiles in AuthorizeNet account
    When I remove last payment profile from AuthorizeNet account
    And I refresh "Credit Card Profile Grid" grid
    Then there is no records in "Credit Card Profile Grid"
    When I reset "Credit Card Profile Grid" grid
    Then there is no records in "Credit Card Profile Grid"

  Scenario: Checkout with new cart
    Given I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Checkout Payment Profile Method Form" with:
      | Profile                        | New Card           |
      | Credit Card Number             | 5424000000001500   |
      | Month                          | 10                 |
      | Year                           | 2027               |
      | CVV                            | 123                |
      | SaveProfile                    | true               |
    And I click "Continue"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    When I click "Account"
    And I click "Manage Payment Profiles"
    Then number of records in "Credit Card Profile Grid" grid should be 1

  Scenario: Checkout with existed cart
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
    And number of records in "Credit Card Profile Grid" grid should be 2
    When There are products in the system available for order
    And I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Checkout Payment Profile Method Form" with:
      | ProfileCVV | 123 |
    Then I should see that option "Second credit card (ends with 0015)" is selected in "CreditCardProfile" select
    And I should see "****1500 (ends with 1500)" for "CreditCardProfile" select
    And I click "Continue"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
