<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector;

class SensorReportClassConnector extends ClassConnector
{
    public function submit()
    {
        $submitData = $this->getSubmitData();
        $payload = $this->getPayloadFromArray($submitData);
        $result = $this->doSubmit($payload);

        if (isset($submitData['override_link_parameters'])) {
            $this->overrideChildrenLink(
                (int)$result['content']['metadata']['id'],
                $this->parseOverrideParameters((array)$submitData['override_link_parameters'])
            );
        }

        return $result;
    }

    private function overrideChildrenLink($id, array $overrideParameters)
    {
        $object = eZContentObject::fetch($id);
        if ($object instanceof eZContentObject) {
            $node = $object->mainNode();
            if ($node instanceof eZContentObjectTreeNode) {
                $children = $node->subTree([
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'Limitation' => []
                ]);
                /** @var eZContentObjectTreeNode $child */
                foreach ($children as $child) {
                    $this->overrideReportItemLink($child->object(), $overrideParameters);
                }
            }
        }
    }

    private function overrideReportItemLink(eZContentObject $object, array $overrideParameters)
    {
        if ($object->attribute('class_identifier') == 'sensor_report_item') {
            $dataMap = $object->dataMap();
            if (isset($dataMap['avoid_override'])
                && (int)$dataMap['avoid_override']->attribute('data_int') === 0
                && isset($dataMap['link'])
                && $dataMap['link']->hasContent()) {
                $link = $dataMap['link']->toString();
                $avoidOverrideFieldsString = isset($dataMap['avoid_override_fields']) ? $dataMap['avoid_override_fields']->toString() : '';
                $avoidOverrideFields = explode(',', $avoidOverrideFieldsString);
                $avoidOverrideFields = array_map('trim', $avoidOverrideFields);
                $newLink = $this->overrideLink($link, $overrideParameters, $avoidOverrideFields);
                if ($link !== $newLink) {
                    $dataMap['link']->fromString($newLink);
                    $dataMap['link']->store();
                    eZSearch::addObject($object, true);
                }
            }
        }
    }

    protected function overrideLink($link, array $overrideParameters, array $avoidOverrideFields)
    {
        $link = parse_url($link);
        if (isset($link['query'])) {
            $parameters = $this->httpParseQuery($link['query']);
            foreach ($parameters as $key => $value) {
                $parameters[$key] = isset($overrideParameters[$key]) && !in_array($key, $avoidOverrideFields) ?
                    $overrideParameters[$key] : $value;

            }
            $link['query'] = http_build_query($parameters);
        }

        return $this->buildUrl($link);
    }

    private function httpParseQuery($query)
    {
        $parameters = array();
        $queryParts = explode('&', $query);
        foreach ($queryParts as $queryPart) {
            $keyValue = explode('=', $queryPart, 2);
            $key = $this->cleanString($keyValue[0]);
            $value = rawurldecode($keyValue[1]);
            if (strpos($key, '[]') !== false){
                $arrayKey = str_replace('[]', '', $key);
                if (!isset($parameters[$arrayKey])){
                    $parameters[$arrayKey] = [];
                }
                $parameters[$arrayKey][] = $value;
            }else {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    private function cleanString($string)
    {
        $string = rawurldecode($string);
        if (strpos($string, '%') !== false){
            $parts = explode('%', $string, 2);
            $string = $parts[0] . '[]';
        }

        return $string;
    }

    private function buildUrl(array $parts)
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }

    protected function parseOverrideParameters(array $overrideParameters)
    {
        $data = [];
        foreach ($overrideParameters as $overrideParameter) {
            $data[$overrideParameter['key']] = urldecode($overrideParameter['value']);
        }

        return $data;
    }
}
