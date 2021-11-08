<?php
/**
 * Server access field file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Server access field class.
 */
class ServerAccess
{
	/**
	 * Get links to share the record in external services (Web service - Applications).
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return \Vtiger_Link_Model|null
	 */
	public static function getLinks(\Vtiger_Record_Model $recordModel): ?\Vtiger_Link_Model
	{
		$fields = $recordModel->getModule()->getFieldsByType('serverAccess', true);
		$isActive = 0;
		foreach ($fields as $fieldName => $fieldModel) {
			if (!$fieldModel->isEditable()) {
				unset($fields[$fieldName]);
			}
			if ($recordModel->getValueByField($fieldName)) {
				$isActive = 1;
			}
		}
		if (empty($fields)) {
			return null;
		}
		$return = null;
		if (1 === \count($fields)) {
			$fieldName = array_key_first($fields);
			$fieldModel = $fields[$fieldName];
			$webServiceApp = self::get($fieldModel->get('fieldparams'));
			$label = \App\Language::translate($isActive ? 'BTN_DISABLE_SHARE_RECORD_IN' : 'BTN_SHARE_RECORD_IN') . ' ' . ($webServiceApp['name'] ?? '');
			$return = \Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'BTN_SERVER_ACCESS',
				'linkhint' => $label,
				'linkicon' => ($isActive ? 'fas fa-user-circle' : 'far fa-user-circle'),
				'linkclass' => 'js-action-confirm btn-sm ' . ($isActive ? 'btn-success' : 'btn-secondary'),
				'dataUrl' => "index.php?module={$recordModel->getModuleName()}&action=SaveAjax&record={$recordModel->getId()}&field={$fieldName}&value=" . ($isActive ? 0 : 1),
				'linkdata' => ['add-btn-icon' => 1,	'source-view' => 'List'],
			]);
		} else {
			$return = \Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'BTN_SERVER_ACCESS',
				'linkicon' => ($isActive ? 'fas fa-user-circle' : 'far fa-user-circle'),
				'linkclass' => 'btn-sm js-quick-edit-modal ' . ($isActive ? 'btn-success' : 'btn-secondary'),
				'linkdata' => ['module' => $recordModel->getModuleName(), 'record' => $recordModel->getId(), 'show-layout' => 'vertical', 'edit-fields' => \App\Json::encode(array_keys($fields))],
			]);
		}
		return $return;
	}

	/**
	 * Get web service application details by id.
	 *
	 * @param int $serverId
	 *
	 * @return array
	 */
	public static function get(int $serverId): array
	{
		if (\App\Cache::has(__METHOD__, $serverId)) {
			return \App\Cache::get(__METHOD__, $serverId);
		}
		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => $serverId])->one(\App\Db::getInstance('webservice')) ?: [];
		\App\Cache::save(__METHOD__, $serverId, $row, \App\Cache::MEDIUM);
		return $row;
	}
}