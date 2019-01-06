<?php

namespace Tedy\GridFieldCustom;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;
/**
 *
 * @author Tedy Lim<tedyjd@gmail.com>
 * @date 06.01.2019
 * @package apluswhs.com
 * @subpackage
 */
class GridFieldDeleteAllButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{
    protected $targetFragment;
    protected $someCustomConstructData;

    //TargetFragment is just for positioning control of the HTML fragment
    //SomeCustomConstructData is just an example of providing some default options into your butotn
    public function __construct($targetFragment = "after", $someCustomConstructData = null, $buttonConfig = array())
    {
        $this->targetFragment = $targetFragment;
        $this->someCustomConstructData = $someCustomConstructData;
        $this->buttonConfig = array(
          'icon'    => 'delete',
          'class'   => 'btn btn-danger mt-2 btn-outline font-icon-trash-bin btn--icon-large',
          'confirm' => _t('GridFieldMultiDeleteButton.Confirm', 'Are you sure you want to delete all items?'),
    		);
        Requirements::javascript('tedy/gridfieldcustom:javascript/GridFieldDeleteAllButton.js');
    }

    /**
     * {@inheritdoc}
     */
    public function getHTMLFragments($gridField)
    {
        $button = new GridField_FormAction(
            $gridField,
            'deleteall',
            'Delete All',
            'mycustomaction',
            null
        );
        $button->addExtraClass('multiselect-button-delete-all');
        if (!empty($this->buttonConfig['icon']))
        {
            $button->setAttribute('data-icon', $this->buttonConfig['icon']);
        }
        if (!empty($this->buttonConfig['class']))
        {
            $button->addExtraClass($this->buttonConfig['class']);
        }
        if (!empty($this->buttonConfig['confirm'])) {
            $button->setAttribute('data-confirm', $this->buttonConfig['confirm']);
        }
        return array(
            //Note: "grid-print-button" is used here to match the styling of the buttons in ModelAdmin
            $this->targetFragment => $button->Field(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getActions($gridField)
    {
        return array('mycustomaction');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if($actionName == 'mycustomaction') {
            return $this->handleMyCustomAction($gridField, $data);
        }
    }

    //For accessing the custom action from the URL
    public function getURLHandlers($gridField) {
        return array(
            'myCustomAction' => 'handleMyCustomAction',
        );
    }

    //Handle the custom action, for both the action button and the URL
    public function handleMyCustomAction($gridField, $data = null)
    {
        //Do your stuff here!
        if ($data instanceof SS_HTTPRequest)
        {
            $data = $data->requestVars();
        }
        $ids = array();
        $class = $gridField->getModelClass();
        if (!$class) user_error('No model class is defined!');
        $response = array();
        $this->onBeforeList($gridField, $data, $ids);
        $records = DataObject::get($class);
        foreach ($records as $index => $record)
        {
            if ($record->hasExtension('Versioned'))
            {
                $record->deleteFromStage('Stage');
                $record->deleteFromStage('Live');
            }
            else
            {
                $record->delete();
            }
        }
        //$this->onAfterList($gridField, $response, $records, $data, $ids);
        return $response;
    }

    protected function onBeforeList($gridField, $data, $idList) {}
    protected function onEmptyList($gridField, &$response, $data, $idList) {}

    protected function onAfterList($gridField, &$response, $records, $data, $idList) {}
}
