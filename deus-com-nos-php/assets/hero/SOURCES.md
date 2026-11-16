# Photos de présentation HD — DEUS COM NOS

Les quatre photos ci-dessous servent **uniquement de visuels d'ambiance pour le carrousel d'accueil**. Elles ne représentent pas nécessairement des articles en stock.

Les photos sont chargées depuis les serveurs des auteurs / de leur plateforme, avec une URL de grande taille dans `views/home.php` ; si l'image Internet échoue, une image PNG locale de `assets/hero/` s'affiche automatiquement.

| Position | Sujet | Source / crédit | Licence de la source |
|---|---|---|---|
| 1 | Sac brun | [personalgraphic.com sur Unsplash](https://unsplash.com/photos/a-brown-leather-handbag-on-a-white-background-zxPo13geJ5U) | Unsplash License |
| 2 | Escarpins beige | [Lucent Designs Media International sur Pexels](https://www.pexels.com/photo/elegant-high-heel-shoes-on-white-background-28821783/) | Pexels License |
| 3 | Petit sac noir | [Rube G. sur Pexels](https://www.pexels.com/photo/design-purse-in-white-background-17152271/) | Pexels License |
| 4 | Sandales à talons | [José Martin Segura Benites sur Pexels](https://www.pexels.com/photo/elegant-high-heels-on-white-background-26856057/) | Pexels License |

## Rendu
La combinaison `mix-blend-mode: multiply` et d'un léger masque CSS fond les arrière-plans blancs dans le décor pastel. Il s'agit d'une **simulation de détourage** : les originaux Internet sont des JPEG, pas des PNG transparents.

Le carrousel reste fonctionnel sans Internet grâce aux PNG locaux préexistants.
