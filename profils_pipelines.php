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
 * Enrichir le formulaire editer_auteur avec les champs du profil
 * @param $flux
 * @return mixed
 */
function profils_formulaire_fond($flux){
	if ($flux['args']['form'] == 'editer_auteur'){
		if ($p = strpos($flux['data'],'<!--extra-->')){
			$complement = recuperer_fond('formulaires/inc-saisie-profil-profil',$flux['args']['contexte']);
			$flux['data'] = substr_replace($flux['data'],$complement,$p,0);
		}
	}
	return $flux;
}

/**
 * Pre-charger les infos profils (nom, adresse, tel) dans le formulaire souscription
 * si le visiteur est loge
 *
 * @param array $flux
 * @return array
 */
function profils_formulaire_charger($flux){

	if ($flux['args']['form']=='souscription'
		AND !test_espace_prive()
	  AND isset($GLOBALS['visiteur_session']['id_auteur'])
	  AND $GLOBALS['visiteur_session']['id_auteur']){

		if (isset($GLOBALS['visiteur_session']['name']) AND $GLOBALS['visiteur_session']['name'])
			$flux['data']['nom'] = $GLOBALS['visiteur_session']['name'];
		if (isset($GLOBALS['visiteur_session']['prenom']) AND $GLOBALS['visiteur_session']['prenom'])
			$flux['data']['prenom'] = $GLOBALS['visiteur_session']['prenom'];

		if (isset($GLOBALS['visiteur_session']['adresse_1'])){
			$adresse = $GLOBALS['visiteur_session']['adresse_1']
				. "\n" . $GLOBALS['visiteur_session']['adresse_2']
				. "\n" . $GLOBALS['visiteur_session']['adresse_bp'];
			$adresse = explode("\n",$adresse);
			$adresse = array_filter($adresse);
			$adresse = trim(implode("\n",$adresse));
			if ($adresse)
				$flux['data']['adresse'] = $adresse;
		}
		if (isset($GLOBALS['visiteur_session']['adresse_cp']) AND $GLOBALS['visiteur_session']['adresse_cp'])
			$flux['data']['code_postal'] = $GLOBALS['visiteur_session']['adresse_cp'];
		if (isset($GLOBALS['visiteur_session']['adresse_ville']) AND $GLOBALS['visiteur_session']['adresse_ville'])
			$flux['data']['ville'] = $GLOBALS['visiteur_session']['adresse_ville'];
		if (isset($GLOBALS['visiteur_session']['adresse_pays']) AND $GLOBALS['visiteur_session']['adresse_pays'])
			$flux['data']['pays'] = $GLOBALS['visiteur_session']['adresse_pays'];

		if (isset($GLOBALS['visiteur_session']['tel_fixe']) AND $GLOBALS['visiteur_session']['tel_fixe'])
			$flux['data']['telephone'] = $GLOBALS['visiteur_session']['tel_fixe'];
		elseif (isset($GLOBALS['visiteur_session']['tel_mobile']) AND $GLOBALS['visiteur_session']['tel_mobile'])
			$flux['data']['telephone'] = $GLOBALS['visiteur_session']['tel_mobile'];
	}
	return $flux;
}