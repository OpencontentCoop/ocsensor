<?php

class SensorSearchableTimeline extends OCCustomSearchableObjectAbstract
{
    public function getGuid()
    {
        return 'timeline-' . $this->attributes['timeline_id'] . '-' . $this->attributes['post_id'];
    }

    public function getFieldValue(OCCustomSearchableFieldInterface $field)
    {
        if ($field->getType() === 'date') {
            $date = $this->attributes[$field->getName()];
            if ($date) {
                $dateTime = DateTime::createFromFormat(SensorTimelinePersistentObject::DATE_FORMAT, $date);
                if ($dateTime instanceof DateTime) {
                    return ezfSolrDocumentFieldBase::convertTimestampToDate($dateTime->format('U'));
                }
            }
        }
        return parent::getFieldValue($field);
    }


    public static function getFields()
    {
        return [
            OCCustomSearchableField::create('timeline_id', 'int'),
            OCCustomSearchableField::create('post_id', 'int'),
            OCCustomSearchableField::create('executor_id', 'int'),
            OCCustomSearchableField::create('group_id_in_charge', 'int'),
            OCCustomSearchableField::create('target_operator_id', 'int'),
            OCCustomSearchableField::create('target_group_id', 'int'),
            OCCustomSearchableField::create('action', 'string'),
            OCCustomSearchableField::create('status_at_end', 'string'),
            OCCustomSearchableField::create('start_at', 'date'),
            OCCustomSearchableField::create('end_at', 'date'),
            OCCustomSearchableField::create('duration', 'int'),
            OCCustomSearchableField::create('post_parent_category_id', 'int'),
            OCCustomSearchableField::create('post_child_category_id', 'int'),
            OCCustomSearchableField::create('post_area_id', 'int'),
            OCCustomSearchableField::create('post_author_id', 'int'),
            OCCustomSearchableField::create('post_author_group_id', 'int'),
            OCCustomSearchableField::create('post_status', 'string'),
            OCCustomSearchableField::create('post_type', 'string'),
        ];
    }
}