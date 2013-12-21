<?php
/**
 * Frontend {{ moduleName }} {{ action }} action
 */
class Frontend{{ moduleName|capitalize }}{{ action|capitalize }} extends FrontendBaseBlock
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    /**
     * Load the data, don't forget to validate the incoming data
     */
    protected function getData()
    {

    }

    /**
     * Parse the data into the template
     */
    protected function parse()
    {

    }
}