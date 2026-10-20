# Troubleshooting : Symfony Docker (FrankenPHP + Mercure)

Projet : `job_board`, basé sur le template [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker).

## Symptôme

Le conteneur `php` est `unhealthy` ou redémarre en boucle (~26 s), et il n'apparaît pas dans Docker Desktop. Les logs s'arrêtent après :

```
INFO    using config from file  {"file": "/etc/frankenphp/Caddyfile"}
```

sans aucun message d'erreur, et le conteneur sort avec `exit code 1`.

## Cause

FrankenPHP 1.13 embarque Mercure 1.0, qui casse la configuration actuelle du template. Le conteneur ne démarre plus. C'est le sujet de la pull request [#969](https://github.com/dunglas/symfony-docker/pull/969) (au moment de l'écriture, elle est ouverte et non fusionnée) et de l'issue [#968](https://github.com/dunglas/symfony-docker/issues/968).

Les erreurs apparaissent une par une, à mesure qu'on corrige la précédente :

| Erreur affichée | Cause |
|---|---|
| `unknown mercure directive "demo"` | La directive `demo` a été supprimée dans Mercure 1.0 |
| `"publisher_jwt"... work only in compatibility mode` | Il faut activer le mode compatibilité (ou migrer vers un bloc `issuer`) |
| `unable to enable hot reloading: no Mercure hub configured` | On a supprimé le bloc `mercure`, dont dépend le hot reload |
| `only one unnamed Mercure hub is allowed per configuration` | `SERVER_NAME: localhost, php:80` crée deux serveurs, donc deux hubs sans nom |

## Correctif

### 1. `compose.override.yaml`

Remplacer `demo` par `playground` :

```yaml
MERCURE_EXTRA_DIRECTIVES: playground
```

L'interface de debug de Mercure est alors sur `/.well-known/mercure/debug/`.

### 2. `frankenphp/Caddyfile`

Dans le bloc `mercure { ... }`, ajouter ces deux lignes au début. Ne pas supprimer le bloc : le hot reload en dépend.

```
mercure {
    name default
    protocol_version_compatibility 8

    # ... le reste du bloc reste inchangé
}
```

- `name default` : nomme le hub, et permet aux deux serveurs (HTTPS et HTTP) de le partager. Selon la PR, `default` est le nom que le hub sans nom portait déjà en interne.
- `protocol_version_compatibility 8` : nécessaire car MercureBundle génère encore des jetons à l'ancien format, que le mode strict rejetterait.

### 3. Valider, puis démarrer

```bash
docker compose build --pull --no-cache
docker compose run --rm --no-deps php frankenphp validate --config /etc/frankenphp/Caddyfile
```

Si `validate` ne renvoie aucune ligne `ERROR` :

```bash
docker compose up -d --wait
docker compose ps
```

Le conteneur doit passer `healthy`. Ouvrir ensuite `https://localhost` et accepter le certificat auto-signé.

> Le `--pull` sur le `build` est important : la nouvelle config ne fonctionne pas sur FrankenPHP 1.12.

## Notes sur la sécurité

- `protocol_version_compatibility 8` assouplit la validation des jetons d'accès Mercure. En développement local, c'est sans conséquence.
- Ne pas garder ce correctif manuel en production si Mercure est utilisé : repartir de la version corrigée du template (une fois la PR fusionnée) ou migrer vers un bloc `issuer`.
- Changer `CADDY_MERCURE_JWT_SECRET` dans l'environnement de production (ne jamais garder `!ChangeThisMercureHubJWTSecretKey!`).
- Si Mercure n'est pas utilisé par le projet, il reste nécessaire ici uniquement à cause du hot reload du mode dev.

## Autres problèmes rencontrés

### Le conteneur n'apparaît pas dans Docker Desktop

Sur Linux, Docker Desktop utilise son propre moteur dans une VM (contexte `desktop-linux`). Les conteneurs lancés avec le moteur classique (contexte `default`) n'y apparaissent pas.

```bash
docker context ls
docker context use desktop-linux
```

Puis relancer le projet : `docker compose up -d --wait`.

### Port 80 déjà utilisé

Utiliser d'autres ports (à mettre dans le fichier `.env` à la racine pour ne pas les retaper) :

```
HTTP_PORT=8000
HTTPS_PORT=8443
HTTP3_PORT=8443
```

Le site est alors sur `https://localhost:8443`. Dans `compose.yaml`, les blocs de ports HTTPS et HTTP/3 ne doivent pas être commentés, sinon le site n'est pas accessible (Caddy redirige le HTTP vers le HTTPS).

### Warning `pull access denied for app-php-dev`

Inoffensif : avec `--pull always`, Docker essaie de télécharger une image qui est construite localement. Lancer simplement `docker compose up -d --wait`.

### Firefox affiche `SEC_ERROR_BAD_SIGNATURE` sur `https://localhost`

Signalé en commentaire de la PR #969. Essayer un autre navigateur ou ajouter l'exception manuellement.

## Commandes de diagnostic

| Besoin | Commande |
|---|---|
| État des conteneurs | `docker compose ps -a` |
| Logs du service php | `docker compose logs php 2>&1 \| tail -50` |
| Trouver l'erreur exacte du Caddyfile | `docker compose run --rm --no-deps php frankenphp validate --config /etc/frankenphp/Caddyfile` |
| Code de sortie et OOM | `docker inspect --format 'exit={{.State.ExitCode}} oom={{.State.OOMKilled}} restarts={{.RestartCount}}' $(docker compose ps -aq php)` |
| État du healthcheck | `docker inspect --format '{{json .State.Health}}' $(docker compose ps -aq php)` |
| Voir les événements de plantage | `docker events --filter event=die --filter event=oom` |
| Configuration Compose réellement appliquée | `docker compose config` |
| Contexte Docker actif | `docker context show` |

Interprétation du code de sortie : `137` ou `oom=true` = manque de mémoire, `139` = plantage (segfault), `1` = erreur de configuration (lancer `validate` pour la voir).

## Repartir de zéro

```bash
docker compose down -v --remove-orphans
docker context use desktop-linux
docker compose build --pull --no-cache
docker compose up -d --wait
```

Puis appliquer le correctif ci-dessus si l'erreur Mercure revient.
