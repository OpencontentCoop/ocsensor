<?php /* #?ini charset="utf-8"?

[ConnectorSettings]
AvailableConnectors[]=remove-operator
AvailableConnectors[]=create-user
AvailableConnectors[]=operator-settings
AvailableConnectors[]=user-settings
AvailableConnectors[]=delete-user-group
AvailableConnectors[]=batch-scenarios
AvailableConnectors[]=duplicate-post
AvailableConnectors[]=import-user
AvailableConnectors[]=create-organization

[remove-operator_ConnectorSettings]
PHPClass=RemoveOperatorConnector

[create-user_ConnectorSettings]
PHPClass=BehalfUserConnector

[operator-settings_ConnectorSettings]
PHPClass=OperatorSettingsConnector

[user-settings_ConnectorSettings]
PHPClass=UserSettingsConnector

[delete-user-group_ConnectorSettings]
PHPClass=DeleteUserGroupConnector

[batch-scenarios_ConnectorSettings]
PHPClass=BatchScenarioConnector

[duplicate-post_ConnectorSettings]
PHPClass=DuplicatePostConnector

[import-user_ConnectorSettings]
PHPClass=UserImportCsvConnector

[create-organization_ConnectorSettings]
PHPClass=BehalfOrganizationConnector
