<?php
/**
 * SVendorEnquiries CRMEntity Class
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class SVendorEnquiries extends Vtiger_CRMEntity
{

	public $table_name = 'u_yf_svendorenquiries';
	public $table_index = 'svendorenquiriesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('u_yf_svendorenquiriescf', 'svendorenquiriesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'u_yf_svendorenquiries', 'u_yf_svendorenquiriescf', 'vtiger_entity_stats');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_svendorenquiries' => 'svendorenquiriesid',
		'u_yf_svendorenquiriescf' => 'svendorenquiriesid',
		'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('svendorenquiries', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('svendorenquiries', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	public $popup_fields = Array('subject');
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('subject', 'assigned_user_id');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			\App\Fields\RecordNumber::setNumber($moduleName, 'S-C', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', ['SVendorEnquiries']);

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('SVendorEnquiries'));
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(vtlib\Functions::getModuleId($moduleName));
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}
}
