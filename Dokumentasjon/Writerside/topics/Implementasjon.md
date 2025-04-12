# Dokumentasjon

## Gruppe

**Gruppemedlemmer:**
:
* Ayub Abdirazak Ali - ayubaa@hiof.no
* Jon Petter Harwiss - jonpha@hiof.no
* Mahamoud Ibrahim - mahamoui@hiof.no
* Mats Hansen - math@hiof.no
* Sander Thorstein Nilsen - sandertn@hiof.no

## Steg 1
Vi brukte en del AI for å utvikle nettsiden ut ifra kravene, både for frontend og backend. Vi lagde en database med tabeller med tanke på minimumskraven, og lagde forhold mellom tabellene.

Nettsidene har følgende funksjoner:
* Vi lagde en funksjon hvor man kan registere seg som en student eller en foreleser med de nødvendige dataene ut ifra minimumskraven.
    * Under oppretting av en foreleser, så kan man lage et kurs med kurs pin.
* Vi lagde betinget logikk for registering av forelesere og studenter, så gitt bruker må fylle ut alle felter før registeringen sendes inn.
* Studenten eller foreleser kan logge seg inn. Webserveren kan sammenlignde brukerens oppgitte passord mot hasha passordet fra databasen til gitt epost.
* Student har muligheten til å opprette melding til gitt kurs som en foreleser kan lese og svare tilbake (Kun siste svarte melding vises)
* Studenten og foreleseren har muligheten til å bytte passordet etter å ha logget seg inn.
* Vi la til en glemt-passord funksjon hvor du kun trenger eposten for å tilgangen til å endre passordet igjen. Vi bruker PHPMailer som sender en epost med et link.
* Gjestebruker har muligheten til en kurs med alle meldingene ved bruk av en pin kode samt legge til en kommentar. På denne siden, kan han/hun se kurs navnet, kurs koden, og bildet til foreleseren i kurset
* Gjestebruker kan også rapportere meldinger med begrunnelse.
* Våres 404 håndtering har et easter egg. Lykke til ᓚᘏᗢ
* Input felter har noen form for validering og sanitering
* For nå, så er frontend og backend i et. Dette skal endret snart™️

Vi bruker docker for å teste nettsiden.

## Steg 2

| Funksjonalitet                         | Student | Foreleser | Administrator | Gjestebruker |
|-----------------------------------------|---------|-----------|--------------|--------------|
| **Brukerregistrering**                  | ✅      | ✅        | ❌           | ❌           |
| Registrere navn og e-post               | ✅      | ✅        | ❌           | ❌           |
| Registrere studieretning/studiekull      | ✅      | ❌        | ❌           | ❌           |
| Registrere bilde                         | ❌      | ✅        | ❌           | ❌           |
| Angi undervisningsemne ved registrering  | ❌      | ✅        | ❌           | ❌           |
| **Autentisering og kontoadministrasjon** |         |           |              |              |
| Logge inn                                | ✅      | ✅        | ✅           | ❌           |
| Bytte passord                            | ✅      | ✅        | ❌           | ❌           |
| Glemt passord                            | ✅      | ✅        | ❌           | ❌           |
| **Meldinger og kommunikasjon**           |         |           |              |              |
| Sende anonym melding om et emne          | ✅      | ❌        | ❌           | ❌           |
| Lese egne meldinger og svar              | ✅      | ❌        | ❌           | ❌           |
| Lese meldinger fra studenter i egne emner| ❌      | ✅        | ❌           | ❌           |
| Svare på studentmeldinger (ett svar)     | ❌      | ✅        | ❌           | ❌           |
| **Administratorfunksjoner**              |         |           |              |              |
| Finne ut hvem som har sendt en melding   | ❌      | ❌        | ✅           | ❌           |
| Slette/endre student- og ansattbrukere   | ❌      | ❌        | ✅           | ❌           |
| Slette meldinger og svar                 | ❌      | ❌        | ✅           | ❌           |
| Se rapporterte meldinger                 | ❌      | ❌        | ✅           | ❌           |
| **Gjestebrukerfunksjoner**               |         |           |              |              |
| Se meldinger og svar med PIN-kode        | ❌      | ❌        | ❌           | ✅           |
| Rapportere upassende meldinger           | ❌      | ❌        | ❌           | ✅           |
| Kommentere på meldinger                  | ❌      | ❌        | ❌           | ✅           |
| **Mobilapp-funksjoner (kun for studenter)** | ✅ (uten passordfunksjoner) | ❌ | ❌ | ❌ |
| **API-dokumentasjon**                     | ❌      | ❌        | ✅           | ❌           |

[Risk Management regneark](https://docs.google.com/spreadsheets/d/1ZZaXRinwKW2zdoBFIOjep6W1I5B51jLRdOeJP1Hkgjw/edit?gid=0#gid=0)

### Det som ble levert
[Repo av det som ble levert](https://github.com/Datasikkerhet-i-utvikling-og-drift-2025/Prosjekt)

Vi hadde i utgangspunktet en del sikkerhets tiltak på plass allerede i steg 1, vi hadde blandt annet prepared statements på plass, litt input validering og sanitering av data. 
Dette var egentlig nok til at ingen av gruppene greide å hacke tjenesten vår. Vi hadde også en enkel variant av logg funskjonalitet som skrev loggene til en .log fil. Samt PHPMailer for å sende reset link til å bytte passord.

Så det vi endte med å levere er egentlig steg 1 med https og ekstra sikkerhets headere fra serveren.

### Det vi har prøvd på men ikke kom i mål
[Repo med alt vi har prøvd men ikke rakk å bli ferdig med](https://github.com/Datasikkerhet-i-utvikling-og-drift-2025/Prosjekt/tree/refactor/WebApp)

Vi var litt for ambisiøse når det kom til steg 2, vi kastet hele backenden og laget den helt fra scratch. Vi bet over mer enn det vi klarte å tygge og kom ikke helt i mål til fristen.
Vi kom veldig langt og har fått til veldig mye, men var såpass med funksjonalitet som ikke var helt 100% ferdig at vi så oss nødt til å levere steg 1 på nytt, men med oppdaterte server instillinger.

* Vi har implementer graylog og brukt den til å logge det som skjer i backenden
* Vi har laget forbedrede versjoner av input validering og sanitering.
* Vi har laget rutiner for alle de forskjellige spørringene til databasen.
* Vi har begrenset hvilke bruker roller som kan utføre spesifikke spørringer.
* Vi har lagt til en sikrere sesjonshåndtering 
  * Den begrenser antall innlogginsforsøk til 3 forsøk
  * Sesjonen utløper etter 30 dager
  * Har funksjonalitet for fingerprinting
* API-et krever en api nøkkel
* Vi har en JWT token, men vi har ikke brukt den til noe fornuftig
* I tillegg til at vi har prøvd å implementere alt i kravspesifikasjonen på nytt så sikkert som vi klarte.

På bildet under kan du se en skisse av strukturen på det vi har prøvd å sette opp, vi har satt det opp som 2 micro tjenester, en for backend og en for frontend, også kommuniserer dem sammen gjennom api-et.

![Alt text](../../pictures/DatasikkerhetIUtviklingOgDriftArkitektur.png)

