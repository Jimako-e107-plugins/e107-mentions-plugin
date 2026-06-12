<?php
if ( ! defined('e107_INIT')) {
	exit;
}


class MentionsAutoComplete extends Mentions
{
	private $db;
	private $ajax;


	/**
	 * MentionsAutoComplete constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->db = e107::getDb();
		$this->ajax = e107::getAjax();
	}


	/**
	 * Static alias for MentionsAutoComplete::getResponse()
	 *
	 * @param string $input
	 *  _GET param to respond to.
	 * @see MentionsAutoComplete::getResponse()
	 */
	public static function query($input)
	{
		$autoComplete = new MentionsAutoComplete;
		$autoComplete->getResponse($input);
	}


	/**
	 * Responds to auto-completion API HTTP requests,
	 * returns JSON formatted response.
	 *
	 * @param string $queryParam
	 *  XHR _GET query param to give response for.
	 */
	public function getResponse($queryParam)
	{

		if (e_AJAX_REQUEST && USER && vartrue($queryParam)) {

			$db = $this->db;
			$tp = $this->parse;
			$ajax = $this->ajax;

			$maxChar = (int) $this->pref('atwho_max_char', 15);
			$limit = (int) $this->pref('atwho_item_limit', 5);

			// cap length, escape for SQL and neutralize LIKE wildcards
			$mq = mb_substr((string) $queryParam, 0, $maxChar);
			$mq = $tp->toDB($mq);
			$mq = addcslashes($mq, '%_');

			$where = "user_name LIKE '" . $mq . "%' AND user_ban = 0 ";

			$result =
				$db->select('user', 'user_name, user_image, user_login',
				$where . ' ORDER BY user_name LIMIT ' . $limit);

			if ($result) {

				$data = [];
				while ($row = $db->fetch()) {

					if ( ! empty($this->prefs['atwho_avatar'])) {
						$data[] = [
							'image'    => $this->getAvatar($row['user_image']),
							'username' => $row['user_name'],
							'name'     => $row['user_login'],
						];
					} else {
						$data[] = [
							'username' => $row['user_name'],
							'name'     => $row['user_login'],
						];
					}
				}

				$ajax->response($data);

			} else {

				$msg = [
					'error' => [
						'msg'  => 'No user found!',
						'code' => '4',
					],
				];

				$ajax->response($msg);

			}

		}
		die;
	}


	/**
	 * Static alias for MentionsAutoComplete::loadLibs()
	 * @see MentionsAutoComplete::loadLibs()
	 */
	public static function libs()
	{
		$autoComplete = new MentionsAutoComplete;
		$autoComplete->loadLibs();
	}


	/**
	 * Loads mentions auto-complete Javascript libraries based on the plugin
	 *  - preference as load it using local or global path. Only loaded if
	 *  - the plugin is active, its a user area and the user is not a guest.
	 */
	public function loadLibs()
	{

		if ( ! empty($this->prefs['mentions_active']) && USER_AREA && USER) {

			if ( ! empty($this->prefs['use_global_path'])) {

				$this->loadLibsUsingGlobalPath();
			} else {

				$this->loadLibsUsingLocalPath();
			}

			$this->setLibOptions();

			e107::js('footer', '{e_PLUGIN}mentions/js/mentions.js', 'jquery');
		}
	}


	/**
	 * Loads Javascript libraries from the global path
	 */
	protected function loadLibsUsingGlobalPath()
	{
		e107::library('load', 'ichord.caret', 'minified');
		e107::library('load', 'ichord.atwho', 'minified');
	}


	/**
	 * Loads Javascript libraries from the local path
	 */
	protected function loadLibsUsingLocalPath()
	{
		e107::css('mentions', 'js/ichord.atwho/dist/css/jquery.atwho.min.css');
		e107::js('footer',
			e_PLUGIN . 'mentions/js/ichord.caret/dist/jquery.caret.min.js',
			'jquery', 1);
		e107::js('footer',
			e_PLUGIN . 'mentions/js/ichord.atwho/dist/js/jquery.atwho.min.js',
			'jquery', 2);
	}


	/**
	 * Lay-down auto-complete Javascript library settings
	 *
	 */
	private function setLibOptions()
	{
		// Mentions auto-complete API endpoint
		$apiPath = e_PLUGIN_ABS . 'mentions/index.php';

		$jsSettings = [
			'api_endpoint' => $apiPath,
			'suggestions'  => [
				'minChar'    => (int) $this->pref('atwho_min_char', 2),
				'maxChar'    => (int) $this->pref('atwho_max_char', 15),
				'entryLimit' => (int) $this->pref('atwho_item_limit', 5),
				'hiFirst'    => (bool) $this->pref('atwho_highlight_first', 1)
			],
			'inputFields' => ['activeOnes' => $this->obtainFields()]
		];

		// Footer - settings + script
		e107::js('settings', ['mentions' => $jsSettings]);
	}


	/**
	 * Returns all e107 'texarea' form fields selector ids that need to have -
	 * auto-complete based on 'mentions_contexts'  plugin preference.
	 *
	 * @return string
	 *  comma separated string of form field ids that require auto-complete
	 */
	private function obtainFields()
	{
		if ((int) $this->pref('mentions_contexts', 1) === 1) {
			return '#cmessage, #forum-quickreply-text, #post';
		}
		
		return '#cmessage, #comment, #forum-quickreply-text, #post';
	}


	/**
	 * Parse and return user avatar image markup ready to be rendered on page.
	 *
	 * @param string $userImage
	 *  User image string obtained from db
	 * @return mixed|null|string
	 *  Html markup with '<img' tag and specified user's avatar image file sourced.
	 */
	private function getAvatar($userImage)
	{
		$measure = (int) $this->pref('avatar_size', 32);

		$shape = $this->pref('avatar_border', 'circle');

		return $this->parse->toAvatar(

			['user_image' => $userImage],

			[
				'w' => $measure,
				'h' => $measure,
				'crop' => 'C',
				'shape' => $shape
			]

		);

	}


}
