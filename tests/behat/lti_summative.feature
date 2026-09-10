@local @local_assess_type @javascript
Feature: LTI activities can be marked as summative
  In order to identify which LTI activities count towards the final mark
  As a teacher
  I need to be able to set an LTI activity as summative.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname | lastname | email                    |
      | teacher1 | Teacher   | 1        | teacher1@example.com     |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "mod_lti > course tools" exist:
      | name          | baseurl                                   | course |
      | Course tool 1 | /mod/lti/tests/fixtures/tool_provider.php | C1     |
      | Course tool 2 | /mod/lti/tests/fixtures/tool_provider.php | C1     |

  Scenario: Admin can select LTI types that can be marked as summative
    When I log in as "admin"
    And I navigate to "Plugins > Local > Assessment type settings" in site administration
    Then "LTI Types" "field" should exist
    And the "LTI Types" select box should contain "Course tool 1"
    And the "LTI Types" select box should contain "Course tool 2"

  Scenario: User cannot mark an LTI activity as summative if it has not been configured to allow this
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Course tool 1" to section "1" using the activity chooser
    Then "Formative or summative?" "field" should not exist

  Scenario: User can mark an LTI activity as summative if it has been configured to allow this
    Given I log in as "admin"
    And I navigate to "Plugins > Local > Assessment type settings" in site administration
    And I set the field "LTI Types" to "Course tool 1"
    And I press "Save changes"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I add a "Course tool 1" to section "1" using the activity chooser
    Then "Formative or summative?" "field" should exist
