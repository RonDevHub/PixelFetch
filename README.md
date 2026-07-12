<h1 align="center">
  <img src="public/assets/logo/Pixelfetch-1.png" height="100">
</h1>
<div align="center">

![Created](https://mini-badges.rondev.de/forgejo/RonDevHub/PixelFetch/created-at/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/forgejo/RonDevHub/PixelFetch/lastcommit/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/stars/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/issues/*/*/en) ![GitHub Repo language](https://mini-badges.rondev.de/forgejo/RonDevHub/PixelFetch/language/*/*/en) ![GitHub Repo license](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/license/*/*/en) ![GitHub Repo release](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/release/*/*/en) ![GitHub Repo release](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/forks/*/*/en) ![GitHub Repo downlods](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/downloads/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/PixelFetch/watchers) [![status-badge](https://ci.commitcloud.net/api/badges/13/status.svg)](https://ci.commitcloud.net/repos/15) 

[![Buy me a coffee](https://mini-badges.rondev.de/icon/cuptogo/Buy_me_a_Coffee-c1d82f-222/for-the-badge "Buy me a coffee")](https://www.buymeacoffee.com/RonDev)
[![Buy me a coffee](https://mini-badges.rondev.de/icon/cuptogo/ko--fi.com-c1d82f-222/for-the-badge "Buy me a coffee")](https://ko-fi.com/U6U31EV2VS)
[![Pizza Power](https://mini-badges.rondev.de/icon/paypal/PayPal/for-the-badge "Pizza Power")](https://www.paypal.com/donate/?hosted_button_id=PWY939TPCQ3RA)
</div>
<hr>

PixelFetch ist eine minimalistische, extrem ressourcensparende und zu 100% datenschutzkonforme Multi-Account-Galerie für Pixelfed. Das Skript nutzt ein automatisiertes **Lazy Caching Verfahren** und läuft sowohl auf klassischem Webspace (wie All-Inkl.com) als auch containerisiert in Docker-Umgebungen.

<div align="center">
<img src=".view/view-1.png" height="200"> <img src=".view/view-2.png" height="200">
</div>

## Features

- **Multi-Account:** Verwalte unbegrenzt viele Pixelfed-Accounts (auch von unterschiedlichen Instanzen) über eine zentrale Installation.
- **Lazy Caching:** Die Daten und Bilder werden nur aktualisiert, wenn die Galerie tatsächlich besucht wird und der Cache abgelaufen ist. Keine störenden Cronjobs im Hintergrund nötig.
- **100% DSGVO-konform:** Bilder werden lokal auf deinen Server geladen. Die Browser deiner Webseitenbesucher kommunizieren niemals mit fremden Pixelfed-Instanzen. Keine IP-Leaks.
- **Zero Dependencies:** Reines PHP ohne dicke Composer-Pakete. CSS und JS sind nativ (Vanilla).
- **Automatischer Dark-Mode:** Erkennt die Systemeinstellungen des Nutzers oder lässt sich global erzwingen.

## Installation auf Shared Hosting

1. Kopiere den gesamten Inhalt dieses Repositories in ein Verzeichnis auf deinem Server.
2. Route deine Domain/Subdomain zwingend in den Unterordner `/public`.
3. Kopiere die Datei `.env.example` zu `.env` und passe die Werte an.
4. Kopiere die Datei `config/accounts.json.example` zu `config/accounts.json` und trage dort deine Pixelfed-Instanzen und dazugehörigen Personal Access Tokens (PAT) ein.
5. Stelle sicher, dass der Ordner `/storage` für den Webserver beschreibbar ist (`CHMOD 755` oder `777`).

## Installation via Docker

Nutze das vorgefertigte Docker-Image. Das Verzeichnis `/var/www/html/storage` muss als persistentes Volume gemountet werden, damit die Cachedaten nach einem Container-Neustart erhalten bleiben:

```yaml
version: '3.8'

services:
  pixelfetch:
    image: ghcr.io/rondevhub/pixelfetch:latest
    container_name: pixelfetch
    ports:
      - "8080:80"
    environment:
      - THEME_MODE=auto
      - DEFAULT_CACHE_TTL=3600
    volumes:
      - ./storage:/var/www/html/storage
      - ./config:/var/www/html/config
    restart: unless-stopped
```

## Lizenz

Freie Software. Modifiziere und teile sie ganz nach deinen Wünschen.
