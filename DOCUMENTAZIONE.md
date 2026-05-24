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


# Challenge 2 - CSRF tramite richieste GET amministrative

## Descrizione della vulnerabilità
L’applicazione utilizzava richieste HTTP GET per eseguire operazioni amministrative sensibili, come l’assegnazione di privilegi agli utenti.

Le route vulnerabili erano:

/admin/{user}/set-admin
/admin/{user}/set-revisor
/admin/{user}/set-writer

//L’utilizzo del metodo GET per modificare lo stato dell’applicazione rappresenta una vulnerabilità di sicurezza, perché permette a siti esterni di eseguire richieste automatiche sfruttando la sessione autenticata dell’amministratore.

//Analisi del rischio
//Un attaccante potrebbe creare una pagina web apparentemente innocua contenente richieste nascoste verso l’applicazione vulnerabile.
//Se un amministratore autenticato visita la pagina:
//* il browser invia automaticamente la richiesta;
//* la sessione admin viene utilizzata senza consenso;
//* l’azione amministrativa viene eseguita.
//uesta tipologia di attacco è nota come Cross-Site Request Forgery (CSRF).

//Esecuzione dell’attacco
//È stata creata una pagina HTML malevola nella cartella:

//XXX-AttackTools/csrf/index.html

//La pagina simulava un sito innocuo dedicato agli orsi, ma conteneva un link nascosto che eseguiva automaticamente la richiesta:

http://internal.admin:8000/admin/2/set-admin

//tramite JavaScript dopo alcuni secondi.
//L’attacco sfruttava la sessione autenticata dell’amministratore già attiva nel browser.

//Vulnerabilità confermata
//Aprendo la pagina malevola mentre era attiva una sessione admin:
//* il browser eseguiva automaticamente la richiesta GET;
//* il server tentava di eseguire l’azione amministrativa;
//* veniva dimostrata la possibilità di sfruttare la sessione admin tramite una pagina esterna.
//Questo conferma la presenza della vulnerabilità CSRF.

/
//Per mitigare la vulnerabilità:
//* le route amministrative sono state modificate da GET a PATCH;
//* le operazioni sensibili non sono più eseguibili tramite semplici link;
//* Laravel può ora applicare correttamente la protezione CSRF tramite token.
//Le route vulnerabili:

//Route::get(...)

//sono state sostituite con:

//Route::patch(...)


//Verifica finale della mitigazione
//Dopo la mia modifica delle route:
//* la pagina CSRF non è più riuscita a eseguire correttamente l’azione amministrativa;
//* le richieste GET verso endpoint PATCH vengono bloccate;
// l’attacco CSRF risulta mitigato.
//La protezione che ho implementato impedisce quindi a siti esterni di sfruttare //automaticamente la sessione autenticata dell’amministratore.

/*
|--------------------------------------------------------------------------
| Challenge 3 - SSRF (Server-Side Request Forgery)
|--------------------------------------------------------------------------
|
| Vulnerabilità:
| Il controller amministratore effettuava una richiesta HTTP verso
| un servizio interno utilizzando HttpService.
|
| Questo comportamento poteva rappresentare un rischio SSRF,
| permettendo potenzialmente richieste server-side verso risorse
| interne dell’infrastruttura.
|
| Endpoint coinvolto:
| http://internal.finance:8001/user-data.php
|
| Mitigazione che ho implementato:
| - controllo degli endpoint autorizzati;
| - gestione delle eccezioni;
| - logging dei tentativi sospetti;
| - blocco delle richieste non consentite.
|
| Verifica finale:
| Dopo la mitigazione l’applicazione registra nei log Laravel
| eventuali tentativi sospetti e impedisce richieste arbitrarie
| verso host interni non autorizzati.
|
*/

## Challenge 4SSRF - tramite richiesta Livewire

Durante il processo di creazione di un articolo, la richiesta Livewire ha esposto dati finanziari interni provenienti da:

http://internal.finance:8001/user-data.php

Attraverso la scheda “Rete/Network” degli strumenti sviluppatore del browser è stato possibile intercettare la risposta JSON restituita dal servizio interno.

La risposta conteneva informazioni sensibili come:

- username
- saldo dei conti
- transazioni
- numeri di carta di credito
- CVV

Questo ha confermato lo sfruttamento con successo di una vulnerabilità SSRF tramite la richiesta HTTP interna gestita dall’`AdminController`.

## Challenge 5 - Stored XSS
// È stato inserito nel campo testo dell’articolo il payload:
// <script>alert('hacked')</script>
//
// Il sistema ha permesso il salvataggio del contenuto senza blocchi lato backend.
//
// Successivamente:
// - l’articolo è stato approvato dal revisore
// - pubblicato nella parte pubblica del blog
// - visualizzato da un utente guest
//
// Il payload viene mostrato nella pagina pubblica del sito.
// Tuttavia il browser non esegue il JavaScript perché il contenuto viene renderizzato escaped dal frontend.
//
// Questo comportamento dimostra comunque:
// - validazione lato server assente o debole
// - possibilità di memorizzare payload malevoli nel database
// - potenziale rischio Stored XSS
//
// Possibili impatti:
// - esecuzione di JavaScript arbitrario
// - furto cookie/sessioni
// - impersonificazione utenti
// - defacement pagina
//
// Mitigazione consigliata:
// - sanitizzazione lato server
// - escaping output Blade
// - whitelist tag HTML consentiti
//
// Esempio mitigazione:
//
// $cleanText = strip_tags($request->text);
//
// oppure:
//
// $cleanText = htmlspecialchars($request->text);
//
// Dopo la mitigazione il payload viene mostrato solo come testo
// e non viene eseguito dal browser.

// Challenge 6 - Mass Assignment
//
// Nel modello User erano presenti tra i campi fillable anche:
// - is_admin
// - is_revisor
// - is_writer
//
// Questo rappresentava una vulnerabilità di tipo Mass Assignment,
// perché un utente malevolo avrebbe potuto manipolare una request HTTP
// aggiungendo campi non previsti dal form, ad esempio:
//
// is_admin=1
//
// ottenendo privilegi amministrativi senza autorizzazione.
//
// Mitigazione:
// Sono stati rimossi da $fillable tutti i campi relativi ai ruoli.
//
// Ora il modello User permette il mass assignment solo per:
// - name
// - email
// - password
//
// Dopo la mitigazione eventuali campi come:
// is_admin
// is_revisor
// is_writer
//
// inviati manualmente dall’utente vengono ignorati dal framework
// e non possono modificare i privilegi dell’account.

## BONUS - Clickjacking
//
// Ho  creato un attacco di Clickjacking tramite iframe,
// caricando il sito all’interno di una pagina malevola.
//
// Vulnerabilità:
// Il browser permetteva il rendering del sito dentro un iframe,
// consentendo ad un attaccante di sovrapporre elementi ingannevoli
// e indurre l’utente a cliccare su contenuti invisibili.
//
// Mitigazione:
// È stato implementato un middleware Laravel che aggiunge:
//
// X-Frame-Options: DENY
//
// a tutte le risposte HTTP.
//
// Dopo la mitigazione il browser blocca il caricamento del sito
// dentro iframe esterni, impedendo l’attacco di Clickjacking.

## progetto finito!!!
