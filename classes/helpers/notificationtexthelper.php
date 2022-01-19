<?php

class SensorNotificationTextHelper
{
    const SITEDATA_NAME = 'Sensor.NotificationTexts';

    private static $templates;

    private static $defaultTemplates;

    public static function storeTexts($newData)
    {
        $data = self::getSiteData();
        $data->setAttribute('value', json_encode($newData));
        $data->store();
        self::$templates = null;
    }

    private static function getSiteData()
    {
        $data = eZSiteData::fetchByName(self::SITEDATA_NAME);
        if (!$data instanceof eZSiteData) {
            $data = new eZSiteData([
                'name' => self::SITEDATA_NAME,
                'value' => json_encode(self::getDefaultTexts()),
            ]);
            $data->store();
        }

        return $data;
    }

    public static function getDefaultTexts()
    {
        return [
            'on_create' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "Your issue has been registered",
                        'ger-DE' => "",
                        'ita-IT' => "La tua segnalazione è stata registrata",
                    ],
                    'text' => [
                        'eng-GB' => "Your issue will be dealt with by an operator as soon as possible",
                        'ger-DE' => "Deine Meldung wurde registriert",
                        'ita-IT' => "La tua segnalazione sarà presa in carico da un operatore al più presto",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "New issue has been registered",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "New issue has been registered",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "New issue has been registered",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_assign' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "Your issue has been taken in charge",
                        'ger-DE' => "Deine Meldung wurde angenommen",
                        'ita-IT' => "La tua segnalazione è stata presa in carico",
                    ],
                    'header' => [
                        'eng-GB' => "Your issue has been taken in charge",
                        'ger-DE' => "Deine Meldung wurde angenommen",
                        'ita-IT' => "La tua segnalazione è stata presa in carico",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "The issue has been assigned",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been assigned to an operator",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un operatore",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "You have been assigned a issue",
                        'ger-DE' => "Es wurde dir eine Meldung zugewiesen",
                        'ita-IT' => "Ti è stata assegnata una segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "You have been assigned a issue",
                        'ger-DE' => "Es wurde dir eine Meldung zugewiesen",
                        'ita-IT' => "Ti è stata assegnata una segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "You can decide whether to resolve the issue or assign it to another operator",
                        'ger-DE' => "Meldung bearbeiten oder einem anderen Betreiber zuweisen",
                        'ita-IT' => "Puoi decidere se risolvere la segnalazione o assegnarla a un altro operatore",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "The issue has been assigned",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been assigned to an operator",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un operatore",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_add_observer' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "Observer added",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ],
                    'header' => [
                        'eng-GB' => "An observer was added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "Observer added",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ],
                    'header' => [
                        'eng-GB' => "An observer was added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "Observer added",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ],
                    'header' => [
                        'eng-GB' => "An observer was added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "Observer added",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiunto osservatore",
                    ],
                    'header' => [
                        'eng-GB' => "An observer was added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un osservatore è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_add_approver' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo riferimento per il cittadino",
                    ],
                    'header' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo riferimento per il cittadino è stata assegnato alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo riferimento per il cittadino",
                    ],
                    'header' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo riferimento per il cittadino è stata assegnato alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo riferimento per il cittadino",
                    ],
                    'header' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo riferimento per il cittadino è stata assegnato alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo riferimento per il cittadino",
                    ],
                    'header' => [
                        'eng-GB' => "New reference for the citizen",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo riferimento per il cittadino è stata assegnato alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_add_comment' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "New comment",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "New comment",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "New comment",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "New comment",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_fix' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "Your issue has been fixed by the operator",
                        'ger-DE' => "Deine Meldung wurde angenommen",
                        'ita-IT' => "La tua segnalazione è stata chiusa dall'operatore",
                    ],
                    'header' => [
                        'eng-GB' => "Your issue has been fixed by the operator",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La tua segnalazione è stata chiusa dall'operatore",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è tornata in carico al responsabile",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "Issue fixed by the operator",
                        'ger-DE' => "Meldungsstatus vom Betreiber abgeschlossen",
                        'ita-IT' => "Segnalazione chiusa da operatore",
                    ],
                    'header' => [
                        'eng-GB' => "Issue fixed by the operator",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La segnalazione è stata chiusa dall'operatore",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "Puoi decidere se chiudere la segnalazione o assegnarla a un altro operatore",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "Issue fixed by the operator",
                        'ger-DE' => "Meldungsstatus vom Betreiber abgeschlossen",
                        'ita-IT' => "Segnalazione chiusa da operatore",
                    ],
                    'header' => [
                        'eng-GB' => "Issue fixed by the operator",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La segnalazione è stata chiusa dall'operatore",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "Issue fixed by the operator",
                        'ger-DE' => "Meldungsstatus vom Betreiber abgeschlossen",
                        'ita-IT' => "Segnalazione chiusa da operatore",
                    ],
                    'header' => [
                        'eng-GB' => "Issue fixed by the operator",
                        'ger-DE' => "Meldungsstatus abgeschlossen durch Betreiber",
                        'ita-IT' => "La segnalazione è stata chiusa dall'operatore",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_close' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "Your issue has been closed",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La tua segnalazione è stata risolta",
                    ],
                    'header' => [
                        'eng-GB' => "Your issue has been closed",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La tua segnalazione è stata risolta",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "Issue closed",
                        'ger-DE' => "",
                        'ita-IT' => "Segnalazione risolta",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been closed",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La segnalazione è stata risolta",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "Issue closed",
                        'ger-DE' => "Meldung erledigt",
                        'ita-IT' => "Segnalazione risolta",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been closed",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La segnalazione è stata risolta",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "Issue closed",
                        'ger-DE' => "Meldung erledigt",
                        'ita-IT' => "Segnalazione risolta",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been closed",
                        'ger-DE' => "Ihre Empfehlung wurde geschlossen",
                        'ita-IT' => "La segnalazione è stata risolta",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_send_private_message' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "New private message",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo messaggio privato",
                    ],
                    'header' => [
                        'eng-GB' => "You have received a private message",
                        'ger-DE' => "",
                        'ita-IT' => "Hai ricevuto un messaggio privato",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "New private message",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo messaggio privato",
                    ],
                    'header' => [
                        'eng-GB' => "You have received a private message",
                        'ger-DE' => "",
                        'ita-IT' => "Hai ricevuto un messaggio privato",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "New private message",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo messaggio privato",
                    ],
                    'header' => [
                        'eng-GB' => "You have received a private message",
                        'ger-DE' => "",
                        'ita-IT' => "Hai ricevuto un messaggio privato",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "New private message",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo messaggio privato",
                    ],
                    'header' => [
                        'eng-GB' => "You have received a private message",
                        'ger-DE' => "",
                        'ita-IT' => "Hai ricevuto un messaggio privato",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'reminder' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "Latest news",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiornamento periodico",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "Latest news",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiornamento periodico",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "Latest news",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiornamento periodico",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "Latest news",
                        'ger-DE' => "",
                        'ita-IT' => "Aggiornamento periodico",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_group_assign' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "Your issue has been assigned to a operator group",
                        'ger-DE' => "",
                        'ita-IT' => "La tua segnalazione è stata assegnata a un gruppo di lavoro",
                    ],
                    'header' => [
                        'eng-GB' => "Your issue has been assigned to a operator group",
                        'ger-DE' => "",
                        'ita-IT' => "La tua segnalazione è stata assegnata a un gruppo di lavoro",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "The issue has been assigned to a operator group",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un gruppo",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been assigned to a operator group",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un gruppo",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "The issue has been assigned to your group",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata al tuo gruppo",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been assigned to your group",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata al tuo gruppo",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "Puoi prendere in carico la segnalazione",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "The issue has been assigned to a operator group",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un gruppo",
                    ],
                    'header' => [
                        'eng-GB' => "The issue has been assigned to a operator group",
                        'ger-DE' => "",
                        'ita-IT' => "La segnalazione è stata assegnata a un gruppo",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_add_comment_to_moderate' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "New comment to moderate",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento da moderare",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment to moderate has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento da moderare è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "New comment to moderate",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento da moderare",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment to moderate has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento da moderare è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "New comment to moderate",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento da moderare",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment to moderate has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento da moderare è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "New comment to moderate",
                        'ger-DE' => "",
                        'ita-IT' => "Nuovo commento da moderare",
                    ],
                    'header' => [
                        'eng-GB' => "A new comment to moderate has been added to the issue",
                        'ger-DE' => "",
                        'ita-IT' => "Un nuovo commento da moderare è stata aggiunto alla segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
            'on_reopen' => [
                'role_' . eZCollaborationItemParticipantLink::ROLE_AUTHOR => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "La tua segnalazione è stata registrata",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "Deine Meldung wurde registriert",
                        'ita-IT' => "La tua segnalazione sarà presa in carico da un operatore al più presto",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_APPROVER => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OWNER => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
                'role_' . eZCollaborationItemParticipantLink::ROLE_OBSERVER => [
                    'title' => [
                        'eng-GB' => "New issue",
                        'ger-DE' => "Neue Meldung",
                        'ita-IT' => "Nuova segnalazione",
                    ],
                    'header' => [
                        'eng-GB' => "",
                        'ger-DE' => "neue Meldung registriert",
                        'ita-IT' => "E' stata registrata una nuova segnalazione",
                    ],
                    'text' => [
                        'eng-GB' => "",
                        'ger-DE' => "",
                        'ita-IT' => "",
                    ],
                ],
            ],
        ];
    }

    public static function reset()
    {
        $data = self::getSiteData();
        $data->setAttribute('value', json_encode(self::getDefaultTexts()));
        $data->store();
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

    public static function getTexts()
    {
        $data = self::getSiteData();

        return json_decode($data->attribute('value'), 1);
    }

    public static function getDefaultTemplates()
    {
        if (self::$defaultTemplates === null) {
            $texts = self::getDefaultTexts();
            self::$templates = [];
            foreach ($texts as $event => $text) {
                foreach ($text as $roleString => $values) {
                    $roleId = str_replace('role_', '', $roleString);
                    foreach ($values as $identifier => $languages) {
                        foreach ($languages as $language => $value) {
                            self::$defaultTemplates[$event][$roleId][$language][$identifier] = $value;
                        }
                    }
                }
            }
        }

        return self::$defaultTemplates;
    }
}
