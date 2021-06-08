<?php
/**
 * ManageConsents integrations test file.
 *
 * @see https://github.com/Maks3w/SwaggerAssertions/
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Tests\Integrations;

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;

/**
 * @internal
 * @coversNothing
 */
final class ManageConsents extends \Tests\Base
{
	use AssertsTrait;

	/**
	 * Api server id.
	 *
	 * @var int
	 */
	private static $serverId;

	/**
	 * Api user id.
	 *
	 * @var int
	 */
	private static $apiUserId;

	/**
	 * Request options.
	 *
	 * @var array
	 */
	private static $requestOptions = [];

	/**
	 * Record ID.
	 *
	 * @var array
	 */
	private static $recordId;
	private static $approvalId;

	/**
	 * @var SchemaManager
	 */
	protected static $schemaManager;

	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $httpClient;

	public static function setUpBeforeClass(): void
	{
		self::$schemaManager = new SchemaManager(json_decode(file_get_contents(ROOT_DIRECTORY . '/public_html/api/ManageConsents.json')));
		$moduleName = 'Contacts';
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if (!($field = current($moduleModel->getFieldsByDisplayType('token')))) {
			$blockInstance = current($moduleModel->getBlocks());
			$field = new \vtlib\Field();
			$field->name = 'token';
			$field->table = $moduleModel->tableName;
			$field->label = 'token';
			$field->column = 'token';
			$field->columntype = 'varchar(' . \Vtiger_Token_UIType::MAX_LENGTH . ')';
			$field->maximumlength = \Vtiger_Token_UIType::MAX_LENGTH;
			$field->uitype = 324;
			$field->typeofdata = 'V~O';
			$field->defaultvalue = '';
			$field->displaytype = 3;
			$blockInstance->addField($field);
		}
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModel->set('salutationtype', 'Mr.');
		$recordModel->set('firstname', 'Test');
		$recordModel->set('lastname', 'Testowy');
		$recordModel->set('contactstatus', 'Active');
		$recordModel->set('email', 'manage_consents@yetiforce.com');
		$recordModel->set('assigned_user_id', \App\User::getCurrentUserId());
		$recordModel->save();
		self::$recordId = $recordModel->getId();
		\App\Fields\Token::setTokens($field->name, $moduleName);

		$recordModel = \Vtiger_Record_Model::getCleanInstance('Approvals');
		$recordModel->set('name', 'Consent1');
		$recordModel->set('approvals_status', 'PLL_ACTIVE');
		$recordModel->set('assigned_user_id', \App\User::getCurrentUserId());
		$recordModel->save();
		self::$approvalId = $recordModel->getId();
	}

	/**
	 * Before a test method is run, this method is invoked.
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		$this->httpClient = new \GuzzleHttp\Client(\App\Utils::merge(\App\RequestHttp::getOptions(), [
			'base_uri' => \App\Config::main('site_URL') . 'webservice/ManageConsents/',
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'timeout' => 60,
			'connect_timeout' => 60,
			'http_errors' => false,
			'headers' => [
				'x-raw-data' => 1
			]
		]));
	}

	/**
	 * Testing add configuration.
	 */
	public function testAddConfiguration(): void
	{
		$app = \Settings_WebserviceApps_Record_Model::getCleanInstance();
		$app->set('type', 'ManageConsents');
		$app->set('status', 1);
		$app->set('name', 'manage_consents');
		$app->set('acceptable_url', '');
		$app->set('pass', 'manage_consents');
		$app->save();
		self::$serverId = (int) $app->getId();

		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => self::$serverId])->one();
		static::assertNotFalse($row, 'No record id: ' . self::$serverId);
		static::assertSame($row['type'], 'ManageConsents');
		static::assertSame($row['status'], 1);
		static::assertSame($row['name'], $app->get('name'));
		static::assertSame($row['pass'], $app->get('pass'));
		self::$requestOptions['headers']['x-api-key'] = $row['api_key'];
		self::$requestOptions['auth'] = [$app->get('name'), $app->get('pass')];

		$user = \Settings_WebserviceUsers_Record_Model::getCleanInstance('ManageConsents');
		$user->set('server_id', self::$serverId);
		$user->set('status', 1);
		$user->set('type', \Api\Portal\Privilege::USER_PERMISSIONS);
		$user->set('user_id', \App\User::getActiveAdminId());
		$user->save();

		self::$apiUserId = $user->getId();
		self::$requestOptions['headers']['x-token'] = $user->get('token');
		$row = (new \App\Db\Query())->from($user->baseTable)->where(['id' => self::$apiUserId])->one();
		static::assertNotFalse($row, 'No record id: ' . self::$apiUserId);
		static::assertSame((int) $row['server_id'], self::$serverId);
		static::assertSame($row['token'], $user->get('token'));
	}

	/**
	 * Testing RecordsList.
	 */
	public function testRecordsList(): void
	{
		$request = $this->httpClient->get('Approvals/RecordsList', self::$requestOptions);
		$this->logs = $body = $request->getBody()->getContents();
		$response = \App\Json::decode($body);
		static::assertSame(200, $request->getStatusCode(), 'Approvals/RecordsList API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertSame(1, $response['status'], 'Approvals/RecordsList API error: ' . PHP_EOL . $request->getReasonPhrase() . '|' . $body);
		static::assertNotEmpty($response['result'], 'Approvals/RecordsList result is empty and should have at least one entry: ' . self::$approvalId);
		static::assertNotEmpty($response['result']['records'][self::$approvalId], 'Approvals/RecordsList record:' . self::$approvalId . ' not exists');
		self::assertResponseBodyMatch($response, self::$schemaManager, '/webservice/ManageConsents/Approvals/RecordsList', 'get', 200);
	}
}