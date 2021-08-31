<?php /*

[RegionalSettings]
TranslationExtensions[]=ocsensor

[TemplateSettings]
ExtensionAutoloadPath[]=ocsensor

[Event]
Listeners[]=content/cache@SensorModuleFunctions::onClearObjectCache
Listeners[]=social_user/signup@SensorNotificationHelper::onSocialUserSignup
Listeners[]=request/input@SensorModuleFunctions::onRequestInput

[Cache]
CacheItems[]=sensor
CacheItems[]=avatar

[Cache_sensor]
name=Sensor cache
id=sensor
tags[]=content
path=sensor
isClustered=true
class=OpenPaSensorRepository

[Cache_avatar]
name=Avatar cache
id=avatar
path=avatars
isClustered=true
class=SensorAvatar

[UserContextHash]
IncludeCurrentUserId=enabled

[RoleSettings]
PolicyOmitList[]=sensor/report

*/ ?>
