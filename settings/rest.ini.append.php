<?php /* #?ini charset="utf-8"?

[ApiProvider]
ProviderClass[sensor_gui]=SensorGuiApiProvider
ProviderClass[sensor]=SensorOpenApiProvider

[SensorGuiApiController_CacheSettings]
ApplicationCache=disabled

[RouteSettings]
SkipFilter[]=SensorGuiApiController_endpoint
SkipFilter[]=SensorOpenApiProvider_endpoint
SkipFilter[]=SensorOpenApiProvider_auth

[SensorApiCompatController_CacheSettings]
ApplicationCache=disabled

[SensorGuiApiController_CacheSettings]
ApplicationCache=disabled

[SensorOpenApiController_CacheSettings]
ApplicationCache=disabled

[Authentication]
RequireAuthentication=enabled
AuthenticationStyle=SensorApiBasicAuthStyle
DefaultUserID=


*/ ?>
