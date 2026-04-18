<?php
/**
 * Vitanova — Messages FR
 * Toutes les chaînes de texte du site en français.
 * NE JAMAIS inventer de nouveaux messages — utiliser uniquement ces constantes.
 */

// ============================================================
// AUTHENTIFICATION — CONNEXION
// ============================================================
define('MSG_LOGIN_WRONG_CREDENTIALS',   'Adresse email ou mot de passe incorrect.');
define('MSG_LOGIN_EMAIL_EMPTY',         'Veuillez saisir votre adresse email.');
define('MSG_LOGIN_PASSWORD_EMPTY',      'Veuillez saisir votre mot de passe.');
define('MSG_LOGIN_EMAIL_INVALID',       'L\'adresse email saisie n\'est pas valide.');
define('MSG_LOGIN_ACCOUNT_NOT_FOUND',   'Aucun compte n\'existe avec cette adresse email.');
define('MSG_LOGIN_SUCCESS',             'Connexion réussie. Bienvenue !');
define('MSG_SESSION_EXPIRED',           'Votre session a expiré. Veuillez vous reconnecter.');

// ============================================================
// AUTHENTIFICATION — INSCRIPTION
// ============================================================
define('MSG_REGISTER_EMAIL_TAKEN',      'Cette adresse email est déjà associée à un compte.');
define('MSG_REGISTER_PASSWORD_SHORT',   'Le mot de passe doit contenir au moins 8 caractères.');
define('MSG_REGISTER_PASSWORD_MISMATCH','Les mots de passe ne correspondent pas.');
define('MSG_REGISTER_NAME_SHORT',       'Le nom doit contenir au moins 2 caractères.');
define('MSG_REGISTER_NAME_EMPTY',       'Veuillez saisir votre nom complet.');
define('MSG_REGISTER_EMAIL_EMPTY',      'Veuillez saisir une adresse email.');
define('MSG_REGISTER_PASSWORD_EMPTY',   'Veuillez choisir un mot de passe.');
define('MSG_REGISTER_CONFIRM_EMPTY',    'Veuillez confirmer votre mot de passe.');
define('MSG_REGISTER_SUCCESS',          'Compte créé avec succès. Bienvenue chez Vitanova !');
define('MSG_REGISTER_SERVER_ERROR',     'Une erreur est survenue lors de la création du compte. Veuillez réessayer.');

// ============================================================
// AUTHENTIFICATION — DÉCONNEXION
// ============================================================
define('MSG_LOGOUT_SUCCESS',            'Vous avez été déconnecté avec succès.');

// ============================================================
// CHECKOUT
// ============================================================
define('MSG_CHECKOUT_NAME_EMPTY',       'Veuillez saisir votre nom complet.');
define('MSG_CHECKOUT_EMAIL_EMPTY',      'Veuillez saisir votre adresse email.');
define('MSG_CHECKOUT_EMAIL_INVALID',    'L\'adresse email saisie n\'est pas valide.');
define('MSG_CHECKOUT_PHONE_EMPTY',      'Veuillez saisir votre numéro de téléphone.');
define('MSG_CHECKOUT_PHONE_INVALID',    'Veuillez saisir un numéro de téléphone tunisien valide (ex: 55 123 456).');
define('MSG_CHECKOUT_ADDRESS_EMPTY',    'Veuillez saisir votre adresse de livraison.');
define('MSG_CHECKOUT_CITY_EMPTY',       'Veuillez saisir votre ville.');
define('MSG_CHECKOUT_POSTAL_EMPTY',     'Veuillez saisir votre code postal.');
define('MSG_CHECKOUT_POSTAL_INVALID',   'Veuillez saisir un code postal tunisien valide (4 chiffres).');
define('MSG_CHECKOUT_CART_EMPTY',       'Votre panier est vide. Veuillez ajouter des produits avant de commander.');
define('MSG_ORDER_SUCCESS',             'Commande confirmée ! Nous vous contacterons pour la livraison.');
define('MSG_ORDER_SERVER_ERROR',        'Une erreur est survenue lors de la validation de votre commande. Veuillez réessayer.');

// ============================================================
// PANIER
// ============================================================
define('MSG_CART_ITEM_ADDED',           'Produit ajouté au panier avec succès.');
define('MSG_CART_ITEM_REMOVED',         'Produit retiré du panier.');
define('MSG_CART_UPDATED',              'Panier mis à jour.');
define('MSG_CART_OUT_OF_STOCK',         'Ce produit est actuellement en rupture de stock.');
define('MSG_CART_QUANTITY_EXCEEDED',    'La quantité demandée dépasse le stock disponible.');
define('MSG_CART_EMPTY',                'Votre panier est vide. Découvrez nos produits !');

// ============================================================
// CONTACT
// ============================================================
define('MSG_CONTACT_NAME_EMPTY',        'Veuillez saisir votre nom.');
define('MSG_CONTACT_EMAIL_EMPTY',       'Veuillez saisir votre adresse email.');
define('MSG_CONTACT_EMAIL_INVALID',     'L\'adresse email saisie n\'est pas valide.');
define('MSG_CONTACT_SUBJECT_EMPTY',     'Veuillez saisir un sujet.');
define('MSG_CONTACT_MESSAGE_EMPTY',     'Veuillez saisir votre message.');
define('MSG_CONTACT_MESSAGE_SHORT',     'Votre message est trop court. Veuillez fournir plus de détails.');
define('MSG_CONTACT_SUCCESS',           'Message envoyé avec succès ! Nous vous répondrons sous 24h.');
define('MSG_CONTACT_SERVER_ERROR',      'Une erreur est survenue. Veuillez réessayer ou nous contacter directement par email.');

// ============================================================
// AVIS PRODUITS
// ============================================================
define('MSG_REVIEW_NOT_LOGGED_IN',      'Vous devez être connecté pour laisser un avis.');
define('MSG_REVIEW_ALREADY_SUBMITTED',  'Vous avez déjà soumis un avis pour ce produit.');
define('MSG_REVIEW_RATING_EMPTY',       'Veuillez sélectionner une note.');
define('MSG_REVIEW_COMMENT_EMPTY',      'Veuillez rédiger un commentaire.');
define('MSG_REVIEW_COMMENT_SHORT',      'Votre avis est trop court. Veuillez écrire au moins 10 caractères.');
define('MSG_REVIEW_SUCCESS',            'Votre avis a été soumis avec succès. Merci !');
define('MSG_REVIEW_SERVER_ERROR',       'Une erreur est survenue lors de la soumission de votre avis.');

// ============================================================
// ADMIN
// ============================================================
define('MSG_ADMIN_UNAUTHORIZED',        'Accès refusé. Vous n\'avez pas les droits nécessaires.');
define('MSG_ADMIN_PRODUCT_ADDED',       'Produit ajouté avec succès.');
define('MSG_ADMIN_PRODUCT_UPDATED',     'Produit mis à jour avec succès.');
define('MSG_ADMIN_PRODUCT_DELETED',     'Produit supprimé avec succès.');
define('MSG_ADMIN_PRODUCT_DELETE_ERROR','Impossible de supprimer ce produit car il est lié à des commandes existantes.');
define('MSG_ADMIN_ORDER_STATUS_UPDATED','Statut de la commande mis à jour.');
define('MSG_ADMIN_REQUIRED_FIELD',      'Veuillez remplir tous les champs obligatoires.');
define('MSG_ADMIN_PRICE_INVALID',       'Le prix saisi n\'est pas valide.');
define('MSG_ADMIN_STOCK_INVALID',       'La valeur du stock n\'est pas valide.');

// ============================================================
// GÉNÉRIQUES / GLOBAUX
// ============================================================
define('MSG_GENERIC_SERVER_ERROR',      'Une erreur inattendue est survenue. Veuillez réessayer.');
define('MSG_GENERIC_REQUIRED_FIELD',    'Ce champ est obligatoire.');
define('MSG_404_TEXT',                  'La page que vous recherchez est introuvable.');
define('MSG_404_BACK',                  'Retour à l\'accueil');
define('MSG_ACCESS_RESTRICTED',         'Vous devez être connecté pour accéder à cette page.');
