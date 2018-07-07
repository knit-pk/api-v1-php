Feature:
    In order to get resources that match my criteria
    As a client software developer
    I want to filter resource collections by their attributes

    Scenario: It receives collection of articles that belong to news category using REST
        Given I add Accept header equal to 'application/json'
        When I send a GET request to '/articles?category.code=news&group[]=CategoryRead'
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON collection should not be empty
        And the JSON items in collection should have node category.code that is equal to news

    Scenario: It receives collection of articles that belong to news category using Hydra
        Given I add Accept header equal to 'application/ld+json'
        When I send a GET request to '/articles?category.code=news&group[]=CategoryRead'
        Then the response status code should be 200
        And the response should be in JSON
        And the header 'Content-Type' should be equal to 'application/ld+json; charset=utf-8'
        And the JSON collection 'hydra:member' should not be empty
        And the JSON items in collection 'hydra:member' should have node 'category.code' that is equal to 'news'

    Scenario: It receives collection of articles that belong to news category using GraphQL
        Given I add Accept header equal to 'application/json'
        When I send a 'GET' request to '/graphql?query={articles(category_code:"news"){edges{node{category{code}}}}}'
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON collection 'data.articles.edges' should not be empty
        And the JSON items in collection 'data.articles.edges' should have node 'node.category.code' that is equal to 'news'

