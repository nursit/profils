<?php
/**
 * Plugin profils
 * Gestion des comptes profils
 * (c) 2011 Nursit.net
 * Licence GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;


function profils_recuperer_fond($flux){
	if ($flux['args']['fond'] == 'formulaires/editer_auteur'){
		if ($p = strpos($flux['data']['texte'],'<!--extra-->')){
			$complement = recuperer_fond('formulaires/inc-saisie-profil-profil',$flux['args']['contexte']);
			$flux['data']['texte'] = substr_replace($flux['data']['texte'],$complement,$p,0);
		}
	}
	return $flux;
}
