╔════════════════════════════════════════════════════════════════════════════════╗
║                          LOGIQUE DE GESTION DES VENTES                           ║
╚════════════════════════════════════════════════════════════════════════════════╝

📋 FLUX DE CRÉATION D'UNE VENTE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ÉTAPE 1️⃣  : CRÉER UNE NOUVELLE VENTE (Formulaire simple)
────────────────────────────────────────────────────────────
URL: /admin/vente/new

Informations demandées:
  • Client (autocomplete)
  • Employé responsable
  • Mode de paiement (Espèces, Crédit, Chèque, Virement)

Action après soumission:
  ✓ La vente est créée avec un code unique (V-ID/ANNÉE)
  ✓ Une facture est automatiquement générée (F-ID/ANNÉE)
  ✓ Redirection vers l'édition pour ajouter les lignes


ÉTAPE 2️⃣  : AJOUTER LES LIGNES DE VENTE (Formulaire complet)
────────────────────────────────────────────────────────────
URL: /admin/vente/{id}/edit

Informations affichées:
  • Code de la vente (auto-généré)
  • Client (affiché)
  • Employé (affiché)
  • Mode de paiement (modifiable)

Actions possibles:
  ✓ Ajouter des lignes de produits (cliquer "Ajouter une ligne")
  ✓ Saisir pour chaque ligne:
    - Produit
    - Quantité
    - Prix unitaire
    - Total ligne (calculé automatiquement = Quantité × Prix unitaire)
  ✓ Supprimer une ligne si nécessaire
  ✓ Enregistrer les modifications

Calculs automatiques:
  • Total ligne = Quantité × Prix unitaire
  • Montant total de la vente = Somme de tous les totaux lignes
  • La facture est mise à jour avec le montant restant à payer


ÉTAPE 3️⃣  : GÉRER LA FACTURE
────────────────────────────────────────────────────────────
URL: /admin/vente/{id}/facture

La facture montre:
  • Code facture (auto-généré)
  • Code vente lié
  • Montant réglé (0 par défaut)
  • Montant restant (calculé automatiquement)
  • Statut (Non réglé)

Actions possibles:
  ✓ Voir la facture en détail
  ✓ Régler la facture (partiellement ou totalement)
  ✓ Imprimer la facture


📊 RÉSUMÉ DES CALCULS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. Lors de la création de la vente (new):
   - Code vente généré automatiquement
   - Code facture généré automatiquement
   - Montant restant = 0 (pas encore de lignes)

2. Lors de l'ajout/modification des lignes (edit):
   - Total ligne = Quantité × Prix unitaire (CALCULÉ)
   - Montant total = Σ(Total lignes) (CALCULÉ)
   - Facture mise à jour: Montant restant = Montant total - Montant réglé

3. Lors de la consultation de la facture (facture):
   - Affichage du montant réglé et du montant restant
   - Possibilité de régler manuellement


🔄 FLUX DE MODIFICATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Pour modifier une vente existante:
  1. Aller à la liste des ventes
  2. Cliquer sur le bouton "modifier" (crayon)
  3. Ajouter, modifier ou supprimer des lignes
  4. Enregistrer les modifications
  → La facture est mise à jour automatiquement


🗑️  FLUX DE SUPPRESSION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Pour supprimer une vente:
  1. Aller à la liste des ventes
  2. Cliquer sur le bouton "supprimer" (poubelle)
  3. Confirmer la suppression
  → La vente ET sa facture sont supprimées automatiquement


💡 POINTS IMPORTANTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ Les codes sont auto-générés → Pas besoin de les saisir
✓ Les calculs sont automatiques → Pas d'erreur manuelle
✓ Chaque vente génère une facture automatiquement
✓ Modification d'une vente = Mise à jour de la facture
✓ Suppression d'une vente = Suppression de la facture aussi
✓ Possibilité d'ajouter/modifier/supprimer des lignes facilement
