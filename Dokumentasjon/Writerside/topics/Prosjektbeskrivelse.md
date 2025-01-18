# Prosjektoppgave

I dette emnet skal det gjennomføres en større utviklingsprosjekt i grupper. Prosjektet har til hensikt å gi praktisk øvelse på det man lærer, samt få litt eget perspektiv og trening på å se temaet sikkerhet i utvikling og drift.

*Prosjektoppgaven er når den blir gjennomført et bevis på at studenten behersker de mer praktiske sidene av kurset. Man får ikke ta eksamen dersom arbeidskravet ikke blir bestått, og således kan dette arbeidskravet sees på som en "indirekte deleksamen". Med dette bakteppet vil man også kreve mer av dette arbeidskravet for å få bestått enn i enkelte arbeidskrav i andre emner som mer kan sees på som "tvungen øving" eller "tvungen forberedelse til eksamen".  Eksamensoppgaven kan komme til å be dere reflektere over prosjektet som helhet eller de delene du har bidratt spesielt mye på i prosjektet.*

Prosjektoppgaven beskrives i dette dokumentet, men hver delinnlevering skal leveres i egne assignments/oppgaver.


## Gruppe
1. Gruppestørrelsen er ideelt 5 personer. Så sant dette "går opp" med antall studenter i emnet, så er gruppestørrelsen ufravikelig.
2. Før dere danner grupper, tenk over arbeidsinnsats, forkunnskaper, ambisjoner og ikke minst tidspunkt for arbeidsøkter. Forsøk å finne gruppemedlemmer som matcher på disse punktene. Hver gruppe bør ha ett medlem med litt Linux-erfaring.  Det er svært viktig at dere ikke bare danner første og beste gruppe, samt at dere avklarer litt forventninger og arbeidsinnsats hos evt. gruppemedlemmer dere ikke kjenner.
3. **Grupper kan ikke endres, men enkeltpersoner kan kastes ut (av emnet) - Vurder <u>nøye</u> sammensetningen**
4. **Dere er alle ansvarlig for at gruppa fungerer, samt å si i fra <u>tidlig</u> om problemer.**
5. Å ikke si i fra med EN GANG problemer oppstår eller ikke tillate noen å delta kan også gjøre at man ikke får godkjent prosjektet, selv om man faglig bidrar nok selv.
6. Gruppe registreres under people (/personer)  og fanen "Project groups". Ikke registrer gruppe før dere er nok personer, og følg kronologisk rekkefølge i gruppenummerering.

## VIKTIG!
> Følgende handlinger vil medføre at prosjektet underkjennes, og man IKKE får ta eksamen:
{style="warning"}

* Utøve penetrasjonstesting/sikkerhetstesting/hacking på annen gruppes virtuelle servere utenom de forhåndsdefinerte tidsperiodene gitt av foreleser. (Det gjelder også før man rekker bytte defaultpassord)
* Utøve hacking ved hjelp av servernes hoved-amdin-konto.
* Kjøre verktøy/angrep mot infrastruktur/maskiner på skolens systemer. Dette innebærer også maskinen som holder på de virtuelle maskinene. <u>Dette gjelder også uhell</u>. Er du i tvil om hva et verktøy gjør, så spør. Sjekk alltid 2-3 ganger ekstra at verktøy kjøres mot rett maskin/IP-adresse.
* Utvikle prosjektet slik at det angriper/skader de som sikkerhetstester det (f.eks. at Andorid-appen gjør skade på testernes telefoner/maskiner)
* Alle gruppemedlemmer skal bidra likt i utviklingen og gjennomføringen av prosjektet **(innsats, ikke ferdighet)** - Enkelte gruppemedlemmer kan på bakgrunn av dette få ikke bestått, selv om gruppen og prosjektet får bestått.
* At man blir kastet ut av en gruppe grunnet manglende innsatsas.

## Praktisk
> I dette emnet er grupper avhengige av hverandres prosjekter. Jeg/vi ønsker ikke å bruke tid på de som lager ekstra mye krøll med innleveringsfrister osv. For at dette emnet skal være gjennomførbart på en "smooth" måte er vi derfor avhengig av **<u>noen absolutte regler</U>**:

1. Dere skal selv ha lest hele denne prosjektoppgaven samt informasjonen i de ulike assignments FØR dere starter på oppgaven.
2. Frister **skal** holdes. Eneste unntak er alvorlig langstidssykdom. Overholdes ikke fristene er dere ute av emnet. Sørg for å planlegge arbeid både i dette og andre emner, slik at dere kan levere til fristen, også dersom noe uforutsett oppstår. Sørg da også for å planlegge og forhindre at det dukker opp problemer i siste øyeblikk ved levering. Skal en frist mot all formodning forlenges må det avklares med fagansvarlig i god tid FØR fristen går ut.
3. Alle på gruppa skal bidra i prosjektet. Selvsagt har ikke alle like forutsetninger og forkunnskaper, men deltakelse betyr nødvendigvis ikke at man skal dra alt arbeidet faglig. Det betyr at man er til stede, er interessert og aktiv samt gjør det man kan for å bidra med sine styrker. Om noen henger "bakpå" med sine bidrag eller ikke møter ved arbeidsøkter er både personene selv og gruppa pliktig å si i fra til foreleser så snart problemene oppstår. Dersom ikke gruppa varsler foreleser underveis, kan prosjektet underkjennes av den grunn.
4. Hver delinnlevering vil godkjennes (eller ikke godkjennes). Alle delinnleveringer må være godkjent for å få gå opp til eksamen. Det vil ikke være anledning til å forbedre en delinnlevering etter at fristen er gått ut og oppgaven ikke er godkjent. Det er derfor viktig at dere spør og får hjelp FØR fristen dersom dere er usikre på om ting er bra nok til å få godkjent eller har problemer.
5. Veiledning vil primært bli gitt via Discord og kanalen #veiledning. Still spørsmål der, så vil det avgjøres om hjelpen kan gis via chat, videomøte eller fysisk oppmøte.
6. Dere får hjelp og veiledning til alt av problemer, men det forutsetter at dere har:
   1. Gjort et realt forsøk på å løse det selv
   2. Diskutert problemet innad i gruppa først.
7. Husk at veiledning også (veldig gjerne) kan være å finne/diskutere beste løsning, ikke bare fikse ting som ikke fungerer...


## Prosjektet - kravspesifikasjon
> Det skal lages et system (nettside) for delvis anonyme (tilbake)meldinger til en foreleser i emner ved høgskolen.
>
> Elementer i grått kan nedprioriteres, det er imidlertid ønskelig at det i gjøres forsøk også på disse. Start imidlertid med elementer i sort. Selve strukturen i systemet og etter hvert "sikkerhetstankengangen" bør ta høyde for alle funksjonene. **<u>Mangler elementer i sort vil i utgangspunktet ikke oppgaven godkjennes.</u>**


### En student skal:
* **Kunne registrere seg ( navn, e-post,** studieretning og studiekull **)**
* **Kunne logge inn**
* Kunne bytte passord
* Utføre "glemt passord"
* **Kunne sende melding ang. et ønsket emne/fag, men forbli anonym (studentene kan velge fritt blant alle registrerte emner)**
* Se evt. svar fra forelesere på tidligere sendte meldinger (trenger ikke håndtere lest/ulest)


### En foreleser skal:
* **Kunne registrere seg (navn, e-post, bilde)**
* **Under registrering angi hvilket emne man underviser, samt PIN-kode for emnet (se seksjonen under). Emnet opprettes så automatisk.**  For de som vil ha en større utfordring, kan dere la en foreleser opprette/undervise flere emner
* **Kunne logge inn**
* **Kunne bytte passord**
* **Utføre "glemt passord"**
* **Lese meldinger fra studenter i emnet(/er) man selv underviser**
* **Svare på meldinger fra studenter (kun ett svar pr. melding)**


### En administrator skal:
* Logge inn
* Kunne finne ut hvem som har sendt en melding
* Slette og endre studentbrukere og ansattbrukere
* Slette meldinger og tilhørende svar
* Se rapporterte meldinger (se neste seksjon)


### En gjestebruker (anonym/ikke innlogget) skal:
* **Kunne se alle meldinger (og svar) for et valgt emne, men kun de man kjenner en firesifret PIN-kode til. Visningssiden skal inneholde emnekode og emnenavn, samt navn og bilde av foreleser..**
* **Kunne rapportere en upassende melding sendt fra en student**
* **Kunne legge inn en kommentar på meldinger fra studentene.**
* Dersom en foreleser eller student besøker den "åpne siden"  mens de er innlogget, skal en handlinger knyttes til brukeren.


### App:
* I tillegg til en nettside skal det også utvikles en mobilapp for studenter som har de samme mulighetene som nettsiden for innlogget student, unntatt glemt passord og passordbytte.
* **<u>For å skalere ned prosjektet litt, så trenger dere ikke lage selve mobilappen, kun API som den tenkte appen skal benytte.</u>**
* **Det skal lages et URL-basert API på serveren, og dokumenteres hvordan dette benyttes. Denne dokumentasjonen skal ligge på http://ip-adresse/stegX/api (X er da 1 eller 2, se under)**
* Lages en app, skal det være mulig å laste ned Android-appen  (apk-fil) via http://ip-adresse/stegX/app (X er da 1 eller 2, se under).

En melding skal altså være knyttet til en student i systemet, men foreleser/gjester skal ikke vite eller kunne finne ut hvem det er som har sendt meldingen.

Bildet av foreleser skal IKKE lagres i en database, men på filområdet i webserveren.

Dere trenger <u>ikke</u> ta høyde for at et emne kan ha flere forelesere.

Systemet skal utvikles i PHP med en Apache webserver og en MySQL-database.  Det er **IKKE** tillatt å benytte rammeverk (til nød for CSS/design). Evt. app skal utvikles i Android Studio med Java eller Kotlin som språk.

Nedprioriter design av nettside til et <u>absolutt minimum</u>. Det er funksjonaliteten vi her skal ha på plass. Nettsiden skal imidlertid ha et "fornuftig brukergrensensitt"

Systemet skal plasseres på en dedikert virtuell-server som tildeles studentgruppen (mer info lenger ned i beskrivelsen).

Alt som ikke er nevnt i oppgaveteksten må dere ta egne valg om. Selv om det muligens er fristende å "ta av" med funksjoner, så prioriter først og fremst at dere dekker kravene.

> **MERK: Det er ikke forventet at dere kan Linux, PHP osv. fra før i prosjektet. Litt av poenget er å simulere en arbeidssituasjon dere må benytte verktøy, progspråk osv. som dere ikke behersker (og de sikkerhetsutfordringer det medfører). Dette vil bli en utfordring for dere, men se på det slik at dere også lærer litt av disse tingene som en bonus... Det er imidlertid en forutsetning for emnet at man har gjennomført minst to programmeringsemner, og dermed kan programmere.**
{style="warning"}


## Prosjektserver
Maskinene ligger på IP-adressene: 158.39.188.203 - 211:
* **Gruppe 1:** 158.39.188.203
* **Gruppe 2:** 158.39.188.204
* **Gruppe 3:** 158.39.188.205
* ...
* **Gruppe 9:** 158.39.188.211

(Kan endres underveis om noen grupper "tuller til" serveren)

Man må være tilkoblet med VPN eller være fysisk på skolens nett for å få tilgang.  Serveren administreres via SSH (f.eks. [putty](https://www.putty.org/))

Root-brukeren heter "datasikkerhet" passord får dere ved å kontakte foreleser.

> **NB! Dere er selv ansvarlige for å jevnlig ta backup av programkode, databaseinnhold og konfigurasjonsfiler. Det kan være lurt å arbeide med dette lokalt (evt. git?), og så deploye til server. Det kan også være veldig lurt å vedlikeholde en "changelog" for alt dere gjør av endringer på installert programvare og konfigurasjon.**
> {style="warning"}

> **NB! I stedet for en "test alt mulig"-strategi, er det bedre å sjekke nøye før dere installerer/konfigurerer severen. Dette vil dere spare tid på i det lange løp.**
{style="note"}

> **NB! Når dere senere i emnet skal aktivere brannmur, sørg for å være HEEEEEEEEELT sikker på at port 22 (SSH) er åpen i konfigurasjonen til brannmuren før brannmuren aktiveres.**
{style="warning"}


## Steg 1
Gruppen skal i løpet av en intensiv periode i starten av emnet utvikle første versjon av prosjektet. Det er <u>svært dårlig tid</u>, noe som skal simulere hvordan sikkerhet nedprioriteres også ute i bedrifter pga. knappe ressurser (tid, ansatte, penger, etc). Dere må komme i mål med minimumsløsningen, så her gjelder det å fordele oppgaver og jobbe "hver for seg" på sin del. ( i motsetning til resten av prosjektet)

**<u>Dere må også ta mange "kjappe løsninger" for å komme i mål og basere dere mye på klipp og lim av kode/fremgangsmåter dere finner på nett, bruke chatGPT, basere dere på tutorials osv.  Altså er "alt lov" innenfor klipp og lim, og faktisk forventet (dette er vel første og siste gang i studiet...)</u>**

**Dere må nok forvente at steg 1 tar "uforholdsmessig mye tid" til å være ett emne i ukene det pågår, men til gjengjeld skjer det lite i andre emner samtidig, og det blir mer "slækk" i dette emnet etter hvert.**

**<u>For deres egen lærings del, og for andre grupper: Ikke tenk noe mer på sikkerhet nå enn dere ville gjort dersom dette var et prosjekt i et annet emne. Ettersom dere vet hva veien videre er, er det fort gjort å "lure seg selv" litt her, men prøv å vær helt fokusert på "lage prosjektet" fremfor "vi skal lage et prosjekt de andre gruppene ikke kan knekke".</u>**

Prosjektet skal ha en "dokumentasjonsside" på http://ip-adresse/dokumentasjon. der dere i denne første innleveringen kan beskrive kort om hva som er gjort (ikke beskriv sikkerhetsløsninger, kjente hull osv., men rett og slett bare hva som er laget og med hvilke overordnede verktøy/teknikker).  Dokumentasjonssiden skal også ha navn/e-postadresser til gruppemedlemmene lett synlig.

Prosjektet **<u>SKAL</u>** ligge på adressen http://ip-adresse/steg1.

> MERK: **Dere får IKKE gjøre endringer på steg 1 etter det er levert.** Dere må imidlertid lage en "versjon 2" (steg 2) parallelt med steg 1.1 og steg 1.2.   Dette løses ved å legge den nye versjonen på http://ip-adresse/steg2, samt benytte et annet schema-navn i databasen.  Evt. endringer i config-filer gjøres når steg 2 starter (evt. om dere er tøffe nok til å kjøre flere config-filer...). Lese hele steg 2 før dere begynner, og merk dere at en "sikkerhetsvurdering" skal gjøres før dere starter å kode/endre.
{style="warning"}

> NB! Husk at dere må ta lokal backup av kode, config-filer og database, slik at prosjektet raskt kan resettes om angrepene i steg 1.1/1.2 er vellykkede...
{style="warning"}

> HUSK: Prosjektet er omfattende og det er helt nødvendig at dere i steg 1 jobber med hver deres deler parallelt, og så setter sammen.  Altså deler jobben i steg 1 inn i 5 deler... I tillegg må dere begynne i går.
{style="warning"}

## Steg 1.1 - rett etter steg 1 er levert
Forsøk å finne flest mulig svakheter i <u>andre gruppers</u> prosjekt basert hva dere kan om sikkerhet til nå (dvs. fra før). Feilene og svakhetene dere finner skal dere dokumentere i en "rapport" som leveres inn. <u>Rapportene vil bli publisert i canvas av foreleser for alle grupper.</u>  Merk at det her med "rapport" menes et enkelt tekstdokument. Innholdet er viktigere en utforming og omfang.

> Sørg for å jobbe som en hacker. Først gjør de angrepene som henter ut informasjon, så de som manipulerer informasjon og til slutt de som "ødelegger".  Vent med ødeleggende angrep til de to siste dagene, så flest mulig grupper får prøvd å angripe før serveren/prosjektet evt. begynner å halte.
{style="note"}

> NB! Dere får IKKE gjøre angrep som medfører at serveren blir utilgjengelig via SSH med brukernavn/passord som studentgruppen har.
{style="warning"}


## Steg 1.2 - rett før steg 2 starter
Forsøk å finne flest mulig svakheter i andre gruppers prosjekt basert hva dere kan om sikkerhet nå (dvs. etter en del forelesninger). Feilene og svakhetene dere finner skal dere dokumentere i en "rapport" som leveres inn. Rapportene vil bli publisert i canvas av foreleser for alle grupper.   Merk at det her med "rapport" menes et enkelt tekstdokument. Innholdet er viktigere en utforming og omfang.

> Sørg for å jobbe som en hacker. Først gjør de angrepene som henter ut informasjon, så de som manipulerer informasjon og til slutt de som "ødelegger".  Vent med ødeleggende angrep til de to siste dagene, så flest mulig grupper får prøvd å angripe før serveren/prosjektet evt. begynner å halte.
{style="note"}

> NB! Dere får IKKE gjøre angrep som medfører at serveren blir utilgjengelig via SSH med brukernavn/passord som studentgruppen har.
{style="warning"}


## Steg 2 - (Arbeides med parallelt som 1.1 og 1.2)
Basert på det dere har lært i kurset og rapporten fra "hackergruppene" i steg 1.1 og 1.2, gjør alle nødvendige (innenfor mulighetsvinduet) utbedringer av deres eget system.  Jeg har forståelse for at steg 2 kan bli enormt omfattende, så prioriter de delene dere lærer mest av selv. Husk da at å gjøre det dere alt kan/forstår (som går raskt) ikke er det dere lærer mest av. Ha uansett med en liste i dokumentasjonen over eventuelle ting dere ikke rakk/prioriterte å gjøre (så vi vet at dere vet).

Ha også fokus på at dere skal <u>logge relevante hendelser</u>.

Prosjektet **<u>SKAL</u>** ligge på adressen http://ip-adresse/steg2.


### Prosessen i dette steget skal være basert på det dere burde ha gjort i steg 1. Dere skal altså i tillegg lage:
* Et veldig enkelt system for Risk Management Framework (f.eks. excel) - Dette bør være det første dere gjør, og bør gjøres sammen hele gruppa (altså for å planlegge steg 2 før dere koder). Det er forstålig at et fullverdig RMF for hele prosjektet tar svært mye tid, så dere kan heller velge å fokusere på noen utvalgte deler og skalere ned omfanget litt. For egen læring er det bedre å gjøre noen deler bra/fullstendig enn å gjøre alt halvveis
* Gjøre noen av de ulike stegene i sikker utvikling. Igjen kan dere fokusere på noen områder i stedet for å gjøre alt komplett (som kan bli for omfattende. Som minimum må dere ha med/gjort følgende (på noen områder):
  * Lage sikkerhetskrav og abuse cases - Også dette skal gjøres FØR dere går i gang med steg 2.  Beskriv heller færre krav godt, enn mange krav halvveis.
  * Gjøre code review (med verktøy?)
  * Gjøre en enkel risikoanalyse av arkitekturen
  * Gjøre en risk-based security test (basert på krav, abuse cases mm) - denne kan gjøres "manuell" eller med verktøy slik som unit-testing  (ikke forelest) eller en kombinasjon.
  * (Penetrasjonstest er hva de andre gruppene gjorde i steg 1.1/steg 1.2)
  
Dere skal dokumentere i en <u>kort rapport</u> de teoretiske punktene over, samt hvilke <u>endringer</u> dere gjør fra steg 1, og hvorfor. Rapporten legges på "dokumentasjonssiden" på serveren.

**Legg også ut kildekoden til prosjektet og konfigurasjonsfiler på dokumentasjonssiden.**

> MERK: Dere får IKKE gjøre endringer på steg 2 etter det er levert.
{style="warning"}

> NB! Husk at dere må ta lokal backup av kode, config-filer og database, slik at prosjektet raskt kan resettes om angrepene i steg 2.1 er vellykkede...
{style="warning"}


## Steg 2.1
### Dette steget skal gjøres i to faser:
a) Forsøk å finne flest mulig svakheter i andre <u>gruppers prosjekt</u>, uten å se på kildekoden/konfigurasjonsfilene til prosjektene

b) Forsøk å finne ytterligere svakheter i <u>andre gruppers prosjekt</u>, ved å se på kildekoden/konfigurasjonsfilene til prosjektene

Feilene og svakhetene dere finner skal dere dokumentere i en "rapport" som leveres inn (angi om de ble funnet i fase a eller b). <u>Rapportene vil bli publisert i canvas av foreleser for alle grupper</u>. Merk at det her med "rapport" menes et enkelt tekstdokument. Innholdet er viktigere en utforming og omfang.

> Sørg for å jobbe som en hacker. Først gjør de angrepene som henter ut informasjon, så de som manipulerer informasjon og til slutt de som "ødelegger".  Vent med ødeleggende angrep til de <u>to siste dagene</u>, så flest mulig grupper får prøvd å angripe før serveren/prosjektet evt. begynner å halte.
{style="note"}

> NB! Dere får IKKE gjøre angrep som medfører at serveren blir utilgjengelig via SSH med brukernavn/passord som studentgruppen har.
{style="warning"}

> Parallelt skal gruppene (for sitt eget prosjekt) benytte logg-funksjoner i prosjektet til å forsøke avdekke angrep som blir forsøkt samt legge ut en rapport om hva dette avslørte på prosjektets dokumentasjonsside.

> VIKTIG: Dere skal ikke benytte svakheter i steg 1 (som fortsatt ligger på server)  eller ting dere fikk tilgang til i steg 1.1 og 1.2 nå.
{style="warning"}


## Steg 3 (frivillig)
Basert på det dere har lært i kurset og rapporten fra "hackergruppene" i steg 2.1, gjør nødvendige (innenfor mulighetsvinduet) utbedringer av deres eget system. 