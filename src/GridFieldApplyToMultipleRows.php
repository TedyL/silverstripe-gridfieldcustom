<?php

namespace Tedy\GridFieldCustom;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use Tedy\GridFieldCustom\GridFieldCheckboxSelectComponent;
use SilverStripe\Control\HTTPRequest;

/**
 *
 *
 * @author Tedy Lim <tedyjd@gmail.com>
 * @date 06.01.2019
 * @package GridFieldCustom
 */
class GridFieldApplyToMultipleRows implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{
    /** @var callable - this will be called for every row */
    protected $rowHandler;

    /** @var string */
    protected $targetFragment;

    /** @var string */
    protected $buttonText;

    /** @var string */
    protected $actionName;

    /** @var array */
    protected $buttonConfig;


    /**
     * @param string $actionName
     * @param string $text
     * @param callable $rowHandler
     * @param string $targetFragment
     * @param array $buttonConfig - icon, class, possibly others
     */
    public function __construct($actionName, $text, $rowHandler, $targetFragment = 'after', $buttonConfig = array())
    {
        $this->actionName = $actionName;
        $this->buttonText = $text;
        $this->rowHandler = $rowHandler;
        $this->targetFragment = $targetFragment;
        $this->buttonConfig = $buttonConfig;
    }


    /**
     * {@inheritdoc}
     */
    public function getHTMLFragments($gridField)
    {
        $button = new GridField_FormAction($gridField, $this->actionName, $this->buttonText, $this->actionName, null);
        $button->addExtraClass('multiselect-button');

        if (!empty($this->buttonConfig['icon'])) {
            $button->setAttribute('data-icon', $this->buttonConfig['icon']);
        }

        if (!empty($this->buttonConfig['class'])) {
            $button->addExtraClass($this->buttonConfig['class']);
        }

        if (!empty($this->buttonConfig['confirm'])) {
            $button->setAttribute('data-confirm', $this->buttonConfig['confirm']);
        }

        return array(
            $this->targetFragment => $button->Field(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getActions($gridField)
    {
        return array($this->actionName);
    }

    /**
     * {@inheritdoc}
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName === $this->actionName) {
            return $this->handleIt($gridField, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getURLHandlers($gridField)
    {
        return array(
            $this->actionName => 'handleIt'
        );
    }


    /**
     * @param GridField $gridField
     * @param array|SS_HTTPRequest $data
     * @return array
     */
    public function handleIt($gridField, $data = array())
    {
        if ($data instanceof HTTPRequest) {
            $data = $data->requestVars();
        }

        // Separate out the ID list from the checkboxes
        $fieldName = GridFieldCheckboxSelectComponent::CHECKBOX_COLUMN;
        $ids = isset($data[$fieldName]) && is_array($data[$fieldName]) ? $data[$fieldName] : array();
        $class = $gridField->getModelClass();
        if (!$class) {
            user_error('No model class is defined!');
        }

        $response = array();

        // Hook for subclasses
        $this->onBeforeList($gridField, $data, $ids);

        if (empty($ids)) {
            $this->onEmptyList($gridField, $response, $data, $ids);
            $records = new ArrayList();
        } else {
            $records = DataObject::get($class)->filter('ID', $ids);
            foreach ($records as $index => $record) {
                call_user_func($this->rowHandler, $record, $index);
            }
        }

        $this->onAfterList($gridField, $response, $records, $data, $ids);
        return $response;
    }


    /**
     * Hook for subclasses
     * @param GridField $gridField
     * @param array $data
     * @param array $idList
     */
    protected function onBeforeList($gridField, $data, $idList)
    {
    }


    /**
     * This allows subclasses to have a hook at the end of running through
     * all the items. Response will usually be an array on the way in
     * but it can be changed to whatever and will be returned as is.
     * @param GridField $gridField
     * @param array|SS_HTTPResponse $response
     * @param SS_List $records
     * @param array $data
     * @param array $idList
     */
    protected function onAfterList($gridField, &$response, $records, $data, $idList)
    {
    }


    /**
     * @param GridField $gridField
     * @param array|SS_HTTPResponse $response
     * @param array $data
     * @param array $idList
     */
    protected function onEmptyList($gridField, &$response, $data, $idList)
    {
    }
}
