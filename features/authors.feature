Feature: Approve License Switch
    As a project contributor
    To give feedback about a license switch
    I want to approve or unapprove a license change

    Scenario: Approve License Switch
        When project "https://github.com/beberlei/assert.git" wants to switch license
        Then the following users approve licenses:
            | ID | Email               |
            | 1  | kontakt@beberlei.de |
        Then the project "beberlei/assert" should have an author approve ratio bigger than "5"%
