<p>
    <strong>ID:</strong> <a href="https://{$social_pagedata.site_url}/sensor/posts/{$object.id}">{$object.id}</a>
</p>
<p>
    <strong>{$object.data_map.subject.contentclass_attribute_name}:</strong> {$object.name|wash()}
</p>
<p>
    <strong>{$object.data_map.type.contentclass_attribute_name}:</strong> {attribute_view_gui attribute= $object.data_map.type}
</p>
{if  $object|has_attribute('geo')}
    <p>
        <strong>{$object.data_map.geo.contentclass_attribute_name}:</strong> {$object.data_map.geo.content.address}
    </p>
{elseif  $object|has_attribute('area')}
    <p>
        <strong>{$object.data_map.area.contentclass_attribute_name}:</strong> {attribute_view_gui attribute= $object.data_map.area}
    </p>
{/if}
<p>
    <strong>{$object.data_map.description.contentclass_attribute_name}:</strong> <small>{attribute_view_gui attribute= $object.data_map.description}</small>
</p>
{if  $object|has_attribute('attachment')}
    <p>
        <strong>{$object.data_map.attachment.contentclass_attribute_name}:</strong> {$object.data_map.attachment.content.original_filename|wash()}
    </p>
{/if}
