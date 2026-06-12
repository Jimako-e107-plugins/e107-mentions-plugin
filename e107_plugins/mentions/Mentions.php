<?php
if ( ! defined('e107_INIT')) {
	exit;
}

class Mentions
{

	protected $prefs;
	protected $parse;

	/**
	 * Mentions constructor.
	 */
	public function __construct()
	{
		$prefs = e107::getPlugPref('mentions');
		$this->prefs = is_array($prefs) ? $prefs : [];
		$this->parse = e107::getParser();
	}

	/**
	 * Returns a single plugin preference value or the given default
	 * when the pref is missing.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function pref($key, $default = null)
	{
		return isset($this->prefs[$key]) && $this->prefs[$key] !== ''
			? $this->prefs[$key] : $default;
	}

	/**
	 * Converts valid user mention to user profile-link
	 *
	 * @param string $mention
	 *  User mention string
	 *
	 * @return string
	 *  User mention profile-link or string prepended with '@'
	 */
	protected function createUserLinkFrom($mention)
	{
		$data = $this->getUserData($mention);

		if ( ! empty($data['user_name'])
			&& $data['user_name'] === $this->stripAtFrom($mention)) {
			$userData =
				['id' => $data['user_id'], 'name' => $data['user_name']];
			$link = e107::getUrl()->create('user/profile/view', $userData);

			return '<a href="' . $link . '">' . $mention . '</a>';
		}

		return $mention;
	}


	/**
	 * Get user data from database
	 *
	 * @param string $mention
	 *  String prepended with '@' which the parsing logic captured.
	 *
	 * @return array
	 *  User details from 'user' table - user_id, user_name, user_email;
	 *  empty array when no matching user exists.
	 */
	protected function getUserData($mention)
	{
		$username = e107::getParser()->toDB($this->stripAtFrom($mention));
		$row = e107::getDb()->retrieve("user", "user_name, user_id, user_email",
			"user_name = '" . $username . "' ");

		return is_array($row) ? $row : [];
	}


	/**
	 * Strips '@' sign from mention string
	 *
	 * @param string $mention
	 *  String prepended with '@'.
	 * @return string
	 *  String striped clean of '@'
	 */
	protected function stripAtFrom($mention)
	{
		return ltrim((string) $mention, '@');
	}


	/**
	 * Does Debug logging. Disabled unless the MENTIONS_DEBUG constant is
	 * defined; logs go to the e107 system log folder, never to the
	 * web-accessible plugin directory.
	 *
	 * @param string|array $content
	 *  The data to be logged - can be passed as string or array.
	 * @param string $logname
	 *  The name of log that need to be written to file-system.
	 */
	protected function log($content, $logname = 'mentions')
	{
		if ( ! defined('MENTIONS_DEBUG') || ! MENTIONS_DEBUG) {
			return;
		}

		if (is_array($content)) {
			$content = var_export($content, true);
		}

		$path = e_LOG . 'mentions_' . preg_replace('/[^\w.-]/', '', $logname) . '.log';

		file_put_contents($path, date('c') . ' ' . $content . "\n", FILE_APPEND);
	}


}
