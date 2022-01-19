<?php

class SensorReportItemClassConnector extends SensorReportClassConnector
{
    public function submit()
    {
        $submitData = $this->getSubmitData();

        $avoidOverride = isset($submitData['avoid_override']) && $submitData['avoid_override'] === 'true';
        $avoidOverrideFieldsString = isset($submitData['avoid_override_fields']) ? $submitData['avoid_override_fields'] : '';
        $avoidOverrideFields = explode(',', $avoidOverrideFieldsString);
        $avoidOverrideFields = array_map('trim', $avoidOverrideFields);
        if (isset($submitData['link']) && !$avoidOverride){
            $submitData['link'] = $this->applyOverride($submitData['link'], $avoidOverrideFields);
        }

        $payload = $this->getPayloadFromArray($submitData);

        return $this->doSubmit($payload);
    }

    private function applyOverride($link, $avoidOverrideFields = [])
    {
        $parentNodeId = false;
        if ($this->getHelper()->hasParameter('object')){
            $object = eZContentObject::fetch((int)$this->getHelper()->getParameter('object'));
            if ($object instanceof eZContentObject){
                $parentNodeId = $object->attribute('main_parent_node_id');
            }
        }elseif ($this->getHelper()->hasParameter('parent')){
            $parentNodeId = (int)$this->getHelper()->getParameter('parent');
        }
        if ($parentNodeId){
            $parentNode = eZContentObjectTreeNode::fetch((int)$parentNodeId);
            if ($parentNode instanceof eZContentObjectTreeNode) {
                $parentContent = \Opencontent\Opendata\Api\Values\Content::createFromEzContentObject($parentNode->object());
                $env = new SensorDefaultEnvironmentSettings();
                $content = $env->filterContent($parentContent);
                if (isset($content['data'][$this->getHelper()->getSetting('language')]['override_link_parameters'])){
                    $overrideParameters = $this->parseOverrideParameters(
                        $content['data'][$this->getHelper()->getSetting('language')]['override_link_parameters']
                    );
                    $link = $this->overrideLink($link, $overrideParameters, $avoidOverrideFields);
                }
            }
        }

        return $link;
    }
}
