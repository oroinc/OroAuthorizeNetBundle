@ticket-BB-18064
@regression
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
Feature: AuthorizeNet integration Single Page Checkout
  ToDo: BAP-16103 Add missing descriptions to the Behat features

  Scenario: Create new AuthorizeNet Integration
    Given I login as administrator
    When I go to System/Integrations/Manage Integrations
    And I click "Create Integration"
    And I select "Authorize.Net" from "Type"
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
    And I activate "Single Page Checkout" workflow

  Scenario: Validation error should not appear if expiration date is valid
    Given There are products in the system available for order
    And I signed in as AmandaRCole@example.org on the store frontend
    And I open page with shopping list List 2
    And I click "Create Order"
    When I fill "Credit Card Form" with:
      | Month | 10 |
    Then I should not see "Invalid Expiration date."
    When I click "Submit Order"
    Then I should see "Invalid Expiration date."
    When I fill "Credit Card Form" with:
      | Year | 2029 |

  Scenario: Frontend AcceptJs Card validation error when pay order with AuthorizeNet
    Given I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5555555555554444 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    When I click "Submit Order"
    Then I should see only following flash messages:
      | Payment gateway error. User authentication failed due to invalid authentication values. |

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
      | We were unable to process your payment. Please verify your payment information and try again. |

  Scenario: Successful order payment with AuthorizeNet
    Given There are products in the system available for order
    When I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I scroll to top
    And I check "Use billing address" on the checkout page
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title

  Scenario: Data from credit card form keeps unchanged after user entered new address
    Given There are products in the system available for order
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5424000000000015 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I scroll to top
    And I check "Use billing address" on the checkout page
    Then CreditCardFormCreditCardNumber field should has 5424000000000015 value
    When I click on "Billing Address Select"
    And I click on "New Address Option"
    And I fill "New Address Popup Form" with:
      | Email        | test@example.com |
    And I click "Continue"
    And I scroll to top
    And I wait until all blocks on one step checkout page are reloaded
    Then CreditCardFormCreditCardNumber field should has 5424000000000015 value
