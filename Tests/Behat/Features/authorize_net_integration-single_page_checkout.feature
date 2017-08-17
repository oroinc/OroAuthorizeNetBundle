@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroAuthorizeNetBundle:AuthorizeNetFixture.yml
Feature: AuthorizeNet integration Single Page Checkout

  Scenario: Create new AuthorizeNet Integration
    Given I login as administrator
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

  Scenario: Create new Payment Rule for Authorize.Net integration
    Given I go to System/Payment Rules
    And I click "Create Payment Rule"
    And I fill form with:
      | Name       | Authorize |
      | Enabled    | true      |
      | Sort Order | 1         |
      | Method     | Authorize |
    And I click "Add Method Button"
    When I save and close form
    Then I should see "Payment rule has been saved" flash message

  Scenario: Enable SinglePage checkout
    Given go to System/Workflows
    When I click "Activate" on row "Single Page Checkout" in grid
    And I click "Activate"
    Then I should see "Workflow activated" flash message

  Scenario: Frontend AcceptJs Card validation error when pay order with AuthorizeNet
    Given There are products in the system available for order
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5555555555554444 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Submit Order"
    Then I should see only following flash messages:
      | Authorize.Net communication error. |

  Scenario: Error from Backend API when pay order with AuthorizeNet
    Given There are products in the system available for order
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5105105105105100 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Submit Order"
    Then I should see only following flash messages:
      | Invalid OTS Token.                                                                            |
      | We were unable to process your payment. Please verify your payment information and try again. |

  Scenario: Successful order payment with AuthorizeNet
    Given There are products in the system available for order
    When I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
