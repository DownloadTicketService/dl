Service de téléchargement de ticket
===================================

.. Contents::


Guide rapide pour envoyer des pièces jointes
--------------------------------------------

Tout d'abord, allez sur https://dl.example.com/ et authentifier vous avec votre
nom d'utilisateur et votre mot de passe.

Vous devriez voir la page ci-dessous:

.. image:: t-step-1.png

1) Sélectionnez le fichier à envoyer avec le bouton "Parcourir"

2) Cliquer sur le bouton "Télécharger" pour envoyer votre fichier.

.. image:: t-step-2.png

3) Cliquez sur le bouton "Envoyer par e-mail" pour envoyer par e-mail le lien
   contenant le fichier que vous venez de télécharger.

Par défaut, le destinataire dispose d'une semaine pour télécharger le fichier
avant qu'il ne soit automatiquement supprimé. Vous pouvez modifier ce
comportement en définissant certains paramètres avant de le télécharger.

Vous pouvez voir la liste des fichiers que vous avez téléchargés et aussi les
gérer en cliquant sur "Tickets actifs", au bas de la page.


Guide rapide pour recevoir des pièces jointes
---------------------------------------------

Tout d'abord, allez sur https://dl.example.com/ et authentifier vous avec votre
nom d'utilisateur et votre mot de passe.

Vous devriez voir la page ci-dessous:

.. image:: g-step-1.png

1) Cliquez sur "Nouvelle concession" au bas de la page :

.. image:: g-step-2.png

2) Entrer *votre* adresse email.

3) Cliquez sur "Créer" pour générer une concession:

.. image:: g-step-3.png

4) Cliquez sur "Envoyer par e-mail" pour envoyer par e-mail le lien qui
   permettra de vous faire parvenir un fichier.

Le destinataire devra simplement suivre les instructions contenues dans le
mail. Une fois, le fichier téléchargé sur le serveur, vous recevrez un e-mail
contenant un autre lien vers le fichier que vous pourrez télécharger.


Paramètres avancés
------------------

Avant de télécharger un fichier, vous pouvez personnaliser les paramètres de
téléchargement et de supression en modifiant les "Paramètres avancés":

.. image:: t-advanced.png

* *Si vous souhaitez que votre fichier ne soit jamais supprimé* cocher "Ticket
  permanent/téléchargement". Ceci rendra votre fichier toujours disponible
  jusqu'à ce que vous le retiriez manuellement.

* *Si vous voulez être averti chaque fois que quelqu'un télécharge le fichier*,
  vous pouvez renseigner votre adresse e-mail dans le champs "Notifier par
  e-mail". Vous recevrez une notification chaque fois que le fichier sera
  téléchargé avec succès ou retiré du serveur.

Expirera dans # jours:

  Indiquer le nombre de jours maximal durant lesquels ce fichier pourra être
  téléchargé. Passé ce délai ce fichier ne pourra plus être téléchargé et sera
  automatiquement supprimé.

Expirera dans # heures après le dernier téléchargement:

  Indiquer le nombre d'heures pendant lesquelles ce fichier restera disponible
  après avoir été téléchargé. Si celui-ci n'est pas téléchargé pendant le délai
  indiqué, il sera automatiquement supprimé.

Expirera après # téléchargement:

  Indiquer le nombre total de téléchargements autorisé pour ce fichier. Au-delà
  de cette limite, ce fichier sera automatiquement supprimé.

Si au moins un de ces paramètres est appliqué le fichier sera supprimé. Mettre
n'importe quel paramètre à "0" pour désactiver son état.
