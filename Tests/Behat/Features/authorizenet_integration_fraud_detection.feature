@regression
@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
@behat-test-env
Feature: AuthorizeNet integration Fraud Detection
  In order user to able create order
  As a Customer
  I want to have the ability to create order when transaction limit per hour reached

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
      | Name                       | AuthorizeNet |
      | Label                      | Authorize    |
      | Short Label                | Au           |
      | Allowed Credit Card Types  | Visa         |
      | API Login ID               | qwer1234     |
      | Transaction Key            | qwerty123456 |
      | Client Key                 | qwer12345    |
      | Require CVV Entry          | true         |
      | Payment Action             | Authorize    |
      | Status                     | Active       |
    And the "Create orders for transactions held for review" checkbox should be checked
    And I save and close form
    Then I should see "Integration saved" flash message
    And I should see AuthorizeNet in grid
    And I create payment rule with "AuthorizeNet" payment method

  Scenario: Successful order with "Authorize" payment action
    Given There are products in the system available for order
    And I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    When I open page with shopping list List 1
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 4111111111111111 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I fill form with:
      | PO Number | 12345 |
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title

  Scenario: Successful order payment with verify action and "Authorize" payment action
    Given I proceed as the Admin
    When go to Sales/Orders
    Then I should see "12345" in grid with following data:
      | Payment Status | Pending payment |
    And click view "12345" in grid
    And I should see following "Order Payment Transaction Grid" grid:
      | Payment Method | Type   | Successful |
      | AuthorizeNet   | Verify | Yes        |
    When I click "Verify Status"
    Then I should see 'This transaction has been approved. You may initiate the charge by clicking "Capture".' flash message
    And I click "Capture"
    Then I should see "Charge The Customer" in the "UiWindow Title" element
    When I click "Yes, Charge" in modal window
    Then I should see "The payment of $13.00 has been captured successfully" flash message

  Scenario: Disable hold transaction option for AuthorizeNet Integration
    Given I proceed as the Admin
    And I go to System/Integrations/Manage Integrations
    And click edit "AuthorizeNet" in grid
    And I uncheck "Create orders for transactions held for review"
    And I save and close form
    Then I should see "Integration saved" flash message

  Scenario: Failed order creating with disabling hold transaction option
    Given I proceed as the Buyer
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 4111111111111111 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I fill form with:
      | PO Number | 54321 |
    When I click "Submit Order"
    Then I should see 'Unable to create an order because the transaction limit reached. Please enable "Create Orders For Transactions Held For Review" option.' flash message

  Scenario: Set "Authorize and Charge" payment action for AuthorizeNet Integration
    Given I proceed as the Admin
    And I go to System/Integrations/Manage Integrations
    And click edit "AuthorizeNet" in grid
    And I fill "Authorize.Net Form" with:
      | Payment Action | Authorize and Charge |
    And I check "Create orders for transactions held for review"
    And I save and close form
    Then I should see "Integration saved" flash message

  Scenario: Successful order with "Authorize and Charge" payment action
    Given I proceed as the Buyer
    When I open page with shopping list List 2
    And I click "Create Order"
    And I fill "Credit Card Form" with:
      | CreditCardNumber | 4111111111111111 |
      | Month            | 11               |
      | Year             | 2027             |
      | CVV              | 123              |
    And I click "Continue"
    And I fill form with:
      | PO Number | 54321 |
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title

  Scenario: Successful order payment with verify action and "Authorize and Charge" payment action
    Given I proceed as the Admin
    And go to Sales/Orders
    Then I should see following grid:
      | PO Number | Payment Status  |
      | 12345     | Paid in full    |
      | 54321     | Pending payment |
    And click view "Pending payment" in grid
    And I should see following "Order Payment Transaction Grid" grid:
      | Payment Method | Type   | Successful |
      | AuthorizeNet   | Verify | Yes        |
    When I click "Verify Status"
    Then I should see "This transaction has already been approved and charged. This order will be marked as paid."
    And I should not see "Capture"
