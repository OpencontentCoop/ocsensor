<p>
    <strong>ID:</strong> <a href="https://{$social_pagedata.site_url}/sensor/posts/{$object.id}">{$object.id}</a>
</p>
<p>
    <strong>{sensor_translate('Object of issue')}:</strong> {$object.name|wash()}
</p>
<p>
    <strong>{sensor_translate('Type')}:</strong> {attribute_view_gui attribute= $object.data_map.type}
</p>
{if  $object|has_attribute('geo')}
    <p>
        <strong>{sensor_translate('Location info')}:</strong> {$object.data_map.geo.content.address}
    </p>
{elseif  $object|has_attribute('area')}
    <p>
        <strong>{sensor_translate('Area')}:</strong> {attribute_view_gui attribute= $object.data_map.area}
    </p>
{/if}
<p>
    <strong>{sensor_translate('Description of issue')}:</strong> <small>{attribute_view_gui attribute= $object.data_map.description}</small>
</p>
{if  $object|has_attribute('attachment')}
    <p>
        <strong>{sensor_translate('Attachments')}:</strong> {$object.data_map.attachment.content.original_filename|wash()}
    </p>
{/if}
