Feature:
    In order to make use of API
    As a client software developer
    I want to be able to perform CRUD actions

    Scenario: It should be possible to get a collection of tags
        Given I am authenticated
        And I add 'Accept' header equal to 'application/json'
        When I send a 'GET' request to '/tags'
        Then the response status code should be 200
        And the response should be in JSON

    Scenario: It should be possible to create a tag
        Given I am authenticated
        And I add 'Content-Type' header equal to 'application/json'
        And I add 'Accept' header equal to 'application/json'
        When I send a 'POST' request to '/tags' with body:
        """
        {
            "name": "Dummy Tag"
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON node 'name' should be equal to 'Dummy Tag'
        And the JSON node 'id' should be a valid uuid

    Scenario: It should not be possible to create a tag with id
        Given I am authenticated
        And I add 'Content-Type' header equal to 'application/json'
        And I add 'Accept' header equal to 'application/json'
        When I send a 'POST' request to '/tags' with body:
        """
        {
            "id": "dummy-id",
            "name": "Dummy Tag"
        }
        """
        Then the response status code should be 400
        And the JSON node 'title' should be equal to 'An error occurred'
        And the JSON node 'detail' should be equal to 'Update is not allowed for this operation.'
