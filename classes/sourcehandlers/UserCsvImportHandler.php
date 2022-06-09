<?php

class UserCsvImportHandler extends SQLIImportAbstractHandler implements ISQLIImportHandler
{
    const SENSOR_HANDLER_IDENTIFIER = 'user_csv_import';

    private $done;

    private $errors = [];

    public function initialize()
    {
        /** @var eZUser $user */
        $user = eZUser::fetchByName('admin');
        eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
    }

    public function getProcessLength()
    {
        return 1;
    }

    public function getNextRow()
    {
        if ($this->done){
            return false;
        }
        $this->done = true;
        return true;
    }

    public function process($row)
    {
        try {
            $file = $this->options->attribute('file');
            $parentNode = eZContentObjectTreeNode::fetch($this->options->attribute('parent_node_id'));
            if ($parentNode instanceof eZContentObjectTreeNode) {
                $settings = json_decode($this->options->attribute('settings'), true);
                $importer = new UserCsvImporter($file);
                $importer->validate();
                $importer->import($parentNode, $settings);
            }else{
                throw new Exception('Parent node not found');
            }
        }catch (Exception $e){
            $this->errors[] = $e->getMessage();
        }
    }

    public function cleanup()
    {
        // TODO: Implement cleanup() method.
    }

    public function getHandlerName()
    {
        return 'OpenSegnalazioni User Csv Import';
    }

    public function getHandlerIdentifier()
    {
        return self::SENSOR_HANDLER_IDENTIFIER;
    }

    public function getProgressionNotes()
    {
        $current = '';
        if (count($this->errors)){
            $current .= "<br>Errors:<ul><li>" . implode("</li><li>", $this->errors) . '</li></ul>';
        }

        return $current;
    }

}
