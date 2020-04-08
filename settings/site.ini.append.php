<?php /*

[RegionalSettings]
TranslationExtensions[]=ocsensor

[TemplateSettings]
ExtensionAutoloadPath[]=ocsensor

[RoleSettings]
PolicyOmitList[]=sensor/use
PolicyOmitList[]=sensor/avatar

[Event]
Listeners[]=content/cache@SensorModuleFunctions::onClearObjectCache
Listeners[]=social_user/signup@SensorNotificationHelper::onSocialUserSignup

[Cache]
CacheItems[]=sensor

[Cache_sensor]
name=Sensor cache
id=sensor
tags[]=content
path=sensor
isClustered=true
class=OpenPaSensorRepository


*/ ?>
