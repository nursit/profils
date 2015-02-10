<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip("formulaires/editer_logo");

/**
 * Formulaire #EDITER_AVATAR
 *
 */

/**
 * Chargement du formulaire
 *
 * @param integer $id_auteur    Identifiant de l'auteur
 * @param string $retour       Url de redirection apres traitement
 * @param Array $options       Tableau d'option (exemple : image_reduire => 50)
 * @return Array               Variables d'environnement pour le fond
 */
function formulaires_editer_avatar_charger_dist($id_auteur, $retour='', $options=array()){
	// pas dans une boucle ? formulaire pour le logo du site
	// dans ce cas, il faut chercher un 'siteon0.ext'
	$objet = 'auteur';
	$_id_objet = 'id_auteur';

	if (!is_array($options))
		$options = unserialize($options);

	if (isset($GLOBALS['visiteur_session']['id_auteur'])
		AND $id_auteur == $GLOBALS['visiteur_session']['id_auteur']
	  AND !isset($options['editable'])){
		$options['editable'] = true;
	}
	if (!isset($options['titre'])){
		$options['titre'] = '';
	}
	if (!isset($options['label'])){
		$options['label'] = _T('profils:label_avatar');
	}

	$charger = charger_fonction("charger","formulaires/editer_logo");
	$valeurs = $charger("auteur",$id_auteur,$retour,$options);

	return $valeurs;
}

/**
 * Identifier le formulaire en faisant abstraction des parametres qui
 * ne representent pas l'objet edite
 */
function formulaires_editer_avatar_identifier_dist($id_auteur, $retour='', $options=array()){
	return serialize(array($id_auteur));
}

/**
 * Verification avant traitement
 *
 * On verifie que l'upload s'est bien passe et
 * que le document recu est une image (d'apres son extension)
 *
 * @param integer $id_auteur    Identifiant de l'auteur
 * @param string $retour       Url de redirection apres traitement
 * @param Array $options       Tableau d'option (exemple : image_reduire => 50)
 * @return Array Tableau des erreurs
 */
function formulaires_editer_avatar_verifier_dist($id_auteur, $retour='', $options=array()){
	$erreurs = array();

	$verifier = charger_fonction("verifier","formulaires/editer_logo");
	$erreurs = $verifier("auteur",$id_auteur,$retour,$options);

	return $erreurs;
}

/**
 * Traitement de l'upload d'un logo
 *
 * Il est affecte au site si la balise n'est pas dans une boucle,
 * sinon a l'objet concerne par la boucle ou indiquee par les parametres d'appel
 *
 * @param integer $id_auteur    Identifiant de l'auteur
 * @param string $retour       Url de redirection apres traitement
 * @param Array $options       Tableau d'option (exemple : image_reduire => 50)
 * @return Array
 */
function formulaires_editer_avatar_traiter_dist($id_auteur, $retour='', $options=array()){
	if (!_request('supprimer_logo_on')){
		refuser_traiter_formulaire_ajax();
	}
	$traiter = charger_fonction("traiter","formulaires/editer_logo");
	$res = $traiter("auteur",$id_auteur,$retour,$options);

	return $res;
}
