# QM Logger
QM Logger est un add-on à Query Monitor qui permet de créer des fichiers de logs dans WP_CONTENT_DIR contenant des statistiques récupérées par Query Monitor.

Deux fichiers peuvent être créés:

- `qm-logger-html.csv` : contient toutes les requêtes faites à WordPress.
- `qm-logger-sql.csv`  : contient toutes les requêtes lentes (slow queries) faites par le site

Par défaut, le **HTML Logger** récupère :

- `date` : Date et heure du log
- `request` : Url ayant été demandée
- `db_total_time` : Temps total des accès à la base de données
- `db_requests` : Nombre de requêtes faites à la base de données
- `html_time` : Temps de génération de la page HTML
- `memory` : Mémoire utilisée pour générer la page (en mégaoctets)

Par défaut, le **Slow queries Logger** récupère :

- `date` : Date et heure du log
- `request` : Url ayant été demandée
- `sql` : Requête SQL exécutée
- `ltime` : Temps d'exécution de la requête


## Paramétrage

Deux filtres ont été créés pour modifier le nom et l'emplacement des fichiers de logs :

- `qml/output/html/filename`
- `qml/output/sql/filename`

Deux filtres ont été créés pour modifier les données récupérées et ajouter de l'information supplémentaire :

- `qml/output/html/data`
- `qml/output/sql/queries`

**Attention :** si vous ajoutez de l'information supplémentaire, les fichiers actuels de logs doivent être supprimés. Autrement, 
les nouvelles informations ne seront pas ajoutées puisque cela briserait la logique du CSV. Un nouveau fichier doit être créé
pour que la nouvelle structure soit appliquée.
