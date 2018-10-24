@regression
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroAuthorizeNetBundle:AuthorizeNetFixture.yml
Feature: Process order submission with Authorize_Net integration
  ToDo: BAP-16103 Add missing descriptions to the Behat features

  Scenario: Create sessions
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Create new AuthorizeNet Integration
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/Integrations/Manage Integrations
    And I click "Create Integration"
    And I select "Authorize.Net" from "Type"
    And I fill "Authorize.Net Form" with:
      | Name                      | AuthorizeNet |
      | Label                     | Authorize    |
      | Short Label               | Au           |
      | Allowed Credit Card Types | Mastercard   |
      | API Login ID              | qwer1234     |
      | Transaction Key           | qwerty123456 |
      | Client Key                | qwer12345    |
      | Require CVV Entry         | true         |
      | Payment Action            | Authorize    |
    And I save and close form
    Then I should see "Integration saved" flash message
    And I should see AuthorizeNet in grid

  Scenario: Create new Payment Rule for Authorize.Net integration
    Given I go to System/Payment Rules
    And I click "Create Payment Rule"
    And I check "Enabled"
    And I fill in "Name" with "Authorize"
    And I fill in "Sort Order" with "1"
    And I select "Authorize" from "Method"
    And I click "Add Method Button"
    And I save and close form
    Then I should see "Payment rule has been saved" flash message

  Scenario: Successful order payment with AuthorizeNet
    Given There are products in the system available for order
    And I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title

  Scenario: Successful capture of authorized order
    Given I proceed as the Admin
    And I am on dashboard
    When I go to Sales/Orders
    And I click View Payment authorized in grid
    And I click "Capture"
    Then I should see "Charge The Customer" in the "UiWindow Title" element
    When I click "Yes, Charge" in modal window
    Then I should see "The payment of $13.00 has been captured successfully" flash message
