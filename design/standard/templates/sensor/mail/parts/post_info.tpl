<p>
    <strong>ID:</strong> <a href="http://{$social_pagedata.site_url}/sensor/posts/{$object.id}">{$node.contentobject_id}</a>
</p>
<p>
    <strong>{$node.data_map.subject.contentclass_attribute_name}:</strong> {$node.name|wash()}
</p>
<p>
    <strong>{$node.data_map.type.contentclass_attribute_name}:</strong> {attribute_view_gui attribute=$node.data_map.type}
</p>
{if $node|has_attribute('geo')}
    <p>
        <strong>{$node.data_map.geo.contentclass_attribute_name}:</strong> {$node.data_map.geo.content.address}
    </p>
{elseif $node|has_attribute('area')}
    <p>
        <strong>{$node.data_map.area.contentclass_attribute_name}:</strong> {attribute_view_gui attribute=$node.data_map.area}
    </p>
{/if}
<p>
    <strong>{$node.data_map.description.contentclass_attribute_name}:</strong> <small>{attribute_view_gui attribute=$node.data_map.description}</small>
</p>
{if $node|has_attribute('attachment')}
    <p>
        <strong>{$node.data_map.attachment.contentclass_attribute_name}:</strong> {$node.data_map.attachment.content.original_filename|wash()}
    </p>
{/if}
