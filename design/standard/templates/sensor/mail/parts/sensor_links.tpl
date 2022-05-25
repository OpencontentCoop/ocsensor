<p>
    {sensor_translate('To see all your issues click %dashboard_link_start% here %dashboard_link_end%', '', hash( '%dashboard_link_start%', concat( '<a href=https://', $social_pagedata.site_url, '/sensor/dashboard/>' ), '%dashboard_link_end%', '</a>' ))}<br />
    {sensor_translate('To disable notifications click %notification_link_start% here %notification_link_end%', '', hash( '%notification_link_start%', concat( '<a href=https://', $social_pagedata.site_url, '/notification/settings/>' ), '%notification_link_end%', '</a>' ))}
</p>
