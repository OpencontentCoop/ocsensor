<?php

class ezfIndexSensor implements ezfIndexPlugin
{
    /**
     * @param eZContentObject $contentObject
     * @param eZSolrDoc[] $docList
     */
    public function modify( eZContentObject $contentObject, &$docList )
    {
        if ( class_exists( 'OpenPaSensorRepository' ) )
        {
            $repository = OpenPaSensorRepository::instance();

            /** @var eZContentObjectVersion $version */
            $version = $contentObject->currentVersion();
            if ( $version === false )
            {
                return false;
            }

            $collaboration = eZPersistentObject::fetchObject(
                eZCollaborationItem::definition(),
                null,
                array(
                    'data_int1' => intval( $contentObject->attribute('id') )
                )
            );
            if(!$collaboration instanceof eZCollaborationItem){
                return false;
            }

            $availableLanguages = $version->translationList( false, false );
            foreach ( $availableLanguages as $languageCode )
            {
                $repository->setCurrentLanguage( $languageCode );
                try
                {
                    $post = $repository->getPostService()->loadPost( $contentObject->attribute( 'id' ) );
                    foreach ( $repository->getSearchService()->getSolrFields( $post ) as $key => $value )
                    {
                        $this->addField( $docList[$languageCode], $key, $value );
                    }
                }
                catch( Exception $e )
                {

                }
            }
        }
    }

    protected function addField( eZSolrDoc $doc, $fieldName, $fieldValue )
    {
        if ( $doc instanceof eZSolrDoc )
        {
            if ( $doc->Doc instanceof DOMDocument )
            {
                $xpath = new DomXpath( $doc->Doc );
                if ( $xpath->evaluate( '//field[@name="' . $fieldName . '"]' )->length == 0 )
                {
                    $doc->addField( $fieldName, $fieldValue );
                }
            }
            elseif ( is_array( $doc->Doc ) && !isset( $doc->Doc[$fieldName] ) )
            {
                $doc->addField( $fieldName, $fieldValue );
            }
        }
    }
}
