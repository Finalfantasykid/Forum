Feature: Login
    In order to use the forum
    As a valid user on the forums
    I need to be able to login

    Scenario: Viewing page as guest
        Given I am on "index.php"
        Then I should see "Username:"
        And I should see "Password:"

    Scenario: Logging in as a valid user
        Given I am logged in as "Admin.User1" using password "Admin.Pass1"
        Then I should see "Admin User1"
    
    Scenario: Logging in using an invalid password
        Given I am logged in as "Admin.User1" using password "Hello"
        Then I should not see "Admin User1"
        And I should see "Incorrect password entered. Please try again."
    
    Scenario: Logging in as an invalid user
        Given I am logged in as "Fake.User" using password "wrong"
        Then I should not see "Fake User"
        And I should see "There is no user by the name of Fake.User"
