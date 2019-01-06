<?php

namespace Tedy\GridFieldCustom;

use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Config\Config;

 /**
  * Gives each row a checkbox which can be paired with various
  * children of GridFieldApplyToMultipleRows subclasses to
  * delete or perform other actions on many records at once.
  *
  * @author Tedy Lim <tedyjd@gmail.com>
  * @date 06.01.2019
  * @package apluswhs.com
  * @subpackage
  */
class GridFieldCheckboxSelectComponent implements GridField_ColumnProvider
{
    const CHECKBOX_COLUMN = 'SelectCheckbox';

    /**
     * Config flag to determine whether checkbox column should be prepended
     * or appended to the table.
     *
     * @var bool
     */
    private static $prepend_column = false;

    public function __construct()
    {
        Requirements::javascript('tedy/gridfieldcustom:javascript/GridFieldCheckboxSelectComponent.js');
    }


    /**
     * Modify the list of columns displayed in the table.
     *
     * @see {@link GridFieldDataColumns->getDisplayFields()}
     * @see {@link GridFieldDataColumns}.
     *
     * @param GridField $gridField
     * @param array - List reference of all column names.
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (Config::inst()->get(__CLASS__, 'prepend_column')) {
            array_unshift($columns, self::CHECKBOX_COLUMN);
        } else {
            $columns[] = self::CHECKBOX_COLUMN;
        }
    }


    /**
     * Names of all columns which are affected by this component.
     *
     * @param GridField $gridField
     * @return array
     */
    public function getColumnsHandled($gridField)
    {
        return array(self::CHECKBOX_COLUMN);
    }


    /**
     * HTML for the column, content of the <td> element.
     *
     * @param  GridField $gridField
     * @param  DataObject $record - Record displayed in this row
     * @param  string $columnName
     * @return string - HTML for the column. Return NULL to skip.
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        if ($columnName === self::CHECKBOX_COLUMN) {
            return '<input class="multiselect no-change-track" type="checkbox"
					name="' . $columnName . '[' . $record->ID . ']"
					value="' . $record->ID . '">';
        } else {
            return null;
        }
    }


    /**
     * Additional metadata about the column which can be used by other components,
     * e.g. to set a title for a search column header.
     *
     * @param GridField $gridField
     * @param string $column
     * @return array - Map of arbitrary metadata identifiers to their values.
     */
    public function getColumnMetadata($gridField, $column)
    {
        if ($column === self::CHECKBOX_COLUMN) {
            $title = _t('GridFieldCustom.SelectAllVisibleRows', 'Select all visible rows');
            return array(
                'title' => '<input class="multiselect-all no-change-track" type="checkbox"
								title="' . htmlentities($title) . '">',
            );
        }
    }


    /**
     * Attributes for the element containing the content returned by {@link getColumnContent()}.
     *
     * @param  GridField $gridField
     * @param  DataObject $record displayed in this row
     * @param  string $columnName
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return array('class' => 'col-checkbox');
    }
}
