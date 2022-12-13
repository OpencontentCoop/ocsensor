<?php

class NullServiceContentIcon extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['icon'] = false;
        $this->data['object_icon'] = false;
        $this->data['context_icon'] = false;
        $this->data['class_icon'] = false;
    }
}
