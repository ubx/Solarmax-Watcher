<?php
/*
 Simple solarmax visualizer php program written by zagibu@gmx.ch in July 2010
This program was originally licensed under WTFPL 2 http://sam.zoy.org/wtfpl/
Improvements by Frank Lassowski flassowski@gmx.de in August 2010
This program is now licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html

language file for SolarMax Watcher

nederlands									by Rene Essink
español											by Guillermina Pepe
italiano										by Giovanna Giambenedetti
english, deutsch, français	by Frank Lassowski

more translations welcome :-) #######
*/

/* operational codes - there are a lot more, but I don't know them ;-) */

$_20001de = "Service";
$_20002de = "Zu wenig Einstrahlung";
$_20003de = "Anfahren";
$_20004de = "Betrieb auf MPP";
$_20005de = "Ventilator an";
$_20006de = "Max. AC-Einspeiseleistung";
$_20007de = "Temperaturüberschreitung";
$_20008de = "Netzbetrieb";
$_20009de = "Max. DC-Eingangsleistung";

$_20001en = "service";
$_20002en = "irradiance too low";
$_20003en = "startup";
$_20004en = "MPP tracking";
$_20005en = "fan on";
$_20006en = "maximum power";
$_20007en = "temperature too high";
$_20008en = "main operation";
$_20009en = "maximum DC input";

$_20001nl = "Service";
$_20002nl = "Te weinig instraling";
$_20003nl = "Opstarten";
$_20004nl = "Bedrijf op MPP";
$_20005nl = "Ventilator draait";
$_20006nl = "Max. AC-uitgangsvermogen";
$_20007nl = "Temperatuur te hoog";
$_20008nl = "Hoofdschakelaar";
$_20009nl = "Max. DC-ingangsvermogen";
$_20010nl = "Max. AC-uitgangsvermogen";

$_20001fr = "service";
$_20002fr = "ensoleillement insuffisant";
$_20003fr = "démarrage";
$_20004fr = "MPP tracking";
$_20005fr = "ventilateur activé";
$_20006fr = "puissance maximale";
$_20007fr = "température trop élevée";
$_20008fr = "sur secteur";
$_20009fr = "maximum DC input";

$_20001es = "service";
$_20002es = "irradiación insuficiente";
$_20003es = "arrancando";
$_20004es = "MPP tracking";
$_20005es = "ventilador encendido";
$_20006es = "potencia máxima";
$_20007es = "temperatura demasiado alta";
$_20008es = "operación en red";
$_20009es = "entrada CC máxima";

$_20001it = "servizio";
$_20002it = "irradiamento troppo basso";
$_20003it = "avvio";
$_20004it = "inseguitore del punto di massima potenza";
$_20005it = "ventilatore acceso";
$_20006it = "potenza massima";
$_20007it = "temperatura troppo alta";
$_20008it = "operazione principale";
$_20009it = "immissione di corrente diretta massima";


/* main window strings */

$text1de = "aktuelle Einspeiseleistung:";
$text2de = "Ertrag heute:";
$text3de = "momentane Temperatur des WR:";
$text4de = "Monatsertrag:";
$text5de = "momentane DC Spannung (String 1 / 2 /3 ):";
$text6de = "Jahresertrag:";
$text7de = "momentaner DC Strom (String 1 /2 /3 ):";
$text8de = "Gesamtertrag:";
$text9de = "Betriebsstatus:";
$text10de = "Gesamtvergütung:";
$text11de = "Ansicht:";
$text12de = "Tag";
$text13de = "Monat";
$text14de = "Jahr";
$text15de = "heute";
$text16de = "Offline";
$text17de = "Ertrag";

$text1en = "current inverter output:";
$text2en = "yield today:";
$text3en = "current inverter temperature:";
$text4en = "monthly yield:";
$text5en = "current DC volage (string 1 / 2 / 3):";
$text6en = "annual yield:";
$text7en = "current DC current (string 1 / 2 / 3):";
$text8en = "total yield:";
$text9en = "operating status:";
$text10en = "total remuneration:";
$text11en = "view:";
$text12en = "day";
$text13en = "month";
$text14en = "year";
$text15en = "today";
$text16en = "offline";
$text17en = "yield";

$text1nl = "Actuele vermogen van de generator:";
$text2nl = "Dagproductie van de generator:";
$text3nl = "Actuele temperatuur van de omvormer:";
$text4nl = "Maandproductie van de generator:";
$text5nl = "CO<sub>2</sub> -bezuiniging loopende jaar:";
$text6nl = "Jaarproductie van de generator:";
$text7nl = "CO<sub>2</sub> -bezuiniging totaal:";
$text8nl = "Totale productie van de generator:";
$text9nl = "Actuele omvormer status:";
$text10nl = "Totaal opbrengst:";
$text11nl = "Uitzicht:";
$text12nl = "Dag";
$text13nl = "Maand";
$text14nl = "Jaar";
$text15nl = "vandaag";

$text17nl = "nl_yield";

$text1fr = "puissance injectée actuelle:";
$text2fr = "production aujourd'hui:";
$text3fr = "température actuelle:";
$text4fr = "production du mois:";
$text5fr = "annual CO<sub>2</sub> savings:";
$text6fr = "production de l'année:";
$text7fr = "total CO<sub>2</sub> savings:";
$text8fr = "production totale:";
$text9fr = "état d'exploitation:";
$text10fr = "rémunération totale:";
$text11fr = "vue:";
$text12fr = "jour";
$text13fr = "mois";
$text14fr = "année";
$text15fr = "aujourd'hui";

$text17fr = "fr_yield";

$text1es = "rendimiento de alimentación actual:";
$text2es = "rendimiento de hoy:";
$text3es = "temperatura actual:";
$text4es = "rendimiento del mes:";
$text5es = "ahorro anual de CO<sub>2</sub>:";
$text6es = "rendimiento anual:";
$text7es = "ahorro total de CO<sub>2</sub>:";
$text8es = "rendimiento total:";
$text9es = "estado de operación:";
$text10es = "remuneración total:";
$text11es = "vista:";
$text12es = "día";
$text13es = "mes";
$text14es = "año";
$text15es = "hoy";

$text17es = "es_yield";

$text1it = "produzione dell’invertitore di corrente:";
$text2it = "produzione odierna:";
$text3it = "temperatura dell’invertitore di corrente:";
$text4it = "produzione mensile:";
$text5it = "risparmio annuale di CO<sub>2</sub>:";
$text6it = "produzione annuale:";
$text7it = "risparmio totale di CO<sub>2</sub>:";
$text8it = "produzione totale:";
$text9it = "stato operativo:";
$text10it = "compenso totale:";
$text11it = "prospetto:";
$text12it = "giorno";
$text13it = "mese";
$text14it = "anno";
$text15it = "oggi";

$text17it = "it_yield";

/* strings for the graphs */

$graphday1de = "Leistung im Verlauf des Tages, Tagesertrag, DC-Spannung, DC-Strom, DC-Leistung";
$graphday2de = "gerade Linie: erwarteter Tagesertrag   Kurve: Tagesertrag";
$graphday3de = "Temperatur WR";
$graphday4de = "Generatorspannung ";
$graphmonth1de = "Ertrag in kWh pro Tag";
$graphmonth2de = "erwarteter Tagesertrag";
$graphmonth3de = "durchschnittlicher Tagesertrag";
$graphyear1de = "Ertrag in kWh pro Monat";
$graphyear2de = "erwarteter Monatsertrag";
$switch_arrayde = array("Ertrag \n", "akkumulierter Ertrag \n", "Vorhersage \n", "Spannung \n", "Strom \n", "Leisung \n", "Gitter </p>\n</div>\n", "Zahlen \n", "Vorhersage \n", "Durchschnitt \n", "Gitter </p>\n</div>\n", "Zahlen \n", "Prozent \n", "Gitter </p>\n</div>\n");

$graphday1en = "power, todays yield, DC-voltage, DC-current, DC-power";
$graphday2en = "straight line: expected daily yield   curve: yield today";
$graphday3en = "inverter temperature";
$graphday4en = "generator voltage";
$graphmonth1en = "yield in kWh per day";
$graphmonth2en = "expected daily yield";
$graphmonth3en = "average daily yield";
$graphyear1en = "yield in kWh per month";
$graphyear2en = "expected monthly yield";
$switch_arrayen = array("yield \n", "accumulated yield \n", "prediction \n", "voltage \n", "current \n", "power \n", "grid </p>\n</div>\n", "numbers \n", "prediction \n", "average \n", "grid </p>\n</div>\n", "numbers \n", "percent \n", "grid </p>\n</div>\n");

$graphday1nl = "vermogen in watt in de daagverloop, dagopbrengst, temperatuur omvormer, dc-spanning";
$graphday2nl = "rechte lijn: verwachte dagproductie, curve: dagproductie ";
$graphday3nl = "temperatuur omvormer ";
$graphday4nl = "spanning van de generator";
$graphmonth1nl = "energie in kWh per daag";
$graphmonth2nl = "verwachte dagopbrengst";
$graphmonth3nl = "gemiddelte dagopbrengst";
$graphyear1nl = "energie in kWh per maand";
$graphyear2nl = "verwachte maandopbrengst";
$switch_arraynl = array("Opbrengst \n", "geakkumuleerde Opbrengst \n", "Voorspelling \n", "Spanning \n", "Temperatuur \n", "Net </p>\n</div>\n", "Cijfers \n", "Voorspelling \n", "Gemiddelde \n", "Net </p>\n</div>\n", "Cijfers \n", "Percent \n", "Net </p>\n</div>\n");

$graphday1fr = "power in Watts, todays yield, inverter temperature, generator voltage";
$graphday2fr = "ligne droite: rendement attendu, courbe: production aujourd'hui";
$graphday3fr = "température d'onduleur";
$graphday4fr = "tension du générateur solaire";
$graphmonth1fr = "rendement quotidien en kWh";
$graphmonth2fr = "rendement attendu quotidien";
$graphmonth3fr = "rendement moyen quotidien";
$graphyear1fr = "rendement mensuel en kWh";
$graphyear2fr = "rendement attendu mensuel";
$switch_arrayfr = array("rendement \n", "rendement accumulés \n", "prévisions \n", "tension \n", "température \n", "grille </p>\n</div>\n", "nombres \n", "prévisions \n", "moyenne \n", "grille </p>\n</div>\n", "nombres \n", "pour cent \n", "grille </p>\n</div>\n");

$graphday1es = "potencia eléctrica en Vatios, producción del día, temperatura del ondulador, voltaje del generador solar";
$graphday2es = "línea recta: producción diaria esperada, curva: producción del día";
$graphday3es = "temperatura del ondulador";
$graphday4es = "voltaje del generador";
$graphmonth1es = "producción diaria en kWh";
$graphmonth2es = "producción diaria esperada";
$graphmonth3es = "producción promedio por día";
$graphyear1es = "producción en kWh por mes";
$graphyear2es = "producción mensual esperada";
$switch_arrayes = array("producción \n", "producción acumuladas \n", "pronóstico \n", "voltaje \n", "temperatura \n", "la red </p>\n</div>\n", "números \n", "pronóstico \n", "promedio \n", "la red </p>\n</div>\n", "números \n", "por ciento \n", "la red </p>\n</div>\n");

$graphday1it = "Potenza in Watt, produzione odierna, temperatura dell’invertitore, voltaggio del generatore";
$graphday2it = "linea retta: produzione giornaliera prevista   curva: produzione odierna";
$graphday3it = "temperatura dell’invertitore";
$graphday4it = "voltaggio del generatore";
$graphmonth1it = "produzione in kWh al giorno";
$graphmonth2it = "produzione giornaliera prevista";
$graphmonth3it = "produzione giornaliera media";
$graphyear1it = "produzione in kWh al mese";
$graphyear2it = "produzione mensile prevista";
$switch_arrayit = array("produzione \n", "produzione accumulati \n", "previsione \n", "voltaggio \n", "temperatura \n", "griglia </p>\n</div>\n", "numeri \n", "previsione \n", "media \n", "griglia </p>\n</div>\n", "numeri \n", "per cento \n", "griglia </p>\n</div>\n");


/* error strings */

$error1de = "Ungültige Werte in den Datumsfeldern ";
$error2de = "Ungültiges Datum: ";
$error3de = "Keine Daten für diese Periode.";

$error1en = "invalid data in date fields ";
$error2en = "invalid date: ";
$error3en = "No data for this period.";

$error1nl = "ongeldige datumswaarden ";
$error2nl = "ongeldig datum: ";
$error3nl = "geen data voor deze periode.";

$error1fr = "invalid data in date fields ";
$error2fr = "invalid date: ";
$error3fr = "No data for this period.";

$error1es = "datos incorrectos en los campos de fecha ";
$error2es = "fecha incorrecta: ";
$error3es = "no existe información para este periodo.";

$error1it = "dati non validi nel campi data ";
$error2it = "data non valida: ";
$error3it = "nessun dato per questo periodo.";
?>
