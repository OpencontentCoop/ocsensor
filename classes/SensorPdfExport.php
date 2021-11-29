<?php

use Opencontent\Sensor\Api\Repository;
use Opencontent\Sensor\Api\Values\Post;

class SensorPdfExport
{
    private $post;
    private $repository;

    private function __construct(OpenPaSensorRepository $repository, Post $post)
    {
        $this->post = $post;
        $this->repository = $repository;
    }

    public static function instance(OpenPaSensorRepository $repository, $postId)
    {
        $post = $repository->getPostService()->loadPost($postId);
        return new SensorPdfExport($repository, $post);
    }

    private function replaceBracket($string)
    {
        $string = str_replace('[', '<b>', $string);
        $string = str_replace(']', '</b>', $string);
        return $string;
    }

    public function generate()
    {
        $filename = $this->post->id . '.pdf';
        $postObject = eZContentObject::fetch($this->post->id);
        $postObjectDataMap = $postObject->dataMap();

        $tpl = eZTemplate::factory();

        $tpl->setVariable('title', $this->replaceBracket($this->repository->getRootNodeAttribute('logo_title')->attribute('data_text')));
        $tpl->setVariable('subtitle', $this->replaceBracket($this->repository->getRootNodeAttribute('logo_subtitle')->attribute('data_text')));
        $logoAlias = 'logo_jpg';
        $tpl->setVariable('logo_image_alias', $logoAlias);
        $logo = $this->repository->getRootNodeAttribute('logo');
        if ($logo instanceof eZContentObjectAttribute && $logo->hasContent()) {
            /** @var eZImageAliasHandler $content */
            $content = $logo->content();
            $alias = $content->attribute($logoAlias);
            $file = eZClusterFileHandler::instance($alias['url']);
            $file->fetch();
            $tpl->setVariable('logo', $logo);
        }else{
            $tpl->setVariable('logo', false);
        }

        $attributes = [
            'Data di pubblicazione' => [$this->post->published->format('d/m/Y H:i')],
            'Tipologia' => [$this->post->type->label],
            'Oggetto della segnalazione' => [$this->post->subject],
            'Dettagli della segnalazione' =>  [preg_replace('/\s+/', ' ', $this->post->description)],
            'Collocazione geografica' => $this->post->geoLocation instanceof Post\Field\GeoLocation ?
                [$this->post->geoLocation->address, '(lat: ' . $this->post->geoLocation->latitude . ', lng: ' . $this->post->geoLocation->longitude . ')'] : [],
        ];

        $images = [];
        if (isset($postObjectDataMap['images']) && $postObjectDataMap['images']->hasContent()){
            /** @var eZMultiBinaryFile[] $files */
            $files = $postObjectDataMap['images']->content();
            foreach ($files as $file) {
                $clusterFile = eZClusterFileHandler::instance($file->filePath());
                $clusterFile->fetch();
                $manager = eZImageManager::instance();
                $targetPath = $file->filePath();
                $parts = explode('.', $targetPath);
                $targetPathSuffix = array_pop( $parts );
                $targetPath = implode('.', $parts) . '_small_jpg.' . $targetPathSuffix;
                $manager->convert($file->filePath(),$targetPath, 'small_jpg');
                if (is_array($targetPath) && isset($targetPath['url'])) {
                    $targetFilePath = $targetPath['url'];
                    $clusterFile = eZClusterFileHandler::instance($targetFilePath);
                    if ($clusterFile->exists()) {
                        $clusterFile->fetch();
                        $info = getimagesize($targetFilePath);
                        $images[] = [
                            'src' => $targetPath['url'],
                            'width' => $info ? $info[0] : 50,
                            'height' => $info ? $info[1] : 50,
                            'border' => 0,
                            'align' => 'left',
                        ];
                    }
                }
            }
        }

        $comments = [];
        foreach ($this->post->comments->messages as $message){
            $comments[] = [
                'Autore' => $message->creator->name,
                'Data' => $message->published->format('d/m/Y H:i'),
                'Testo' => preg_replace('/\s+/', ' ', $message->text)
            ];
        }

        $notes = [];
        foreach ($this->post->privateMessages->messages as $message){
            $notes[] = [
                'Autore' => $message->creator->name,
                'Data' => $message->published->format('d/m/Y H:i'),
                'Testo' => preg_replace('/\s+/', ' ', $message->text)
            ];
        }

        $tpl->setVariable('post_id', $this->post->id);
        $tpl->setVariable('attributes', $attributes);
        $tpl->setVariable('images', $images);
        $tpl->setVariable('comments', $comments);
        $tpl->setVariable('notes', $notes);
        $tpl->setVariable('generate_stream', 1);
        $tpl->setVariable('generate_file', 0);
        $tpl->setVariable('filename', $filename);

        $textElements = [];
        $uri = 'design:sensor/post.pdf.tpl';
        eZTemplateIncludeFunction::handleInclude($textElements, $uri, $tpl, '', '');
        $pdf_definition = implode('', $textElements);
        $pdf_definition = str_replace([' ',
            "\r\n",
            "\t",
            "\n"],
            '',
            $pdf_definition);

        $tpl->setVariable('pdf_definition', $pdf_definition);
        $tpl->setVariable('filename', $filename);
        $uri = 'design:execute_pdf.tpl';
        $textElements = [];

        header("Content-Disposition: attachment; filename={$filename}");
        eZTemplateIncludeFunction::handleInclude($textElements, $uri, $tpl, '', '');
    }
}
