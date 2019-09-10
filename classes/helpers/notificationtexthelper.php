<?php

class SensorNotificationTextHelper
{
    const SITEDATA_NAME = 'Sensor.NotificationTexts';

    private static $templates;

    private static function getSiteData()
    {
        $data = eZSiteData::fetchByName(self::SITEDATA_NAME);
        if (!$data instanceof eZSiteData) {
            $data = new eZSiteData(array(
                'name' => self::SITEDATA_NAME,
                'value' => json_encode(self::getDefaultTexts())
            ));
            $data->store();
        }

        return $data;
    }

    public static function storeTexts($newData)
    {
        $data = self::getSiteData();
        $data->setAttribute('value', json_encode($newData));
        $data->store();
    }

    public static function reset()
    {
        $data = self::getSiteData();
        $data->setAttribute('value', json_encode(self::getDefaultTexts()));
        $data->store();
    }

    public static function getTexts()
    {
        $data = self::getSiteData();

        return json_decode($data->attribute('value'), 1);
    }

    public static function getTemplates()
    {
        if (self::$templates === null) {
            $texts = self::getTexts();
            self::$templates = [];
            foreach ($texts as $event => $text) {
                foreach ($text as $roleString => $values) {
                    $roleId = str_replace('role_', '', $roleString);
                    foreach ($values as $identifier => $languages) {
                        foreach ($languages as $language => $value) {
                            self::$templates[$event][$roleId][$language][$identifier] = $value;
                        }
                    }
                }
            }
        }

        return self::$templates;
    }

    public static function getDefaultTexts()
    {
        return array(
            'on_create' => array(
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La tua segnalazione è stata registrata",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Deine Meldung wurde registriert",
                        'ita-IT' => "La tua segnalazione sarà presa in carico da un operatore al più presto",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
            ),
            'on_assign' => array(
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Deine Meldung wurde angenommen",
                        'ita-IT' => "La tua segnalazione è stata presa in carico",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Deine Meldung wurde angenommen",
                        'ita-IT' => "La tua segnalazione è stata presa in carico",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un operatore",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Es wurde dir eine Meldung zugewiesen",
                        'ita-IT' => "Ti è stata assegnata una segnalazione",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Es wurde dir eine Meldung zugewiesen",
                        'ita-IT' => "Ti è stata assegnata una segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldung bearbeiten oder einem anderen Betreiber zuweisen",
                        'ita-IT' => "Puoi decidere se risolvere la segnalazione o assegnarla a un altro operatore",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un operatore",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
            ),
            'on_add_observer' => array(
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
            ),
            'on_add_comment' => array(
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
            ),
            'on_fix' => array(
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Deine Meldung wurde angenommen",
                        'ita-IT' => "La tua segnalazione è stata chiusa dall'operatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La tua segnalazione è stata chiusa dall'operatore",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è tornata in carico al responsabile",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus vom Betreiber abgeschlossen",
                        'ita-IT' => "Segnalazione chiusa da operatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La segnalazione è stata chiusa dall'operatore",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "Puoi decidere se chiudere la segnalazione o assegnarla a un altro operatore",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus vom Betreiber abgeschlossen",
                        'ita-IT' => "Segnalazione chiusa da operatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La segnalazione è stata chiusa dall'operatore",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus vom Betreiber abgeschlossen",
                        'ita-IT' => "Segnalazione chiusa da operatore",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La segnalazione è stata chiusa dall'operatore",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
            ),
            'on_close' => array(
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La tua segnalazione è stata risolta",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La tua segnalazione è stata risolta",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Segnalazione risolta",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La segnalazione è stata risolta",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldung erledigt",
                        'ita-IT' => "Segnalazione risolta",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La segnalazione è stata risolta",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => array(
                    'title' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Meldung erledigt",
                        'ita-IT' => "Segnalazione risolta",
                    ),
                    'header' => array(
                        'eng-GB' => "",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La segnalazione è stata risolta",
                    ),
                    'text' => array(
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ),
                ),
            ),
        );
    }
}