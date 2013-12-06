<?php
/**
 * Backend {{ moduleName }} config
 */
class Backend{{ moduleName|capitalize }}Config extends BackendBaseConfig
{
    /**
     * The default action
     *
     * @var    string
     */
    protected $defaultAction = 'index';

    /**
     * The disabled actions
     *
     * @var    array
     */
    protected $disabledActions = array();
}
