{let class_content=$attribute.class_content
     class_list=fetch( class, list, hash( class_filter, $class_content.class_constraint_list ) )
     can_create=true()
     new_object_initial_node_placement=false()
     browse_object_start_node=false()}

{default html_class='full' placeholder=false()}

{if $placeholder}
<label>{$placeholder}</label>
{/if}

{default attribute_base=ContentObjectAttribute}

{def $categoryList = sensor_categorycontainer().children}

{if ne( count( $categoryList ), 0)}
<select class="{$html_class}" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]" {*size="10" multiple*}>
    {foreach $categoryList as $category}
      {def $category_children_count = $category.children_count}
      {if $category_children_count|gt(0)}
      <optgroup label="{$category.name|wash}">
      {else}
        <option value="{$category.contentobject_id}"
        {if ne( count( $attribute.content.relation_list ), 0)}
        {foreach $attribute.content.relation_list as $item}
             {if eq( $item.contentobject_id, $category.contentobject_id )}
                selected="selected"
                {break}
             {/if}
        {/foreach}
        {/if}
        >
          {$category.name|wash}
        </option>
      {/if}
      {foreach $category.children as $categoryChild}
          <option value="{$categoryChild.contentobject_id}"
          {if ne( count( $attribute.content.relation_list ), 0)}
          {foreach $attribute.content.relation_list as $item}
               {if eq( $item.contentobject_id, $categoryChild.contentobject_id )}
                  selected="selected"
                  {break}
               {/if}
          {/foreach}
          {/if}
          >
          {$categoryChild.name|wash}
        </option>
      {/foreach}
      {if $category_children_count|gt(0)}
      </optgroup>
      {/if}
      {undef $category_children_count}
    {/foreach}
</select>
{/if}

{/default}

{/default}
{/let}
