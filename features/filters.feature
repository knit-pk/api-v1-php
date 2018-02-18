Feature:
  In order to get resources that match my criteria
  As a client software developer
  I want to filter resource collections by their attributes

  Scenario: It receives collection of articles that belong to news category
    Given I add Accept header equal to 'application/json'
    When I send a 'GET' request to '/articles?category.code=news&group[]=CategoryRead'
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON collection should not be empty
    And the JSON collection every node 'category.code' should be equal to 'news'
