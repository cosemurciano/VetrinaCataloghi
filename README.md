# VetrinaCataloghi
Plugin WordPress per la gestione dei cataloghi.

## Funzionalità aggiunte

- Visualizzazione dei cataloghi PDF direttamente nel frontend tramite PDF.js con layout a due colonne.
- Pagina di impostazioni per configurare i parametri del viewer PDF.js e caricare un logo personalizzato.

## Elenco dettagliato delle funzionalità

- **Custom post type "Vetrina Cataloghi"** con supporto a titolo, contenuto e immagine in evidenza, complete di riscrittura permalink dedicata e archivio pubblico.【F:vetrina-cataloghi.php†L16-L43】
- **Tassonomia gerarchica "Categorie"** associata ai cataloghi per organizzare i contenuti in categorie con interfaccia di gestione nel backend.【F:vetrina-cataloghi.php†L45-L74】
- **Metabox per upload del PDF** che permette di selezionare o caricare il file dal Media Library, memorizzando l'allegato come metadato personalizzato del catalogo.【F:vetrina-cataloghi.php†L83-L152】
- **Caricamento degli script admin** necessari per il media uploader e per l'editor di codice nella pagina del CSS personalizzato.【F:vetrina-cataloghi.php†L180-L205】
- **Colonna miniatura nell'elenco cataloghi** per visualizzare rapidamente l'immagine in evidenza in amministrazione.【F:vetrina-cataloghi.php†L206-L233】
- **Gestione dei permalink all'attivazione/disattivazione del plugin** con flush delle rewrite rules dedicato.【F:vetrina-cataloghi.php†L234-L247】
- **Stili frontend dedicati al viewer** con possibilità di iniettare CSS personalizzato salvato nelle opzioni del plugin.【F:vetrina-cataloghi.php†L251-L272】
- **Template personalizzato per le singole schede** che incorpora il viewer PDF.js, rispetta le funzionalità abilitate e consente di mostrare logo, titolo e contenuti testuali accanto al documento.【F:vetrina-cataloghi.php†L273-L288】【F:templates/single-vetrina_catalogo.php†L1-L55】
- **Template per la tassonomia** con griglia responsive dei cataloghi e paginazione standard di WordPress.【F:vetrina-cataloghi.php†L290-L305】【F:templates/taxonomy-categoria_cataloghi.php†L1-L36】
- **Impostazione del numero di cataloghi per pagina** fissata a 20 elementi per archivio e tassonomia, migliorando la navigazione delle liste.【F:vetrina-cataloghi.php†L306-L317】
- **Pagine di impostazioni dedicate** nel menu del custom post type:
  - Configurazione del viewer PDF.js con attivazione/disattivazione delle sue componenti UI e caricamento del logo, con valori sanificati prima del salvataggio.【F:vetrina-cataloghi.php†L318-L402】
  - Editor del CSS del template con integrazione del code editor di WordPress e sanificazione del codice inserito.【F:vetrina-cataloghi.php†L180-L205】【F:vetrina-cataloghi.php†L318-L352】【F:vetrina-cataloghi.php†L404-L423】
  - Generatore di shortcode con selezione categoria, limite numerico e layout per riga, completo di pulsante per la copia rapida.【F:vetrina-cataloghi.php†L425-L509】
- **Shortcode `[vc_cataloghi]`** che crea una griglia responsive dei cataloghi filtrabile per categoria, con controllo sul numero di elementi mostrati, colonne configurabili e caricamento degli stili dedicati.【F:vetrina-cataloghi.php†L584-L640】【F:assets/css/cataloghi-shortcode.css†L1-L21】
- **Sanificazione delle opzioni e del CSS** per garantire la sicurezza dei dati memorizzati nelle impostazioni.【F:vetrina-cataloghi.php†L330-L352】

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
