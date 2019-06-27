<?php
require_once('./inc/config.php');

if ( isset($_SESSION['discord_web']) ) {
	website::website_redirect($website['url']);
} else if ( isset($_GET['code']) ) {
	$auth = website::discord_auth($website['discord_client'], $website['discord_secret'], $website['discord_scopes'], $_GET['code']);
	if ( $auth ) {
		$user = website::discord_user_info($auth['access_token']);
		if ( $user ) {

			$_SESSION['discord_web'] = array(
				'user_id'			=> $user['id'],											// ID Account
				'email'				=> $user['email'],										// Cuenta de correo
				'username'			=> $user['username'],									// KuroNeko
				'discriminator'		=> $user['discriminator'],								// 0001
				'avatar_id'			=> $user['avatar'],										// Hash avatar

				'flags'				=> $user['flags'],
				'premium_type'		=> $user['premium_type'],

				'access_token'		=> $auth['access_token'],								// Token
				'refresh_token'		=> $auth['refresh_token'],								// Token
				'expiration_info'	=> $auth['expires_in'],									// Tiempo de vida
				'expiration_date'	=> date('d/m/Y H:i:s', time() + $auth['expires_in'])	// Cuando expirará


			);

			$sql = "
				INSERT INTO usuarios
					(id, name, tag, avatar, email, token_access, token_refresh, token_expiration_date, stamp_info)
				VALUES
					(:id, :name, :tag, :avatar, :email, :token_access, :token_refresh, :token_expiration_date, current_timestamp)
				ON DUPLICATE KEY UPDATE
					name					= :name,
					tag						= :tag,
					avatar					= :avatar,
					email					= :email,
					token_access			= :token_access,
					token_refresh			= :token_refresh,
					token_expiration_date	= :token_expiration_date,
					stamp_info				= current_timestamp
				";

			$query = $mysql->prepare($sql);
			$query->execute([
				':id'						=> $user['id'],
				':name'						=> $user['username'],
				':tag'						=> $user['discriminator'],
				':avatar'					=> $user['avatar'],
				':email'					=> $user['email'],

				':token_access'				=> $auth['access_token'],
				':token_expiration_date'	=> date('Y-m-d H:i:s', time() + $auth['expires_in']),
				':token_refresh'			=> $auth['refresh_token']
			]);

			website::website_redirect($website['url']);
		}
	}
} else {
	website::discord_auth_redirect($website['discord_client'], $website['discord_scopes']);
}
?>
