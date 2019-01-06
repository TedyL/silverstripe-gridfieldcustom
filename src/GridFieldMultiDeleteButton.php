<?php

namespace Tedy\GridFieldCustom;

/**
 *
 * @author Tedy Lim <tedyjd@gmail.com>
 * @date 06.01.2019
 * @package apluswhs.com
 * @subpackage
 */
class GridFieldMultiDeleteButton extends GridFieldApplyToMultipleRows
{
    /**
     * Shortcut to create a button that deletes all selected entries
     */
    public function __construct($targetFragment = 'after')
    {
        parent::__construct('deleteselected', _t('GridFieldMultiDeleteButton.ButtonText', 'Delete Selected'), array($this, 'deleteRecord'), $targetFragment, array(
            'class'   => 'btn btn-outline-danger mt-2 font-icon-trash-bin btn--icon-large',
            'confirm' => _t('GridFieldMultiDeleteButton.Confirm', 'Are you sure you want to delete all selected items?'),
        ));
    }


    /**
     * @param DataObject $record
     * @param int $index
     */
    public function deleteRecord($record, $index)
    {
        if ($record->hasExtension('Versioned')) {
            $record->deleteFromStage('Stage');
            $record->deleteFromStage('Live');
        } else {
            $record->delete();
        }
    }
}
