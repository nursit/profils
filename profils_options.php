<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Verifier le statut abonne/desabonne a la newsletter
 * @param $email
 * @return bool|string
 */
function profils_verifier_statut_newsletter($email){
	$statut = '';
	if ($email){
		$subscriber = charger_fonction("subscriber","newsletter");
		$infos = $subscriber($email);
		if ($infos
			AND $infos['status']=='on'
			AND in_array('newsletter',$infos['listes']))
			$statut = ' ';
	}
	return $statut;
}
