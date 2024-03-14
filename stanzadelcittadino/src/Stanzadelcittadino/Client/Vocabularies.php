<?php

namespace Opencontent\Stanzadelcittadino\Client;

class Vocabularies
{
    public static function mapType($source): array
    {
        $map = [
            '34a10624-20d8-451e-a805-358aae738e9f' => ['Anagrafe, elettorale e cimiteri'],
            '435dfa72-932b-44f9-87d8-7ef8924fa59f' => ['Animali vaganti', 'Animali'],
            'ded2e0b4-eb4e-4314-ab3e-c742d5642796' => ['Aree di parcheggio'],
            '211464b5-4c73-4626-86c7-b13902b32831' => ['Attività commerciali'],
            'd63b7880-56b5-4dd6-9425-8a6221e940d7' => ['Barriere architettoniche'],
            '8daddf74-adb1-4237-81da-993b3f70b8b1' => ['Cantieri', 'Cantieri, edilizia e urbanistica'],
            '5dc2192d-9dfe-4641-90c1-651a65857cac' => ['Cultura, sport e grandi eventi'],
            '6bc289a9-e945-477b-b427-41e8686e4e30' => ['Degrado urbano'],
            'd07b3c82-2e4e-4f71-8fbe-dd3300d6ec82' => ['Igiene pubblica'],
            '70cbba61-47e4-4d85-98bf-03e4817cf272' => ['Illuminazione pubblica'],
            '71cf4444-9493-4d4e-b8f0-d35fea15ce81' => ['Impianto idrico e fognario'],
            '4e232629-0cb5-4211-b7ac-198294735008' => ['Inquinamento acustico', 'Inquinamento aria/acustico'],
            '97722683-b117-4dc0-b0c8-bd2611de10bd' => ['Manutenzione stradale', 'Manutenzione strade e arredi urbani'],
            'e00c76c5-a590-418d-8725-3a33192ac03c' => ['Raccolta dei rifiuti', 'Rifiuti e pulizia strade'],
            'ae18dfb7-819b-452a-b5e5-9606c6b90df9' => ['Segnaletica stradale', 'Segnaletica stradale'],
            '8052808f-ac9c-4e48-a6eb-d29984bfa0a4' => ['Sito web e servizi digitali'],
            'bf63e178-6e56-4952-99f0-23893857340f' => ['Veicoli abbandonati'],
            'e441060f-0a98-4ac7-abd2-779bf3813440' => ['Verde pubblico'],
            '47ce50c4-886e-448d-868c-7f9d32c83546' => ['Viabilità', 'Mobilità e trasporto pubblico'],
            '8b644663-851b-4b9f-ba0b-7637a67a876b' => ['Oggetti smarriti'],
            'd9b86b48-8d47-4107-b5c4-fc27f3bab46e' => ['Politiche sociali e casa'],
            '880e57ac-e90b-4b8a-9462-085bf64db35a' => ['Rivi, torrenti e spiagge'],
            '53755f5c-7cef-41cc-8d27-c9b9a11383df' => ['Sanzioni'],
            '9882a0f0-a078-45ec-b93d-0cb3a2171fb4' => ['Scuole'],
            'c1a4c634-3a3d-4c85-a541-5e6bae5eb87f' => ['Servizi finanziari e tributi'],
            '4ca0927c-b028-4ef2-bbe0-9d985e93a896' => ['Sicurezza'],
            '54031b71-aaad-4a74-9c8f-2c7fd1250ffd' => ['Turismo'],
        ];

        foreach ($map as $id => $values) {
            if (in_array($source, $values)) {
                return [
                    'label' => $source,
                    'value' => $id,
                ];
            }
        }

        return [];
    }
}