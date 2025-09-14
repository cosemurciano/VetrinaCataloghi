# VetrinaCataloghi
Plugin WordPress per la gestione dei cataloghi.

## Funzionalità aggiunte

- Visualizzazione dei cataloghi PDF direttamente nel frontend tramite PDF.js con layout a due colonne.
- Pagina di impostazioni per configurare i parametri del viewer PDF.js e caricare un logo personalizzato.

## Shortcode Cataloghi

Lo shortcode `[vc_cataloghi]` consente di mostrare un elenco di cataloghi in modo responsive.

Attributi disponibili:

- `categoria`: slug della categoria da filtrare (facoltativo).
- `numero`: numero di cataloghi da mostrare, oppure `tutti` per visualizzarli tutti.
- `per_riga`: numero di cataloghi per riga sui dispositivi larghi (default 3).

Esempio:

```
[vc_cataloghi categoria="promo" numero="6" per_riga="3"]
```
