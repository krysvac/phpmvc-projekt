# PHPMVC Project

Detta repo är mitt slutprojekt i kursen DV1486   

## Installation

 - Ladda ner repot till en webbserver med php >= 5.6

```
https://github.com/Vesihiisi/dv1486-project.git
```

2. Projektet har några paket från 3e part som behov för att kunna fungera. Dessa finns att see i composer.json filen samt composer.lock som tillsammans ska ge dig all relevant information. För att uppdatera kör en uppdatering med composer

```
composer install
```

3. Projektet använder mysql, konfigurationen för det finns i app/config/config_mysql.php.

4. För att initialisera projektet, gå in på sidan och besök webroot/setup alternativt webroot/index.php/setup