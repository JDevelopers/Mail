/*
 * Constants:
 *  allTimeZone
 *  timeZoneForCountry
 *  AllTimeZonesArr
 *  Countries
 */

var allTimeZone = "" +
"<select id='defTimeZone' style='width: 300px;' name='defTimeZone'>" +
    "<option value='1'>(GMT-11:00) Apia</option>" +
    "<option value='2'>(GMT-11:00) Midway</option>" +
    "<option value='3'>(GMT-11:00) Niue</option>" +
    "<option value='4'>(GMT-11:00) Pago Pago</option>" +
    "<option value='5'>(GMT-10:00) Fakaofo</option>" +
    "<option value='6'>(GMT-10:00) Hawaii Time</option>" +
    "<option value='7'>(GMT-10:00) Johnston</option>" +
    "<option value='8'>(GMT-10:00) Rarotonga</option>" +
    "<option value='9'>(GMT-10:00) Tahiti</option>" +
    "<option value='10'>(GMT-09:30) Marquesas</option>" +
    "<option value='11'>(GMT-09:00) Alaska Time</option>" +
    "<option value='12'>(GMT-09:00) Gambier</option>" +
    "<option value='13'>(GMT-08:00) Pacific Time</option>" +
    "<option value='14'>(GMT-08:00) Pacific Time - Tijuana</option>" +
    "<option value='15'>(GMT-08:00) Pacific Time - Vancouver</option>" +
    "<option value='16'>(GMT-08:00) Pacific Time - Whitehorse</option>" +
    "<option value='17'>(GMT-08:00) Pitcairn</option>" +
    "<option value='18'>(GMT-07:00) Mountain Time</option>" +
    "<option value='19'>(GMT-07:00) Mountain Time - Arizona</option>" +
    "<option value='20'>(GMT-07:00) Mountain Time - Chihuahua, Mazatlan</option>" +
    "<option value='21'>(GMT-07:00) Mountain Time - Dawson Creek</option>" +
    "<option value='22'>(GMT-07:00) Mountain Time - Edmonton</option>" +
    "<option value='23'>(GMT-07:00) Mountain Time - Hermosillo</option>" +
    "<option value='24'>(GMT-07:00) Mountain Time - Yellowknife</option>" +
    "<option value='25'>(GMT-06:00) Belize</option>" +
    "<option value='26'>(GMT-06:00) Central Time</option>" +
    "<option value='27'>(GMT-06:00) Central Time - Mexico City</option>" +
    "<option value='28'>(GMT-06:00) Central Time - Regina</option>" +
    "<option value='29'>(GMT-06:00) Central Time - Winnipeg</option>" +
    "<option value='30'>(GMT-06:00) Costa Rica</option>" +
    "<option value='31'>(GMT-06:00) Easter Island</option>" +
    "<option value='32'>(GMT-06:00) El Salvador</option>" +
    "<option value='33'>(GMT-06:00) Galapagos</option>" +
    "<option value='34'>(GMT-06:00) Guatemala</option>" +
    "<option value='35'>(GMT-06:00) Managua</option>" +
    "<option value='36'>(GMT-05:00) Bogota</option>" +
    "<option value='37'>(GMT-05:00) Cayman</option>" +
    "<option value='38'>(GMT-05:00) Eastern Time</option>" +
    "<option value='39'>(GMT-05:00) Eastern Time - Iqaluit</option>" +
    "<option value='40'>(GMT-05:00) Eastern Time - Montreal</option>" +
    "<option value='41'>(GMT-05:00) Eastern Time - Toronto</option>" +
    "<option value='42'>(GMT-05:00) Grand Turk</option>" +
    "<option value='43'>(GMT-05:00) Guayaquil</option>" +
    "<option value='44'>(GMT-05:00) Havana</option>" +
    "<option value='45'>(GMT-05:00) Jamaica</option>" +
    "<option value='46'>(GMT-05:00) Lima</option>" +
    "<option value='47'>(GMT-05:00) Nassau</option>" +
    "<option value='48'>(GMT-05:00) Panama</option>" +
    "<option value='49'>(GMT-05:00) Port-au-Prince</option>" +
    "<option value='50'>(GMT-05:00) Rio Branco</option>" +
    "<option value='51'>(GMT-04:00) Anguilla</option>" +
    "<option value='52'>(GMT-04:00) Antigua</option>" +
    "<option value='53'>(GMT-04:00) Aruba</option>" +
    "<option value='54'>(GMT-04:00) Asuncion</option>" +
    "<option value='55'>(GMT-04:00) Atlantic Time - Halifax</option>" +
    "<option value='56'>(GMT-04:00) Barbados</option>" +
    "<option value='57'>(GMT-04:00) Bermuda</option>" +
    "<option value='58'>(GMT-04:00) Boa Vista</option>" +
    "<option value='59'>(GMT-04:00) Campo Grande</option>" +
    "<option value='60'>(GMT-04:00) Caracas</option>" +
    "<option value='61'>(GMT-04:00) Cuiaba</option>" +
    "<option value='62'>(GMT-04:00) Curacao</option>" +
    "<option value='63'>(GMT-04:00) Dominica</option>" +
    "<option value='64'>(GMT-04:00) Grenada</option>" +
    "<option value='65'>(GMT-04:00) Guadeloupe</option>" +
    "<option value='66'>(GMT-04:00) Guyana</option>" +
    "<option value='67'>(GMT-04:00) La Paz</option>" +
    "<option value='68'>(GMT-04:00) Manaus</option>" +
    "<option value='69'>(GMT-04:00) Martinique</option>" +
    "<option value='70'>(GMT-04:00) Montserrat</option>" +
    "<option value='71'>(GMT-04:00) Palmer</option>" +
    "<option value='72'>(GMT-04:00) Port of Spain</option>" +
    "<option value='73'>(GMT-04:00) Porto Velho</option>" +
    "<option value='74'>(GMT-04:00) Puerto Rico</option>" +
    "<option value='75'>(GMT-04:00) Santiago</option>" +
    "<option value='76'>(GMT-04:00) Santo Domingo</option>" +
    "<option value='77'>(GMT-04:00) St. Kitts</option>" +
    "<option value='78'>(GMT-04:00) St. Lucia</option>" +
    "<option value='79'>(GMT-04:00) St. Thomas</option>" +
    "<option value='80'>(GMT-04:00) St. Vincent</option>" +
    "<option value='81'>(GMT-04:00) Stanley</option>" +
    "<option value='82'>(GMT-04:00) Thule</option>" +
    "<option value='83'>(GMT-04:00) Tortola</option>" +
    "<option value='84'>(GMT-03:30) Newfoundland Time - St. Johns</option>" +
    "<option value='85'>(GMT-03:00) Araguaina</option>" +
    "<option value='86'>(GMT-03:00) Belem</option>" +
    "<option value='87'>(GMT-03:00) Buenos Aires</option>" +
    "<option value='88'>(GMT-03:00) Cayenne</option>" +
    "<option value='89'>(GMT-03:00) Fortaleza</option>" +
    "<option value='90'>(GMT-03:00) Godthab</option>" +
    "<option value='91'>(GMT-03:00) Maceio</option>" +
    "<option value='92'>(GMT-03:00) Miquelon</option>" +
    "<option value='93'>(GMT-03:00) Montevideo</option>" +
    "<option value='94'>(GMT-03:00) Paramaribo</option>" +
    "<option value='95'>(GMT-03:00) Recife</option>" +
    "<option value='96'>(GMT-03:00) Rothera</option>" +
    "<option value='97'>(GMT-03:00) Salvador</option>" +
    "<option value='98'>(GMT-03:00) Sao Paulo</option>" +
    "<option value='99'>(GMT-02:00) Noronha</option>" +
    "<option value='100'>(GMT-02:00) South Georgia</option>" +
    "<option value='101'>(GMT-01:00) Azores</option>" +
    "<option value='102'>(GMT-01:00) Cape Verde</option>" +
    "<option value='103'>(GMT-01:00) Scoresbysund</option>" +
    "<option value='104'>(GMT+00:00) Abidjan</option>" +
    "<option value='105'>(GMT+00:00) Accra</option>" +
    "<option value='106'>(GMT+00:00) Atlantic/Faeroe</option>" +
    "<option value='107'>(GMT+00:00) Bamako</option>" +
    "<option value='108'>(GMT+00:00) Banjul</option>" +
    "<option value='109'>(GMT+00:00) Bissau</option>" +
    "<option value='110'>(GMT+00:00) Canary Islands</option>" +
    "<option value='111'>(GMT+00:00) Casablanca</option>" +
    "<option value='112'>(GMT+00:00) Conakry</option>" +
    "<option value='113'>(GMT+00:00) Dakar</option>" +
    "<option value='114'>(GMT+00:00) Danmarkshavn</option>" +
    "<option value='115'>(GMT+00:00) Dublin</option>" +
    "<option value='116'>(GMT+00:00) El Aaiun</option>" +
    "<option value='117'>(GMT+00:00) Freetown</option>" +
    "<option value='118'>(GMT+00:00) Lisbon</option>" +
    "<option value='119'>(GMT+00:00) Lome</option>" +
    "<option value='120'>(GMT+00:00) London</option>" +
    "<option value='121'>(GMT+00:00) Monrovia</option>" +
    "<option value='122'>(GMT+00:00) Nouakchott</option>" +
    "<option value='123'>(GMT+00:00) Ouagadougou</option>" +
    "<option value='124'>(GMT+00:00) Reykjavik</option>" +
    "<option value='125'>(GMT+00:00) Sao Tome</option>" +
    "<option value='126'>(GMT+00:00) St Helena</option>" +
    "<option value='127'>(GMT+01:00) Algiers</option>" +
    "<option value='128'>(GMT+01:00) Amsterdam</option>" +
    "<option value='129'>(GMT+01:00) Andorra</option>" +
    "<option value='130'>(GMT+01:00) Bangui</option>" +
    "<option value='131'>(GMT+01:00) Berlin</option>" +
    "<option value='132'>(GMT+01:00) Brazzaville</option>" +
    "<option value='133'>(GMT+01:00) Brussels</option>" +
    "<option value='134'>(GMT+01:00) Budapest</option>" +
    "<option value='135'>(GMT+01:00) Central European Time</option>" +
    "<option value='136'>(GMT+01:00) Ceuta</option>" +
    "<option value='137'>(GMT+01:00) Copenhagen</option>" +
    "<option value='138'>(GMT+01:00) Douala</option>" +
    "<option value='139'>(GMT+01:00) Gibraltar</option>" +
    "<option value='140'>(GMT+01:00) Kinshasa</option>" +
    "<option value='141'>(GMT+01:00) Lagos</option>" +
    "<option value='142'>(GMT+01:00) Libreville</option>" +
    "<option value='143'>(GMT+01:00) Luanda</option>" +
    "<option value='144'>(GMT+01:00) Luxembourg</option>" +
    "<option value='145'>(GMT+01:00) Madrid</option>" +
    "<option value='146'>(GMT+01:00) Malabo</option>" +
    "<option value='147'>(GMT+01:00) Malta</option>" +
    "<option value='148'>(GMT+01:00) Monaco</option>" +
    "<option value='149'>(GMT+01:00) Ndjamena</option>" +
    "<option value='150'>(GMT+01:00) Niamey</option>" +
    "<option value='151'>(GMT+01:00) Oslo</option>" +
    "<option value='152'>(GMT+01:00) Paris</option>" +
    "<option value='153'>(GMT+01:00) Porto-Novo</option>" +
    "<option value='154'>(GMT+01:00) Rome</option>" +
    "<option value='155'>(GMT+01:00) Stockholm</option>" +
    "<option value='156'>(GMT+01:00) Tirane</option>" +
    "<option value='157'>(GMT+01:00) Tunis</option>" +
    "<option value='158'>(GMT+01:00) Vaduz</option>" +
    "<option value='159'>(GMT+01:00) Vienna</option>" +
    "<option value='160'>(GMT+01:00) Warsaw</option>" +
    "<option value='161'>(GMT+01:00) Windhoek</option>" +
    "<option value='162'>(GMT+01:00) Zurich</option>" +
    "<option value='163'>(GMT+02:00) Amman</option>" +
    "<option value='164'>(GMT+02:00) Athens</option>" +
    "<option value='165'>(GMT+02:00) Beirut</option>" +
    "<option value='166'>(GMT+02:00) Blantyre</option>" +
    "<option value='167'>(GMT+02:00) Bucharest</option>" +
    "<option value='168'>(GMT+02:00) Bujumbura</option>" +
    "<option value='169'>(GMT+02:00) Cairo</option>" +
    "<option value='170'>(GMT+02:00) Chisinau</option>" +
    "<option value='171'>(GMT+02:00) Damascus</option>" +
    "<option value='172'>(GMT+02:00) Gaborone</option>" +
    "<option value='173'>(GMT+02:00) Gaza</option>" +
    "<option value='174'>(GMT+02:00) Harare</option>" +
    "<option value='175'>(GMT+02:00) Helsinki</option>" +
    "<option value='176'>(GMT+02:00) Istanbul</option>" +
    "<option value='177'>(GMT+02:00) Johannesburg</option>" +
    "<option value='178'>(GMT+02:00) Kiev</option>" +
    "<option value='179'>(GMT+02:00) Kigali</option>" +
    "<option value='180'>(GMT+02:00) Lubumbashi</option>" +
    "<option value='181'>(GMT+02:00) Lusaka</option>" +
    "<option value='182'>(GMT+02:00) Maputo</option>" +
    "<option value='183'>(GMT+02:00) Maseru</option>" +
    "<option value='184'>(GMT+02:00) Mbabane</option>" +
    "<option value='185'>(GMT+02:00) Minsk</option>" +
    "<option value='186'>(GMT+02:00) Kaliningrad</option>" +
    "<option value='187'>(GMT+02:00) Nicosia</option>" +
    "<option value='188'>(GMT+02:00) Riga</option>" +
    "<option value='189'>(GMT+02:00) Sofia</option>" +
    "<option value='190'>(GMT+02:00) Tallinn</option>" +
    "<option value='191'>(GMT+02:00) Tel Aviv</option>" +
    "<option value='192'>(GMT+02:00) Tripoli</option>" +
    "<option value='193'>(GMT+02:00) Vilnius</option>" +
    "<option value='194'>(GMT+03:00) Addis Ababa</option>" +
    "<option value='195'>(GMT+03:00) Aden</option>" +
    "<option value='196'>(GMT+03:00) Africa/Asmera</option>" +
    "<option value='197'>(GMT+03:00) Antananarivo</option>" +
    "<option value='198'>(GMT+03:00) Baghdad</option>" +
    "<option value='199'>(GMT+03:00) Bahrain</option>" +
    "<option value='200'>(GMT+03:00) Comoro</option>" +
    "<option value='201'>(GMT+03:00) Dar es Salaam</option>" +
    "<option value='202'>(GMT+03:00) Djibouti</option>" +
    "<option value='203'>(GMT+03:00) Kampala</option>" +
    "<option value='204'>(GMT+03:00) Khartoum</option>" +
    "<option value='205'>(GMT+03:00) Kuwait</option>" +
    "<option value='206'>(GMT+03:00) Mayotte</option>" +
    "<option value='207'>(GMT+03:00) Mogadishu</option>" +
    "<option value='208'>(GMT+03:00) Moscow</option>" +
    "<option value='209'>(GMT+03:00) Nairobi</option>" +
    "<option value='210'>(GMT+03:00) Qatar</option>" +
    "<option value='211'>(GMT+03:00) Riyadh</option>" +
    "<option value='212'>(GMT+03:00) Syowa</option>" +
    "<option value='213'>(GMT+03:30) Tehran</option>" +
    "<option value='214'>(GMT+04:00) Baku</option>" +
    "<option value='215'>(GMT+04:00) Dubai</option>" +
    "<option value='216'>(GMT+04:00) Mahe</option>" +
    "<option value='217'>(GMT+04:00) Mauritius</option>" +
    "<option value='218'>(GMT+04:00) Samara</option>" +
    "<option value='219'>(GMT+04:00) Muscat</option>" +
    "<option value='220'>(GMT+04:00) Reunion</option>" +
    "<option value='221'>(GMT+04:00) Tbilisi</option>" +
    "<option value='222'>(GMT+04:00) Yerevan</option>" +
    "<option value='223'>(GMT+04:30) Kabul</option>" +
    "<option value='224'>(GMT+05:00) Aqtau</option>" +
    "<option value='225'>(GMT+05:00) Aqtobe</option>" +
    "<option value='226'>(GMT+05:00) Ashgabat</option>" +
    "<option value='227'>(GMT+05:00) Dushanbe</option>" +
    "<option value='228'>(GMT+05:00) Karachi</option>" +
    "<option value='229'>(GMT+05:00) Kerguelen</option>" +
    "<option value='230'>(GMT+05:00) Maldives</option>" +
    "<option value='231'>(GMT+05:00) Yekaterinburg</option>" +
    "<option value='232'>(GMT+05:00) Tashkent</option>" +
    "<option value='233'>(GMT+05:30) Colombo</option>" +
    "<option value='234'>(GMT+05:30) India Standard Time</option>" +
    "<option value='235'>(GMT+06:00) Almaty</option>" +
    "<option value='236'>(GMT+06:00) Bishkek</option>" +
    "<option value='237'>(GMT+06:00) Chagos</option>" +
    "<option value='238'>(GMT+06:00) Dhaka</option>" +
    "<option value='239'>(GMT+06:00) Mawson</option>" +
    "<option value='240'>(GMT+06:00) Omsk, Novosibirsk</option>" +
    "<option value='241'>(GMT+06:00) Thimphu</option>" +
    "<option value='242'>(GMT+06:00) Vostok</option>" +
    "<option value='243'>(GMT+06:30) Cocos</option>" +
    "<option value='244'>(GMT+06:30) Rangoon</option>" +
    "<option value='245'>(GMT+07:00) Bangkok</option>" +
    "<option value='246'>(GMT+07:00) Christmas</option>" +
    "<option value='247'>(GMT+07:00) Davis</option>" +
    "<option value='248'>(GMT+07:00) Hanoi</option>" +
    "<option value='249'>(GMT+07:00) Hovd</option>" +
    "<option value='250'>(GMT+07:00) Jakarta</option>" +
    "<option value='251'>(GMT+07:00) Krasnoyarsk</option>" +
    "<option value='252'>(GMT+07:00) Phnom Penh</option>" +
    "<option value='253'>(GMT+07:00) Vientiane</option>" +
    "<option value='254'>(GMT+08:00) Brunei</option>" +
    "<option value='255'>(GMT+08:00) Casey</option>" +
    "<option value='256'>(GMT+08:00) China Time - Beijing</option>" +
    "<option value='257'>(GMT+08:00) Hong Kong</option>" +
    "<option value='258'>(GMT+08:00) Kuala Lumpur</option>" +
    "<option value='259'>(GMT+08:00) Macau</option>" +
    "<option value='260'>(GMT+08:00) Makassar</option>" +
    "<option value='261'>(GMT+08:00) Manila</option>" +
    "<option value='262'>(GMT+08:00) Irkutsk</option>" +
    "<option value='263'>(GMT+08:00) Singapore</option>" +
    "<option value='264'>(GMT+08:00) Taipei</option>" +
    "<option value='265'>(GMT+08:00) Ulaanbaatar</option>" +
    "<option value='266'>(GMT+08:00) Western Time - Perth</option>" +
    "<option value='267'>(GMT+09:00) Choibalsan</option>" +
    "<option value='268'>(GMT+09:00) Dili</option>" +
    "<option value='269'>(GMT+09:00) Jayapura</option>" +
    "<option value='270'>(GMT+09:00) Yakutsk</option>" +
    "<option value='271'>(GMT+09:00) Palau</option>" +
    "<option value='272'>(GMT+09:00) Pyongyang</option>" +
    "<option value='273'>(GMT+09:00) Seoul</option>" +
    "<option value='274'>(GMT+09:00) Tokyo</option>" +
    "<option value='275'>(GMT+09:30) Central Time - Adelaide</option>" +
    "<option value='276'>(GMT+09:30) Central Time - Darwin</option>" +
    "<option value='277'>(GMT+10:00) Dumont D&</option>" +
    "<option value='278'>(GMT+10:00) Eastern Time - Brisbane</option>" +
    "<option value='279'>(GMT+10:00) Eastern Time - Hobart</option>" +
    "<option value='280'>(GMT+10:00) Eastern Time - Melbourne, Sydney</option>" +
    "<option value='281'>(GMT+10:00) Guam</option>" +
    "<option value='282'>(GMT+10:00) Yuzhno-Sakhalinsk</option>" +
    "<option value='283'>(GMT+10:00) Port Moresby</option>" +
    "<option value='284'>(GMT+10:00) Saipan</option>" +
    "<option value='285'>(GMT+10:00) Truk</option>" +
    "<option value='286'>(GMT+11:00) Efate</option>" +
    "<option value='287'>(GMT+11:00) Guadalcanal</option>" +
    "<option value='288'>(GMT+11:00) Kosrae</option>" +
    "<option value='289'>(GMT+11:00) Magadan</option>" +
    "<option value='290'>(GMT+11:00) Noumea</option>" +
    "<option value='291'>(GMT+11:00) Ponape</option>" +
    "<option value='292'>(GMT+11:30) Norfolk</option>" +
    "<option value='293'>(GMT+12:00) Antarctica/McMurdo</option>" +
    "<option value='294'>(GMT+12:00) Antarctica/South_Pole</option>" +
    "<option value='295'>(GMT+12:00) Auckland</option>" +
    "<option value='296'>(GMT+12:00) Fiji</option>" +
    "<option value='297'>(GMT+12:00) Funafuti</option>" +
    "<option value='298'>(GMT+12:00) Kwajalein</option>" +
    "<option value='299'>(GMT+12:00) Majuro</option>" +
    "<option value='300'>(GMT+12:00) Petropavlovsk-Kamchatskiy</option>" +
    "<option value='301'>(GMT+12:00) Nauru</option>" +
    "<option value='302'>(GMT+12:00) Tarawa</option>" +
    "<option value='303'>(GMT+12:00) Wake</option>" +
    "<option value='304'>(GMT+12:00) Wallis</option>" +
    "<option value='305'>(GMT+13:00) Enderbury</option>" +
    "<option value='306'>(GMT+13:00) Tongatapu</option>" +
    "<option value='307'>(GMT+14:00) Kiritimati</option>" +
"</select>";


var timeZoneForCountry = [];

	timeZoneForCountry["AF"] = Array('223');
	timeZoneForCountry["AX"] = Array('175');
	timeZoneForCountry["AL"] = Array('156');
	timeZoneForCountry["DZ"] = Array('127');
	timeZoneForCountry["AS"] = Array('4');
	timeZoneForCountry["AD"] = Array('129');
	timeZoneForCountry["AO"] = Array('143');
	timeZoneForCountry["AI"] = Array('51');
	timeZoneForCountry["AQ"] = Array('71','96','212','239','242','247','255','277','293','294');
	timeZoneForCountry["AG"] = Array('52');
	timeZoneForCountry["AR"] = Array('87');
	timeZoneForCountry["AM"] = Array('222');
	timeZoneForCountry["AW"] = Array('53');
	timeZoneForCountry["AU"] = Array('266','275','276','278','279','280');
	timeZoneForCountry["AT"] = Array('159');
	timeZoneForCountry["AZ"] = Array('214');
	timeZoneForCountry["BS"] = Array('47');
	timeZoneForCountry["BH"] = Array('199');
	timeZoneForCountry["BD"] = Array('238');
	timeZoneForCountry["BB"] = Array('56');
	timeZoneForCountry["BY"] = Array('185');
	timeZoneForCountry["BE"] = Array('133');
	timeZoneForCountry["BZ"] = Array('25');
	timeZoneForCountry["BJ"] = Array('153');
	timeZoneForCountry["BM"] = Array('57');
	timeZoneForCountry["BT"] = Array('241');
	timeZoneForCountry["BO"] = Array('67');
	timeZoneForCountry["BA"] = Array('135');
	timeZoneForCountry["BW"] = Array('172');
	timeZoneForCountry["BV"] = Array(''); //empty
	timeZoneForCountry["BR"] = Array('50','58','59','61','68','73','85','86','89','91','95','97','98','99');
	timeZoneForCountry["IO"] = Array('237');
	timeZoneForCountry["BN"] = Array('254');
	timeZoneForCountry["BG"] = Array('189');
	timeZoneForCountry["BF"] = Array('123');
	timeZoneForCountry["BI"] = Array('168');
	timeZoneForCountry["KH"] = Array('252');
	timeZoneForCountry["CM"] = Array('138');
	timeZoneForCountry["CA"] = Array('15','16','21','22','24','28','29','39','40','41','55','84');
	timeZoneForCountry["CV"] = Array('102');
	timeZoneForCountry["KY"] = Array('37');
	timeZoneForCountry["CF"] = Array('130');
	timeZoneForCountry["TD"] = Array('149');
	timeZoneForCountry["CL"] = Array('31','75');
	timeZoneForCountry["CN"] = Array('256');
	timeZoneForCountry["CX"] = Array('246');
	timeZoneForCountry["CC"] = Array('243');
	timeZoneForCountry["CO"] = Array('36');
	timeZoneForCountry["KM"] = Array('200');
	timeZoneForCountry["CG"] = Array('140','180');
	timeZoneForCountry["CD"] = Array('132');
	timeZoneForCountry["CK"] = Array('8');
	timeZoneForCountry["CR"] = Array('30');
	timeZoneForCountry["CI"] = Array('104');
	timeZoneForCountry["HR"] = Array('135');
	timeZoneForCountry["CU"] = Array('44');
	timeZoneForCountry["CY"] = Array('187');
	timeZoneForCountry["CZ"] = Array('135');
	timeZoneForCountry["DK"] = Array('137');
	timeZoneForCountry["DJ"] = Array('202');
	timeZoneForCountry["DM"] = Array('63');
	timeZoneForCountry["DO"] = Array('76');
	timeZoneForCountry["TL"] = Array('268');
	timeZoneForCountry["EC"] = Array('33','43');
	timeZoneForCountry["EG"] = Array('169');
	timeZoneForCountry["SV"] = Array('32');
	timeZoneForCountry["GQ"] = Array('146');
	timeZoneForCountry["ER"] = Array('196');
	timeZoneForCountry["EE"] = Array('190');
	timeZoneForCountry["ET"] = Array('194');
	timeZoneForCountry["FK"] = Array('81');
	timeZoneForCountry["FO"] = Array('106');
	timeZoneForCountry["FJ"] = Array('296');
	timeZoneForCountry["FI"] = Array('175');
	timeZoneForCountry["FR"] = Array('152');
	timeZoneForCountry["GF"] = Array('88');
	timeZoneForCountry["PF"] = Array('9','10','12');
	timeZoneForCountry["TF"] = Array('229');
	timeZoneForCountry["GA"] = Array('142');
	timeZoneForCountry["GM"] = Array('108');
	timeZoneForCountry["GE"] = Array('221');
	timeZoneForCountry["DE"] = Array('131');
	timeZoneForCountry["GH"] = Array('105');
	timeZoneForCountry["GI"] = Array('139');
	timeZoneForCountry["GR"] = Array('164');
	timeZoneForCountry["GL"] = Array('82','90','103','114');
	timeZoneForCountry["GD"] = Array('64');
	timeZoneForCountry["GP"] = Array('65');
	timeZoneForCountry["GU"] = Array('281');
	timeZoneForCountry["GT"] = Array('34');
	timeZoneForCountry["GG"] = Array('120');
	timeZoneForCountry["GN"] = Array('112');
	timeZoneForCountry["GW"] = Array('109');
	timeZoneForCountry["GY"] = Array('66');
	timeZoneForCountry["HT"] = Array('49');
	timeZoneForCountry["HM"] = Array(); //empty
	timeZoneForCountry["HN"] = Array('26');
	timeZoneForCountry["HK"] = Array('257');
	timeZoneForCountry["HU"] = Array('134');
	timeZoneForCountry["IS"] = Array('124');
	timeZoneForCountry["IN"] = Array('234');
	timeZoneForCountry["ID"] = Array('250','260','269');
	timeZoneForCountry["IR"] = Array('213');
	timeZoneForCountry["IQ"] = Array('198');
	timeZoneForCountry["IE"] = Array('115');
	timeZoneForCountry["IM"] = Array('120');
	timeZoneForCountry["IL"] = Array('191');
	timeZoneForCountry["IT"] = Array('154');
	timeZoneForCountry["JM"] = Array('45');
	timeZoneForCountry["JP"] = Array('274');
	timeZoneForCountry["JE"] = Array('120');
	timeZoneForCountry["JO"] = Array('163');
	timeZoneForCountry["KZ"] = Array('224','225','235');
	timeZoneForCountry["KE"] = Array('209');
	timeZoneForCountry["KI"] = Array('302','307');
	timeZoneForCountry["KW"] = Array('205');
	timeZoneForCountry["KG"] = Array('236');
	timeZoneForCountry["LA"] = Array('253');
	timeZoneForCountry["LV"] = Array('188');
	timeZoneForCountry["LB"] = Array('165');
	timeZoneForCountry["LS"] = Array('183');
	timeZoneForCountry["LR"] = Array('121');
	timeZoneForCountry["LY"] = Array('192');
	timeZoneForCountry["LI"] = Array('158');
	timeZoneForCountry["LT"] = Array('193');
	timeZoneForCountry["LU"] = Array('144');
	timeZoneForCountry["MO"] = Array('259');
	timeZoneForCountry["MK"] = Array('135');
	timeZoneForCountry["MG"] = Array('197');
	timeZoneForCountry["MW"] = Array('166');
	timeZoneForCountry["MY"] = Array('258');
	timeZoneForCountry["MV"] = Array('230');
	timeZoneForCountry["ML"] = Array('107');
	timeZoneForCountry["MT"] = Array('147');
	timeZoneForCountry["MH"] = Array('298','299');
	timeZoneForCountry["MQ"] = Array('69');
	timeZoneForCountry["MR"] = Array('122');
	timeZoneForCountry["MU"] = Array('217');
	timeZoneForCountry["YT"] = Array('206');
	timeZoneForCountry["MX"] = Array('14','20','23','27');
	timeZoneForCountry["FM"] = Array('285','288','291');
	timeZoneForCountry["MD"] = Array('170');
	timeZoneForCountry["MC"] = Array('148');
	timeZoneForCountry["MN"] = Array('249','265','267');
	timeZoneForCountry["ME"] = Array('135');
	timeZoneForCountry["MS"] = Array('70');
	timeZoneForCountry["MA"] = Array('111');
	timeZoneForCountry["MZ"] = Array('182');
	timeZoneForCountry["MM"] = Array('244');
	timeZoneForCountry["NA"] = Array('161');
	timeZoneForCountry["NR"] = Array('301');
	timeZoneForCountry["NP"] = Array('234');
	timeZoneForCountry["NL"] = Array('128');
	timeZoneForCountry["AN"] = Array('62');
	timeZoneForCountry["NC"] = Array('290');
	timeZoneForCountry["NZ"] = Array('295');
	timeZoneForCountry["NI"] = Array('35');
	timeZoneForCountry["NE"] = Array('150');
	timeZoneForCountry["NG"] = Array('141');
	timeZoneForCountry["NU"] = Array('3');
	timeZoneForCountry["NF"] = Array('292');
	timeZoneForCountry["MP"] = Array('284');
	timeZoneForCountry["KP"] = Array('272');
	timeZoneForCountry["NO"] = Array('151');
	timeZoneForCountry["OM"] = Array('219');
	timeZoneForCountry["PK"] = Array('228');
	timeZoneForCountry["PW"] = Array('271');
	timeZoneForCountry["PS"] = Array('173');
	timeZoneForCountry["PA"] = Array('48');
	timeZoneForCountry["PG"] = Array('283');
	timeZoneForCountry["PY"] = Array('54');
	timeZoneForCountry["PE"] = Array('46');
	timeZoneForCountry["PH"] = Array('261');
	timeZoneForCountry["PN"] = Array('17');
	timeZoneForCountry["PL"] = Array('160');
	timeZoneForCountry["PT"] = Array('101','118');
	timeZoneForCountry["PR"] = Array('74');
	timeZoneForCountry["QA"] = Array('210');
	timeZoneForCountry["RE"] = Array('220');
	timeZoneForCountry["RO"] = Array('167');
	timeZoneForCountry["RU"] = Array('186','208','218','231','240','251','262','270','282','289','300');
	timeZoneForCountry["RW"] = Array('179');
	timeZoneForCountry["SH"] = Array('126');
	timeZoneForCountry["KN"] = Array('77');
	timeZoneForCountry["LC"] = Array('78');
	timeZoneForCountry["PM"] = Array('92');
	timeZoneForCountry["VC"] = Array('80');
	timeZoneForCountry["WS"] = Array('1');
	timeZoneForCountry["SM"] = Array('154');
	timeZoneForCountry["ST"] = Array('125');
	timeZoneForCountry["SA"] = Array('211');
	timeZoneForCountry["SN"] = Array('113');
	timeZoneForCountry["RS"] = Array('135');
	timeZoneForCountry["CS"] = Array('135');
	timeZoneForCountry["SC"] = Array('216');
	timeZoneForCountry["SL"] = Array('117');
	timeZoneForCountry["SG"] = Array('263');
	timeZoneForCountry["SK"] = Array('135');
	timeZoneForCountry["SI"] = Array('135');
	timeZoneForCountry["SB"] = Array('287');
	timeZoneForCountry["SO"] = Array('207');
	timeZoneForCountry["ZA"] = Array('177');
	timeZoneForCountry["GS"] = Array('100');
	timeZoneForCountry["KR"] = Array('273');
	timeZoneForCountry["ES"] = Array('110','136','145');
	timeZoneForCountry["LK"] = Array('233');
	timeZoneForCountry["SD"] = Array('204');
	timeZoneForCountry["SR"] = Array('94');
	timeZoneForCountry["SJ"] = Array('151');
	timeZoneForCountry["SZ"] = Array('184');
	timeZoneForCountry["SE"] = Array('155');
	timeZoneForCountry["CH"] = Array('162');
	timeZoneForCountry["SY"] = Array('171');
	timeZoneForCountry["TW"] = Array('264');
	timeZoneForCountry["TJ"] = Array('227');
	timeZoneForCountry["TZ"] = Array('201');
	timeZoneForCountry["TH"] = Array('245');
	timeZoneForCountry["TG"] = Array('119');
	timeZoneForCountry["TK"] = Array('5');
	timeZoneForCountry["TO"] = Array('306');
	timeZoneForCountry["TT"] = Array('72');
	timeZoneForCountry["TN"] = Array('157');
	timeZoneForCountry["TR"] = Array('176');
	timeZoneForCountry["TM"] = Array('226');
	timeZoneForCountry["TC"] = Array('42');
	timeZoneForCountry["TV"] = Array('297');
	timeZoneForCountry["UG"] = Array('203');
	timeZoneForCountry["UA"] = Array('178');
	timeZoneForCountry["AE"] = Array('215');
	timeZoneForCountry["GB"] = Array('120');
	timeZoneForCountry["US"] = Array('6','11','13','18','19','26','38');
	timeZoneForCountry["UM"] = Array('2','7','303','305');
	timeZoneForCountry["UY"] = Array('93');
	timeZoneForCountry["UZ"] = Array('232');
	timeZoneForCountry["VU"] = Array('286');
	timeZoneForCountry["VA"] = Array('154');
	timeZoneForCountry["VE"] = Array('60');
	timeZoneForCountry["VN"] = Array('248');
	timeZoneForCountry["VG"] = Array('83');
	timeZoneForCountry["VI"] = Array('79');
	timeZoneForCountry["WF"] = Array('304');
	timeZoneForCountry["EH"] = Array('116');
	timeZoneForCountry["YE"] = Array('195');
	timeZoneForCountry["ZM"] = Array('181');
	timeZoneForCountry["ZW"] = Array('174');
    //----------------------------------------------------------------------------------------------------------------------------------------
    var AllTimeZonesArr = [];
    
    AllTimeZonesArr['1'] = '(GMT-11:00) Apia';
    AllTimeZonesArr['2'] = '(GMT-11:00) Midway';
    AllTimeZonesArr['3'] = '(GMT-11:00) Niue';
    AllTimeZonesArr['4'] = '(GMT-11:00) Pago Pago';
    AllTimeZonesArr['5'] = '(GMT-10:00) Fakaofo';
    AllTimeZonesArr['6'] = '(GMT-10:00) Hawaii Time';
    AllTimeZonesArr['7'] = '(GMT-10:00) Johnston';
    AllTimeZonesArr['8'] = '(GMT-10:00) Rarotonga';
    AllTimeZonesArr['9'] = '(GMT-10:00) Tahiti';
    AllTimeZonesArr['10'] = '(GMT-09:30) Marquesas';
    AllTimeZonesArr['11'] = '(GMT-09:00) Alaska Time';
    AllTimeZonesArr['12'] = '(GMT-09:00) Gambier';
    AllTimeZonesArr['13'] = '(GMT-08:00) Pacific Time';
    AllTimeZonesArr['14'] = '(GMT-08:00) Pacific Time - Tijuana';
    AllTimeZonesArr['15'] = '(GMT-08:00) Pacific Time - Vancouver';
    AllTimeZonesArr['16'] = '(GMT-08:00) Pacific Time - Whitehorse';
    AllTimeZonesArr['17'] = '(GMT-08:00) Pitcairn';
    AllTimeZonesArr['18'] = '(GMT-07:00) Mountain Time';
    AllTimeZonesArr['19'] = '(GMT-07:00) Mountain Time - Arizona';
    AllTimeZonesArr['20'] = '(GMT-07:00) Mountain Time - Chihuahua, Mazatlan';
    AllTimeZonesArr['21'] = '(GMT-07:00) Mountain Time - Dawson Creek';
    AllTimeZonesArr['22'] = '(GMT-07:00) Mountain Time - Edmonton';
    AllTimeZonesArr['23'] = '(GMT-07:00) Mountain Time - Hermosillo';
    AllTimeZonesArr['24'] = '(GMT-07:00) Mountain Time - Yellowknife';
    AllTimeZonesArr['25'] = '(GMT-06:00) Belize';
    AllTimeZonesArr['26'] = '(GMT-06:00) Central Time ';
    AllTimeZonesArr['27'] = '(GMT-06:00) Central Time - Mexico City';
    AllTimeZonesArr['28'] = '(GMT-06:00) Central Time - Regina';
    AllTimeZonesArr['29'] = '(GMT-06:00) Central Time - Winnipeg';
    AllTimeZonesArr['30'] = '(GMT-06:00) Costa Rica';
    AllTimeZonesArr['31'] = '(GMT-06:00) Easter Island';
    AllTimeZonesArr['32'] = '(GMT-06:00) El Salvador';
    AllTimeZonesArr['33'] = '(GMT-06:00) Galapagos';
    AllTimeZonesArr['34'] = '(GMT-06:00) Guatemala';
    AllTimeZonesArr['35'] = '(GMT-06:00) Managua';
    AllTimeZonesArr['36'] = '(GMT-05:00) Bogota';
    AllTimeZonesArr['37'] = '(GMT-05:00) Cayman';
    AllTimeZonesArr['38'] = '(GMT-05:00) Eastern Time';
    AllTimeZonesArr['39'] = '(GMT-05:00) Eastern Time - Iqaluit';
    AllTimeZonesArr['40'] = '(GMT-05:00) Eastern Time - Montreal';
    AllTimeZonesArr['41'] = '(GMT-05:00) Eastern Time - Toronto';
    AllTimeZonesArr['42'] = '(GMT-05:00) Grand Turk';
    AllTimeZonesArr['43'] = '(GMT-05:00) Guayaquil';
    AllTimeZonesArr['44'] = '(GMT-05:00) Havana';
    AllTimeZonesArr['45'] = '(GMT-05:00) Jamaica';
    AllTimeZonesArr['46'] = '(GMT-05:00) Lima';
    AllTimeZonesArr['47'] = '(GMT-05:00) Nassau';
    AllTimeZonesArr['48'] = '(GMT-05:00) Panama';
    AllTimeZonesArr['49'] = '(GMT-05:00) Port-au-Prince';
    AllTimeZonesArr['50'] = '(GMT-05:00) Rio Branco';
    AllTimeZonesArr['51'] = '(GMT-04:00) Anguilla';
    AllTimeZonesArr['52'] = '(GMT-04:00) Antigua';
    AllTimeZonesArr['53'] = '(GMT-04:00) Aruba';
    AllTimeZonesArr['54'] = '(GMT-04:00) Asuncion';
    AllTimeZonesArr['55'] = '(GMT-04:00) Atlantic Time - Halifax';
    AllTimeZonesArr['56'] = '(GMT-04:00) Barbados';
    AllTimeZonesArr['57'] = '(GMT-04:00) Bermuda';
    AllTimeZonesArr['58'] = '(GMT-04:00) Boa Vista';
    AllTimeZonesArr['59'] = '(GMT-04:00) Campo Grande';
    AllTimeZonesArr['60'] = '(GMT-04:00) Caracas';
    AllTimeZonesArr['61'] = '(GMT-04:00) Cuiaba';
    AllTimeZonesArr['62'] = '(GMT-04:00) Curacao';
    AllTimeZonesArr['63'] = '(GMT-04:00) Dominica';
    AllTimeZonesArr['64'] = '(GMT-04:00) Grenada';
    AllTimeZonesArr['65'] = '(GMT-04:00) Guadeloupe';
    AllTimeZonesArr['66'] = '(GMT-04:00) Guyana';
    AllTimeZonesArr['67'] = '(GMT-04:00) La Paz';
    AllTimeZonesArr['68'] = '(GMT-04:00) Manaus';
    AllTimeZonesArr['69'] = '(GMT-04:00) Martinique';
    AllTimeZonesArr['70'] = '(GMT-04:00) Montserrat';
    AllTimeZonesArr['71'] = '(GMT-04:00) Palmer';
    AllTimeZonesArr['72'] = '(GMT-04:00) Port of Spain';
    AllTimeZonesArr['73'] = '(GMT-04:00) Porto Velho';
    AllTimeZonesArr['74'] = '(GMT-04:00) Puerto Rico';
    AllTimeZonesArr['75'] = '(GMT-04:00) Santiago';
    AllTimeZonesArr['76'] = '(GMT-04:00) Santo Domingo';
    AllTimeZonesArr['77'] = '(GMT-04:00) St. Kitts';
    AllTimeZonesArr['78'] = '(GMT-04:00) St. Lucia';
    AllTimeZonesArr['79'] = '(GMT-04:00) St. Thomas';
    AllTimeZonesArr['80'] = '(GMT-04:00) St. Vincent';
    AllTimeZonesArr['81'] = '(GMT-04:00) Stanley';
    AllTimeZonesArr['82'] = '(GMT-04:00) Thule';
    AllTimeZonesArr['83'] = '(GMT-04:00) Tortola';
    AllTimeZonesArr['84'] = '(GMT-03:30) Newfoundland Time - St. Johns';
    AllTimeZonesArr['85'] = '(GMT-03:00) Araguaina';
    AllTimeZonesArr['86'] = '(GMT-03:00) Belem';
    AllTimeZonesArr['87'] = '(GMT-03:00) Buenos Aires';
    AllTimeZonesArr['88'] = '(GMT-03:00) Cayenne';
    AllTimeZonesArr['89'] = '(GMT-03:00) Fortaleza';
    AllTimeZonesArr['90'] = '(GMT-03:00) Godthab';
    AllTimeZonesArr['91'] = '(GMT-03:00) Maceio';
    AllTimeZonesArr['92'] = '(GMT-03:00) Miquelon';
    AllTimeZonesArr['93'] = '(GMT-03:00) Montevideo';
    AllTimeZonesArr['94'] = '(GMT-03:00) Paramaribo';
    AllTimeZonesArr['95'] = '(GMT-03:00) Recife';
    AllTimeZonesArr['96'] = '(GMT-03:00) Rothera';
    AllTimeZonesArr['97'] = '(GMT-03:00) Salvador';
    AllTimeZonesArr['98'] = '(GMT-03:00) Sao Paulo';
    AllTimeZonesArr['99'] = '(GMT-02:00) Noronha';
    AllTimeZonesArr['100'] = '(GMT-02:00) South Georgia';
    AllTimeZonesArr['101'] = '(GMT-01:00) Azores';
    AllTimeZonesArr['102'] = '(GMT-01:00) Cape Verde';
    AllTimeZonesArr['103'] = '(GMT-01:00) Scoresbysund';
    AllTimeZonesArr['104'] = '(GMT+00:00) Abidjan';
    AllTimeZonesArr['105'] = '(GMT+00:00) Accra';
    AllTimeZonesArr['106'] = '(GMT+00:00) Atlantic/Faeroe';
    AllTimeZonesArr['107'] = '(GMT+00:00) Bamako';
    AllTimeZonesArr['108'] = '(GMT+00:00) Banjul';
    AllTimeZonesArr['109'] = '(GMT+00:00) Bissau';
    AllTimeZonesArr['110'] = '(GMT+00:00) Canary Islands';
    AllTimeZonesArr['111'] = '(GMT+00:00) Casablanca';
    AllTimeZonesArr['112'] = '(GMT+00:00) Conakry';
    AllTimeZonesArr['113'] = '(GMT+00:00) Dakar';
    AllTimeZonesArr['114'] = '(GMT+00:00) Danmarkshavn';
    AllTimeZonesArr['115'] = '(GMT+00:00) Dublin';
    AllTimeZonesArr['116'] = '(GMT+00:00) El Aaiun';
    AllTimeZonesArr['117'] = '(GMT+00:00) Freetown';
    AllTimeZonesArr['118'] = '(GMT+00:00) Lisbon';
    AllTimeZonesArr['119'] = '(GMT+00:00) Lome';
    AllTimeZonesArr['120'] = '(GMT+00:00) London';
    AllTimeZonesArr['121'] = '(GMT+00:00) Monrovia';
    AllTimeZonesArr['122'] = '(GMT+00:00) Nouakchott';
    AllTimeZonesArr['123'] = '(GMT+00:00) Ouagadougou';
    AllTimeZonesArr['124'] = '(GMT+00:00) Reykjavik';
    AllTimeZonesArr['125'] = '(GMT+00:00) Sao Tome';
    AllTimeZonesArr['126'] = '(GMT+00:00) St Helena';
    AllTimeZonesArr['127'] = '(GMT+01:00) Algiers';
    AllTimeZonesArr['128'] = '(GMT+01:00) Amsterdam';
    AllTimeZonesArr['129'] = '(GMT+01:00) Andorra';
    AllTimeZonesArr['130'] = '(GMT+01:00) Bangui';
    AllTimeZonesArr['131'] = '(GMT+01:00) Berlin';
    AllTimeZonesArr['132'] = '(GMT+01:00) Brazzaville';
    AllTimeZonesArr['133'] = '(GMT+01:00) Brussels';
    AllTimeZonesArr['134'] = '(GMT+01:00) Budapest';
    AllTimeZonesArr['135'] = '(GMT+01:00) Central European Time';
    AllTimeZonesArr['136'] = '(GMT+01:00) Ceuta';
    AllTimeZonesArr['137'] = '(GMT+01:00) Copenhagen';
    AllTimeZonesArr['138'] = '(GMT+01:00) Douala';
    AllTimeZonesArr['139'] = '(GMT+01:00) Gibraltar';
    AllTimeZonesArr['140'] = '(GMT+01:00) Kinshasa';
    AllTimeZonesArr['141'] = '(GMT+01:00) Lagos';
    AllTimeZonesArr['142'] = '(GMT+01:00) Libreville';
    AllTimeZonesArr['143'] = '(GMT+01:00) Luanda';
    AllTimeZonesArr['144'] = '(GMT+01:00) Luxembourg';
    AllTimeZonesArr['145'] = '(GMT+01:00) Madrid';
    AllTimeZonesArr['146'] = '(GMT+01:00) Malabo';
    AllTimeZonesArr['147'] = '(GMT+01:00) Malta';
    AllTimeZonesArr['148'] = '(GMT+01:00) Monaco';
    AllTimeZonesArr['149'] = '(GMT+01:00) Ndjamena';
    AllTimeZonesArr['150'] = '(GMT+01:00) Niamey';
    AllTimeZonesArr['151'] = '(GMT+01:00) Oslo';
    AllTimeZonesArr['152'] = '(GMT+01:00) Paris';
    AllTimeZonesArr['153'] = '(GMT+01:00) Porto-Novo';
    AllTimeZonesArr['154'] = '(GMT+01:00) Rome';
    AllTimeZonesArr['155'] = '(GMT+01:00) Stockholm';
    AllTimeZonesArr['156'] = '(GMT+01:00) Tirane';
    AllTimeZonesArr['157'] = '(GMT+01:00) Tunis';
    AllTimeZonesArr['158'] = '(GMT+01:00) Vaduz';
    AllTimeZonesArr['159'] = '(GMT+01:00) Vienna';
    AllTimeZonesArr['160'] = '(GMT+01:00) Warsaw';
    AllTimeZonesArr['161'] = '(GMT+01:00) Windhoek';
    AllTimeZonesArr['162'] = '(GMT+01:00) Zurich';
    AllTimeZonesArr['163'] = '(GMT+02:00) Amman';
    AllTimeZonesArr['164'] = '(GMT+02:00) Athens';
    AllTimeZonesArr['165'] = '(GMT+02:00) Beirut';
    AllTimeZonesArr['166'] = '(GMT+02:00) Blantyre';
    AllTimeZonesArr['167'] = '(GMT+02:00) Bucharest';
    AllTimeZonesArr['168'] = '(GMT+02:00) Bujumbura';
    AllTimeZonesArr['169'] = '(GMT+02:00) Cairo';
    AllTimeZonesArr['170'] = '(GMT+02:00) Chisinau';
    AllTimeZonesArr['171'] = '(GMT+02:00) Damascus';
    AllTimeZonesArr['172'] = '(GMT+02:00) Gaborone';
    AllTimeZonesArr['173'] = '(GMT+02:00) Gaza';
    AllTimeZonesArr['174'] = '(GMT+02:00) Harare';
    AllTimeZonesArr['175'] = '(GMT+02:00) Helsinki';
    AllTimeZonesArr['176'] = '(GMT+02:00) Istanbul';
    AllTimeZonesArr['177'] = '(GMT+02:00) Johannesburg';
    AllTimeZonesArr['178'] = '(GMT+02:00) Kiev';
    AllTimeZonesArr['179'] = '(GMT+02:00) Kigali';
    AllTimeZonesArr['180'] = '(GMT+02:00) Lubumbashi';
    AllTimeZonesArr['181'] = '(GMT+02:00) Lusaka';
    AllTimeZonesArr['182'] = '(GMT+02:00) Maputo';
    AllTimeZonesArr['183'] = '(GMT+02:00) Maseru';
    AllTimeZonesArr['184'] = '(GMT+02:00) Mbabane';
    AllTimeZonesArr['185'] = '(GMT+02:00) Minsk';
    AllTimeZonesArr['186'] = '(GMT+02:00) Kaliningrad';
    AllTimeZonesArr['187'] = '(GMT+02:00) Nicosia';
    AllTimeZonesArr['188'] = '(GMT+02:00) Riga';
    AllTimeZonesArr['189'] = '(GMT+02:00) Sofia';
    AllTimeZonesArr['190'] = '(GMT+02:00) Tallinn';
    AllTimeZonesArr['191'] = '(GMT+02:00) Tel Aviv';
    AllTimeZonesArr['192'] = '(GMT+02:00) Tripoli';
    AllTimeZonesArr['193'] = '(GMT+02:00) Vilnius';
    AllTimeZonesArr['194'] = '(GMT+03:00) Addis Ababa';
    AllTimeZonesArr['195'] = '(GMT+03:00) Aden';
    AllTimeZonesArr['196'] = '(GMT+03:00) Africa/Asmera';
    AllTimeZonesArr['197'] = '(GMT+03:00) Antananarivo';
    AllTimeZonesArr['198'] = '(GMT+03:00) Baghdad';
    AllTimeZonesArr['199'] = '(GMT+03:00) Bahrain';
    AllTimeZonesArr['200'] = '(GMT+03:00) Comoro';
    AllTimeZonesArr['201'] = '(GMT+03:00) Dar es Salaam';
    AllTimeZonesArr['202'] = '(GMT+03:00) Djibouti';
    AllTimeZonesArr['203'] = '(GMT+03:00) Kampala';
    AllTimeZonesArr['204'] = '(GMT+03:00) Khartoum';
    AllTimeZonesArr['205'] = '(GMT+03:00) Kuwait';
    AllTimeZonesArr['206'] = '(GMT+03:00) Mayotte';
    AllTimeZonesArr['207'] = '(GMT+03:00) Mogadishu';
    AllTimeZonesArr['208'] = '(GMT+03:00) Moscow';
    AllTimeZonesArr['209'] = '(GMT+03:00) Nairobi';
    AllTimeZonesArr['210'] = '(GMT+03:00) Qatar';
    AllTimeZonesArr['211'] = '(GMT+03:00) Riyadh';
    AllTimeZonesArr['212'] = '(GMT+03:00) Syowa';
    AllTimeZonesArr['213'] = '(GMT+03:30) Tehran';
    AllTimeZonesArr['214'] = '(GMT+04:00) Baku';
    AllTimeZonesArr['215'] = '(GMT+04:00) Dubai';
    AllTimeZonesArr['216'] = '(GMT+04:00) Mahe';
    AllTimeZonesArr['217'] = '(GMT+04:00) Mauritius';
    AllTimeZonesArr['218'] = '(GMT+04:00) Samara';
    AllTimeZonesArr['219'] = '(GMT+04:00) Muscat';
    AllTimeZonesArr['220'] = '(GMT+04:00) Reunion';
    AllTimeZonesArr['221'] = '(GMT+04:00) Tbilisi';
    AllTimeZonesArr['222'] = '(GMT+04:00) Yerevan';
    AllTimeZonesArr['223'] = '(GMT+04:30) Kabul';
    AllTimeZonesArr['224'] = '(GMT+05:00) Aqtau';
    AllTimeZonesArr['225'] = '(GMT+05:00) Aqtobe';
    AllTimeZonesArr['226'] = '(GMT+05:00) Ashgabat';
    AllTimeZonesArr['227'] = '(GMT+05:00) Dushanbe';
    AllTimeZonesArr['228'] = '(GMT+05:00) Karachi';
    AllTimeZonesArr['229'] = '(GMT+05:00) Kerguelen';
    AllTimeZonesArr['230'] = '(GMT+05:00) Maldives';
    AllTimeZonesArr['231'] = '(GMT+05:00) Yekaterinburg';
    AllTimeZonesArr['232'] = '(GMT+05:00) Tashkent';
    AllTimeZonesArr['233'] = '(GMT+05:30) Colombo';
    AllTimeZonesArr['234'] = '(GMT+05:30) India Standard Time';
    AllTimeZonesArr['235'] = '(GMT+06:00) Almaty';
    AllTimeZonesArr['236'] = '(GMT+06:00) Bishkek';
    AllTimeZonesArr['237'] = '(GMT+06:00) Chagos';
    AllTimeZonesArr['238'] = '(GMT+06:00) Dhaka';
    AllTimeZonesArr['239'] = '(GMT+06:00) Mawson';
    AllTimeZonesArr['240'] = '(GMT+06:00) Omsk, Novosibirsk';
    AllTimeZonesArr['241'] = '(GMT+06:00) Thimphu';
    AllTimeZonesArr['242'] = '(GMT+06:00) Vostok';
    AllTimeZonesArr['243'] = '(GMT+06:30) Cocos';
    AllTimeZonesArr['244'] = '(GMT+06:30) Rangoon';
    AllTimeZonesArr['245'] = '(GMT+07:00) Bangkok';
    AllTimeZonesArr['246'] = '(GMT+07:00) Christmas';
    AllTimeZonesArr['247'] = '(GMT+07:00) Davis';
    AllTimeZonesArr['248'] = '(GMT+07:00) Hanoi';
    AllTimeZonesArr['249'] = '(GMT+07:00) Hovd';
    AllTimeZonesArr['250'] = '(GMT+07:00) Jakarta';
    AllTimeZonesArr['251'] = '(GMT+07:00) Krasnoyarsk';
    AllTimeZonesArr['252'] = '(GMT+07:00) Phnom Penh';
    AllTimeZonesArr['253'] = '(GMT+07:00) Vientiane';
    AllTimeZonesArr['254'] = '(GMT+08:00) Brunei';
    AllTimeZonesArr['255'] = '(GMT+08:00) Casey';
    AllTimeZonesArr['256'] = '(GMT+08:00) China Time - Beijing';
    AllTimeZonesArr['257'] = '(GMT+08:00) Hong Kong';
    AllTimeZonesArr['258'] = '(GMT+08:00) Kuala Lumpur';
    AllTimeZonesArr['259'] = '(GMT+08:00) Macau';
    AllTimeZonesArr['260'] = '(GMT+08:00) Makassar';
    AllTimeZonesArr['261'] = '(GMT+08:00) Manila';
    AllTimeZonesArr['262'] = '(GMT+08:00) Irkutsk';
    AllTimeZonesArr['263'] = '(GMT+08:00) Singapore';
    AllTimeZonesArr['264'] = '(GMT+08:00) Taipei';
    AllTimeZonesArr['265'] = '(GMT+08:00) Ulaanbaatar';
    AllTimeZonesArr['266'] = '(GMT+08:00) Western Time - Perth';
    AllTimeZonesArr['267'] = '(GMT+09:00) Choibalsan';
    AllTimeZonesArr['268'] = '(GMT+09:00) Dili';
    AllTimeZonesArr['269'] = '(GMT+09:00) Jayapura';
    AllTimeZonesArr['270'] = '(GMT+09:00) Yakutsk';
    AllTimeZonesArr['271'] = '(GMT+09:00) Palau';
    AllTimeZonesArr['272'] = '(GMT+09:00) Pyongyang';
    AllTimeZonesArr['273'] = '(GMT+09:00) Seoul';
    AllTimeZonesArr['274'] = '(GMT+09:00) Tokyo';
    AllTimeZonesArr['275'] = '(GMT+09:30) Central Time - Adelaide';
    AllTimeZonesArr['276'] = '(GMT+09:30) Central Time - Darwin';
    AllTimeZonesArr['277'] = '(GMT+10:00) Dumont D&';
    AllTimeZonesArr['278'] = '(GMT+10:00) Eastern Time - Brisbane';
    AllTimeZonesArr['279'] = '(GMT+10:00) Eastern Time - Hobart';
    AllTimeZonesArr['280'] = '(GMT+10:00) Eastern Time - Melbourne, Sydney';
    AllTimeZonesArr['281'] = '(GMT+10:00) Guam';
    AllTimeZonesArr['282'] = '(GMT+10:00) Yuzhno-Sakhalinsk';
    AllTimeZonesArr['283'] = '(GMT+10:00) Port Moresby';
    AllTimeZonesArr['284'] = '(GMT+10:00) Saipan';
    AllTimeZonesArr['285'] = '(GMT+10:00) Truk';
    AllTimeZonesArr['286'] = '(GMT+11:00) Efate';
    AllTimeZonesArr['287'] = '(GMT+11:00) Guadalcanal';
    AllTimeZonesArr['288'] = '(GMT+11:00) Kosrae';
    AllTimeZonesArr['289'] = '(GMT+11:00) Magadan';
    AllTimeZonesArr['290'] = '(GMT+11:00) Noumea';
    AllTimeZonesArr['291'] = '(GMT+11:00) Ponape';
    AllTimeZonesArr['292'] = '(GMT+11:30) Norfolk';
    AllTimeZonesArr['293'] = '(GMT+12:00) Antarctica/McMurdo';
    AllTimeZonesArr['294'] = '(GMT+12:00) Antarctica/South_Pole';
    AllTimeZonesArr['295'] = '(GMT+12:00) Auckland';
    AllTimeZonesArr['296'] = '(GMT+12:00) Fiji';
    AllTimeZonesArr['297'] = '(GMT+12:00) Funafuti';
    AllTimeZonesArr['298'] = '(GMT+12:00) Kwajalein';
    AllTimeZonesArr['299'] = '(GMT+12:00) Majuro';
    AllTimeZonesArr['300'] = '(GMT+12:00) Petropavlovsk-Kamchatskiy';
    AllTimeZonesArr['301'] = '(GMT+12:00) Nauru';
    AllTimeZonesArr['302'] = '(GMT+12:00) Tarawa';
    AllTimeZonesArr['303'] = '(GMT+12:00) Wake';
    AllTimeZonesArr['304'] = '(GMT+12:00) Wallis';
    AllTimeZonesArr['305'] = '(GMT+13:00) Enderbury';
    AllTimeZonesArr['306'] = '(GMT+13:00) Tongatapu';
    AllTimeZonesArr['307'] = '(GMT+14:00) Kiritimati';

var Countries = [
			{Value:"AF", Name:"Afghanistan"},
			{Value:"AX", Name:"Aland Islands"},
			{Value:"AL", Name:"Albania"},
			{Value:"DZ", Name:"Algeria"},
			{Value:"AS", Name:"American Samoa"},
			{Value:"AD", Name:"Andorra"},
			{Value:"AO", Name:"Angola"},
			{Value:"AI", Name:"Anguilla"},
			{Value:"AQ", Name:"Antarctica"},
			{Value:"AG", Name:"Antigua and Barbuda"},
			{Value:"AR", Name:"Argentina"},
			{Value:"AM", Name:"Armenia"},
			{Value:"AW", Name:"Aruba"},
			{Value:"AU", Name:"Australia"},
			{Value:"AT", Name:"Austria"},
			{Value:"AZ", Name:"Azerbaijan"},
			{Value:"BS", Name:"Bahamas"},
			{Value:"BH", Name:"Bahrain"},
			{Value:"BD", Name:"Bangladesh"},
			{Value:"BB", Name:"Barbados"},
			{Value:"BY", Name:"Belarus"},
			{Value:"BE", Name:"Belgium"},
			{Value:"BZ", Name:"Belize"},
			{Value:"BJ", Name:"Benin"},
			{Value:"BM", Name:"Bermuda"},
			{Value:"BT", Name:"Bhutan"},
			{Value:"BO", Name:"Bolivia"},
			{Value:"BA", Name:"Bosnia and Herzegovina"},
			{Value:"BW", Name:"Botswana"},
			{Value:"BV", Name:"Bouvet Island"},
			{Value:"BR", Name:"Brazil"},
			{Value:"IO", Name:"British Indian Ocean Territory"},
			{Value:"BN", Name:"Brunei"},
			{Value:"BG", Name:"Bulgaria"},
			{Value:"BF", Name:"Burkina Faso"},
			{Value:"BI", Name:"Burundi"},
			{Value:"KH", Name:"Cambodia"},
			{Value:"CM", Name:"Cameroon"},
			{Value:"CA", Name:"Canada"},
			{Value:"CV", Name:"Cape Verde"},
			{Value:"KY", Name:"Cayman Islands"},
			{Value:"CF", Name:"Central African Republic"},
			{Value:"TD", Name:"Chad"},
			{Value:"CL", Name:"Chile"},
			{Value:"CN", Name:"China"},
			{Value:"CX", Name:"Christmas Island"},
			{Value:"CC", Name:"Cocos Islands"},
			{Value:"CO", Name:"Colombia"},
			{Value:"KM", Name:"Comoros"},
			{Value:"CG", Name:"Congo"},
			{Value:"CD", Name:"Congo, Democratic Republic of the"},
			{Value:"CK", Name:"Cook Islands"},
			{Value:"CR", Name:"Costa Rica"},
			{Value:"CI", Name:"Cote d'Ivoire"},
			{Value:"HR", Name:"Croatia"},
			{Value:"CU", Name:"Cuba"},
			{Value:"CY", Name:"Cyprus"},
			{Value:"CZ", Name:"Czech Republic"},
			{Value:"DK", Name:"Denmark"},
			{Value:"DJ", Name:"Djibouti"},
			{Value:"DM", Name:"Dominica"},
			{Value:"DO", Name:"Dominican Republic"},
			{Value:"TL", Name:"East Timor"},
			{Value:"EC", Name:"Ecuador"},
			{Value:"EG", Name:"Egypt"},
			{Value:"SV", Name:"El Salvador"},
			{Value:"GQ", Name:"Equatorial Guinea"},
			{Value:"ER", Name:"Eritrea"},
			{Value:"EE", Name:"Estonia"},
			{Value:"ET", Name:"Ethiopia"},
			{Value:"FK", Name:"Falkland Islands"},
			{Value:"FO", Name:"Faroe Islands"},
			{Value:"FJ", Name:"Fiji"},
			{Value:"FI", Name:"Finland"},
			{Value:"FR", Name:"France"},
			{Value:"GF", Name:"French Guiana"},
			{Value:"PF", Name:"French Polynesia"},
			{Value:"TF", Name:"French Southern Territories"},
			{Value:"GA", Name:"Gabon"},
			{Value:"GM", Name:"Gambia"},
			{Value:"GE", Name:"Georgia"},
			{Value:"DE", Name:"Germany"},
			{Value:"GH", Name:"Ghana"},
			{Value:"GI", Name:"Gibraltar"},
			{Value:"GR", Name:"Greece"},
			{Value:"GL", Name:"Greenland"},
			{Value:"GD", Name:"Grenada"},
			{Value:"GP", Name:"Guadeloupe"},
			{Value:"GU", Name:"Guam"},
			{Value:"GT", Name:"Guatemala"},
			{Value:"GG", Name:"Guernsey"},
			{Value:"GN", Name:"Guinea"},
			{Value:"GW", Name:"Guinea"},
			{Value:"GY", Name:"Guyana"},
			{Value:"HT", Name:"Haiti"},
			{Value:"HM", Name:"Heard Island and McDonald Islands"},
			{Value:"HN", Name:"Honduras"},
			{Value:"HK", Name:"Hong Kong"},
			{Value:"HU", Name:"Hungary"},
			{Value:"IS", Name:"Iceland"},
			{Value:"IN", Name:"India"},
			{Value:"ID", Name:"Indonesia"},
			{Value:"IR", Name:"Iran"},
			{Value:"IQ", Name:"Iraq"},
			{Value:"IE", Name:"Ireland"},
			{Value:"IM", Name:"Isle of Man"},
			{Value:"IL", Name:"Israel"},
			{Value:"IT", Name:"Italy"},
			{Value:"JM", Name:"Jamaica"},
			{Value:"JP", Name:"Japan"},
			{Value:"JE", Name:"Jersey"},
			{Value:"JO", Name:"Jordan"},
			{Value:"KZ", Name:"Kazakhstan"},
			{Value:"KE", Name:"Kenya"},
			{Value:"KI", Name:"Kiribati"},
			{Value:"KW", Name:"Kuwait"},
			{Value:"KG", Name:"Kyrgyzstan"},
			{Value:"LA", Name:"Laos"},
			{Value:"LV", Name:"Latvia"},
			{Value:"LB", Name:"Lebanon"},
			{Value:"LS", Name:"Lesotho"},
			{Value:"LR", Name:"Liberia"},
			{Value:"LY", Name:"Libya"},
			{Value:"LI", Name:"Liechtenstein"},
			{Value:"LT", Name:"Lithuania"},
			{Value:"LU", Name:"Luxembourg"},
			{Value:"MO", Name:"Macao"},
			{Value:"MK", Name:"Macedonia"},
			{Value:"MG", Name:"Madagascar"},
			{Value:"MW", Name:"Malawi"},
			{Value:"MY", Name:"Malaysia"},
			{Value:"MV", Name:"Maldives"},
			{Value:"ML", Name:"Mali"},
			{Value:"MT", Name:"Malta"},
			{Value:"MH", Name:"Marshall Islands"},
			{Value:"MQ", Name:"Martinique"},
			{Value:"MR", Name:"Mauritania"},
			{Value:"MU", Name:"Mauritius"},
			{Value:"YT", Name:"Mayotte"},
			{Value:"MX", Name:"Mexico"},
			{Value:"FM", Name:"Micronesia"},
			{Value:"MD", Name:"Moldova"},
			{Value:"MC", Name:"Monaco"},
			{Value:"MN", Name:"Mongolia"},
			{Value:"ME", Name:"Montenegro"},
			{Value:"MS", Name:"Montserrat"},
			{Value:"MA", Name:"Morocco"},
			{Value:"MZ", Name:"Mozambique"},
			{Value:"MM", Name:"Myanmar"},
			{Value:"NA", Name:"Namibia"},
			{Value:"NR", Name:"Nauru"},
			{Value:"NP", Name:"Nepal"},
			{Value:"NL", Name:"Netherlands"},
			{Value:"AN", Name:"Netherlands Antilles"},
			{Value:"NC", Name:"New Caledonia"},
			{Value:"NZ", Name:"New Zealand"},
			{Value:"NI", Name:"Nicaragua"},
			{Value:"NE", Name:"Niger"},
			{Value:"NG", Name:"Nigeria"},
			{Value:"NU", Name:"Niue"},
			{Value:"NF", Name:"Norfolk Island"},
			{Value:"MP", Name:"Northern Mariana Islands"},
			{Value:"KP", Name:"North Korea"},
			{Value:"NO", Name:"Norway"},
			{Value:"OM", Name:"Oman"},
			{Value:"PK", Name:"Pakistan"},
			{Value:"PW", Name:"Palau"},
			{Value:"PS", Name:"Palestinian Territory"},
			{Value:"PA", Name:"Panama"},
			{Value:"PG", Name:"Papua New Guinea"},
			{Value:"PY", Name:"Paraguay"},
			{Value:"PE", Name:"Peru"},
			{Value:"PH", Name:"Philippines"},
			{Value:"PN", Name:"Pitcairn"},
			{Value:"PL", Name:"Poland"},
			{Value:"PT", Name:"Portugal"},
			{Value:"PR", Name:"Puerto Rico"},
			{Value:"QA", Name:"Qatar"},
			{Value:"RE", Name:"Reunion"},
			{Value:"RO", Name:"Romania"},
			{Value:"RU", Name:"Russia"},
			{Value:"RW", Name:"Rwanda"},
			{Value:"SH", Name:"Saint Helena"},
			{Value:"KN", Name:"Saint Kitts and Nevis"},
			{Value:"LC", Name:"Saint Lucia"},
			{Value:"PM", Name:"Saint Pierre and Miquelon"},
			{Value:"VC", Name:"Saint Vincent and the Grenadines"},
			{Value:"WS", Name:"Samoa"},
			{Value:"SM", Name:"San Marino"},
			{Value:"ST", Name:"Sao Tome and Principe"},
			{Value:"SA", Name:"Saudi Arabia"},
			{Value:"SN", Name:"Senegal"},
			{Value:"RS", Name:"Serbia"},
			{Value:"CS", Name:"Serbia and Montenegro"},
			{Value:"SC", Name:"Seychelles"},
			{Value:"SL", Name:"Sierra Leone"},
			{Value:"SG", Name:"Singapore"},
			{Value:"SK", Name:"Slovakia"},
			{Value:"SI", Name:"Slovenia"},
			{Value:"SB", Name:"Solomon Islands"},
			{Value:"SO", Name:"Somalia"},
			{Value:"ZA", Name:"South Africa"},
			{Value:"GS", Name:"South Georgia and the South Sandwich Islands"},
			{Value:"KR", Name:"South Korea"},
			{Value:"ES", Name:"Spain"},
			{Value:"LK", Name:"Sri Lanka"},
			{Value:"SD", Name:"Sudan"},
			{Value:"SR", Name:"Suriname"},
			{Value:"SJ", Name:"Svalbard and Jan Mayen"},
			{Value:"SZ", Name:"Swaziland"},
			{Value:"SE", Name:"Sweden"},
			{Value:"CH", Name:"Switzerland"},
			{Value:"SY", Name:"Syria"},
			{Value:"TW", Name:"Taiwan"},
			{Value:"TJ", Name:"Tajikistan"},
			{Value:"TZ", Name:"Tanzania"},
			{Value:"TH", Name:"Thailand"},
			{Value:"TG", Name:"Togo"},
			{Value:"TK", Name:"Tokelau"},
			{Value:"TO", Name:"Tonga"},
			{Value:"TT", Name:"Trinidad and Tobago"},
			{Value:"TN", Name:"Tunisia"},
			{Value:"TR", Name:"Turkey"},
			{Value:"TM", Name:"Turkmenistan"},
			{Value:"TC", Name:"Turks and Caicos Islands"},
			{Value:"TV", Name:"Tuvalu"},
			{Value:"UG", Name:"Uganda"},
			{Value:"UA", Name:"Ukraine"},
			{Value:"AE", Name:"United Arab Emirates"},
			{Value:"GB", Name:"United Kingdom"},
			{Value:"US", Name:"United States"},
			{Value:"UM", Name:"United States minor outlying islands"},
			{Value:"UY", Name:"Uruguay"},
			{Value:"UZ", Name:"Uzbekistan"},
			{Value:"VU", Name:"Vanuatu"},
			{Value:"VA", Name:"Vatican City"},
			{Value:"VE", Name:"Venezuela"},
			{Value:"VN", Name:"Vietnam"},
			{Value:"VG", Name:"Virgin Islands, British"},
			{Value:"VI", Name:"Virgin Islands, U.S."},
			{Value:"WF", Name:"Wallis and Futuna"},
			{Value:"EH", Name:"Western Sahara"},
			{Value:"YE", Name:"Yemen"},
			{Value:"ZM", Name:"Zambia"},
			{Value:"ZW", Name:"Zimbabwe"}
			];

if (typeof window.JSFileLoaded != 'undefined') {
	JSFileLoaded();
}