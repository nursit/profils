<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

function action_autosubscribe_newsletter_dist($email=null){

	if (is_null($email)){
		$securiser_action = charger_fonction("securiser_action","inc");
		$email = $securiser_action();
	}

	// autosubscribe immediat sans double optin
	$subscribe = charger_fonction("subscribe","newsletter");
	$subscribe($email,array('force'=>true));
}