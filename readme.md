Fleenk
=============
Webová aplikace pro organizaci sběru výpočetní techniky při rozsáhlých akcích

O programu
-----------
Tato práce byla vytvořena jako praktická část bakalářské práce pro Vysokou školu Ekonomickou. 
Jedná se o aplikaci nahrazující dispečera jako informační kanál při komunikaci mezi týmy techniků a klienty.  

Požadavky apliakce
------------
 - PHP 7+
 - Apache
 - SMTP server
 - MariaDb
 - Composer

Instalace
------------
Při instalaci se předpokládá s již rozběhlými servery s veškerými požadovanými aplikacemi.
 1. Pro získání knihoven spusťe composer příkazem 
 `$:Composer install`
 1. Ujistěte se že PHP má možnost psát do složek temp a log. Na centos linux napřiklad je zapotřebí provést tyto příkazy:
 `$:chcon -t /PathToFile/Fleenk/src/log/ -R` a `/var/www/html/Fleenk/src/temp/ -R`
 1. Pro propojení se SMTP serverem a Apache vyplňte konfiguraci nacházející se v common.neon
 1. Vytvořte tabulky v databázi za pomoci kódu nalézajícím se v CreateScript.sql
 1. Nahrajte základní hodnoty do databáze za pomoci kódu nalézajícím se v InsertScript.sql  
 Základní přihlašovací údaje pro administrátora jsou "adm@adm.cz" a heslo "Admin".  
 Toto heslo si ihned změňte kliknutím na tlačítko s nápisem Admin.
 1. Do šablony Template.xlsx přeneste potřebná data, a tuto šablonu následně vložte na stránce Database do aplikace. 
 Pokud jste data správně vložily, importují se do databáze.
 1. Vaše aplikace je připravena pro použití, dočasná hesla byla automaticky zaslána uživatelům.


Changelog
--------------

### Patch-2

Přidáno:
 - Management stránka pro optimalizování vytíženosti týmů

### Patch-1

Přidáno:
 - Našeptávač Flex-Datalist pro stránky týmů
 - Pokročilejší hlášení chyb při vkládání dat z Excelu

Opraveno:
- Nefunkční dropdown navigační menu pro Klienta a Technika
 - Chybná chybová hláška při vkládání nových dat z Excelu
 - Jazyková nekonzistence vyskakovacích oken.
 - Chybějící adresáře log a temp
 - Chybný CamelCase v InsertScriptu
