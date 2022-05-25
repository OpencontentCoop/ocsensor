<?php

use Opencontent\Sensor\Api\Repository;
use Opencontent\Sensor\Api\Values\Post;

class SensorPdfExport
{
    private $post;
    private $repository;
    private $translator;

    private function __construct(OpenPaSensorRepository $repository, Post $post)
    {
        $this->post = $post;
        $this->repository = $repository;
        $this->translator = SensorTranslationHelper::instance();
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
            $this->translator->translate('Creation date') => [$this->post->published->format('d/m/Y H:i')],
            $this->translator->translate('Type') => [$this->repository->getPostTypeService()->loadPostType($this->post->type->identifier)->name],
            $this->translator->translate('Visibility') => $this->post->privacy->identifier === 'public' && $this->post->moderation->identifier !== 'waiting' ?
                [$this->translator->translate('public', 'privacy')] :
                [$this->translator->translate('private', 'privacy')],
            $this->translator->translate('Object of issue') => [$this->post->subject],
            $this->translator->translate('Description of issue') =>  [preg_replace('/\s+/', ' ', $this->post->description)],
            $this->translator->translate('Location info') => $this->post->geoLocation instanceof Post\Field\GeoLocation && $this->post->geoLocation->latitude != 0 ?
                [$this->post->geoLocation->address, '(lat: ' . $this->post->geoLocation->latitude . ', lng: ' . $this->post->geoLocation->longitude . ')'] : [],
        ];
        if (count($this->post->categories)){
            $attributes[$this->translator->translate('Category')] = [$this->post->categories[0]->name];
        }
        if (count($this->post->areas)){
            $attributes[$this->translator->translate('Area')] = [$this->post->areas[0]->name];
        }

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

        $files = [];
        foreach ($this->post->files as $file){
            $files[] = [
                'Name' => basename($file->downloadUrl),
                'Url' => $file->downloadUrl,
                'Size' => $this->formatBytes($file->size),
                'Mime' => $file->mimeType,
            ];
        }

        $attachments = [];
        foreach ($this->post->attachments as $file){
            $attachments[] = [
                'Name' => basename($file->downloadUrl),
                'Url' => $file->downloadUrl,
                'Size' => $this->formatBytes($file->size),
                'Mime' => $file->mimeType,
            ];
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

        $responses = [];
        foreach ($this->post->responses->messages as $message){
            $responses[] = [
                'Autore' => $message->creator->name,
                'Data' => $message->published->format('d/m/Y H:i'),
                'Testo' => preg_replace('/\s+/', ' ', $message->text)
            ];
        }

        $timelines = [];
        foreach ($this->post->timelineItems->messages as $message){
            $timelines[] = [
                'Autore' => $message->creator->name,
                'Data' => $message->published->format('d/m/Y H:i'),
                'Testo' => preg_replace('/\s+/', ' ', $message->text)
            ];
        }

        $tpl->setVariable('post_id', $this->post->id);
        $tpl->setVariable('attributes', $attributes);
        $tpl->setVariable('images', $images);
        $tpl->setVariable('files', $files);
        $tpl->setVariable('attachments', $attachments);
        $tpl->setVariable('comments', $comments);
        $tpl->setVariable('notes', $notes);
        $tpl->setVariable('responses', $responses);
        $tpl->setVariable('timelines', $timelines);
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

    private function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }
}
