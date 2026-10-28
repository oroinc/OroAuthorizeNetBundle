@ticket-BB-28023
@regression
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
@behat-test-env
Feature: Single page checkout completion when Authorize.Net gateway fails after validation

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

  Scenario: Checkout does not complete when the purchase call to the gateway fails after validation succeeded
    Given There are products in the system available for order
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "ORO, Fifth avenue, 10115 Berlin, Germany" from "Billing Address"
    And I select "ORO, Fifth avenue, 10115 Berlin, Germany" from "Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 5500005555555559 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Submit Order"
    Then I should see only following flash messages:
      | We were unable to process your payment. Please verify your payment information and try again. |
    And I should not see "Thank You For Your Purchase!"
