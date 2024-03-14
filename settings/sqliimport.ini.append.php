<?php /* #?ini charset="utf-8"?

[ImportSettings]
AvailableSourceHandlers[]=sensor_scenario_edit
AvailableSourceHandlers[]=sensor_group_reindex
AvailableSourceHandlers[]=user_csv_import
AvailableSourceHandlers[]=inefficiency_retry
RobotUserID=14

[sensor_scenario_edit-HandlerSettings]
Enabled=true
Name=Batch Edit Scenario
ClassName=SensorBatchScenarioEditHandler
Debug=enabled

[sensor_group_reindex-HandlerSettings]
Enabled=true
Name=Reindex Post Group Participant
ClassName=SensorPostGroupParticipantHandler
Debug=enabled

[user_csv_import-HandlerSettings]
Enabled=true
Name=User Csv Import
ClassName=UserCsvImportHandler
Debug=enabled

[inefficiency_retry-HandlerSettings]
Enabled=true
Name=Batch Inefficiency Retry
ClassName=InefficiencyRetryHandler
Debug=enabled

*/ ?>
