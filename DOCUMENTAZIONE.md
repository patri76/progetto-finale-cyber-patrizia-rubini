# Progetto Finale Cyber Security - Patrizia Rubini

## Introduzione
Descrizione del progetto Cyber Blog e della Financial App.

## Setup iniziale
- Clone del progetto
- Configurazione database
- Configurazione hosts
- Avvio server Laravel
- Avvio Vite
- Avvio Financial App

## Challenge 1 - Rate limiter mancante
### Vulnerabilità
### Attacco eseguito
### Mitigazione implementata
### Verifica finale

## Challenge 1 - Mancanza di Rate Limiter

### Descrizione della vulnerabilità
Durante l’analisi dell’applicazione è stata individuata una vulnerabilità nella route pubblica dedicata alla ricerca degli articoli:

`/articles/search`

La route era accessibile liberamente da qualsiasi utente e non implementava alcun meccanismo di limitazione del numero di richieste HTTP.  
Questo significa che un attaccante avrebbe potuto inviare un numero molto elevato di richieste automatiche in un tempo estremamente ridotto, causando:

- sovraccarico del server;
- aumento dell’utilizzo di CPU e memoria;
- rallentamento generale dell’applicazione;
- possibile indisponibilità temporanea del servizio (DoS - Denial of Service).

La vulnerabilità è particolarmente critica perché la route di ricerca interagisce direttamente con il database e può quindi generare numerose query simultanee.

---

### Analisi del rischio
L’assenza di un rate limiter su endpoint pubblici rappresenta una problematica molto comune nelle applicazioni web moderne.

Un attaccante potrebbe sfruttare questa debolezza per:
- automatizzare migliaia di richieste tramite script;
- effettuare attacchi di brute force o enumeration;
- degradare le performance del sistema;
- aumentare inutilmente il carico sul database.

In un ambiente reale, un simile comportamento potrebbe compromettere la disponibilità dell’applicazione per gli utenti legittimi.

---

### Esecuzione dell’attacco
Per verificare concretamente la vulnerabilità è stato creato uno script Bash utilizzando `curl`.

Lo script simulava un attacco di tipo flood inviando richieste HTTP ripetute verso:

`/articles/search?query=test`

In assenza di protezioni:
- tutte le richieste venivano accettate dal server;
- non veniva restituito alcun errore;
- non esisteva alcun blocco automatico dell’IP.

Lo script utilizzato è stato salvato nella cartella:

`XXX-AttackTools/rate-limiter/dos-search.sh`

e inviava numerose richieste consecutive all’endpoint vulnerabile.

---

### Mitigazione implementata
Per mitigare la vulnerabilità è stato utilizzato il middleware di rate limiting integrato in Laravel.

Alla route vulnerabile HO AGGIUNTO la seguente protezione:

Route::get('/articles/search', [ArticleController::class, 'articleSearch'])
    ->middleware('throttle:10,1')
    ->name('articles.search');
