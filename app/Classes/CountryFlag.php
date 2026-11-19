<?php

/**
 * Guardar como: app/Classes/CountryFlag.php
 *
 * Converte o nome do país guardado em participants.represented_country /
 * participants.nationality num código ISO 3166-1 alpha-2, usado para
 * compor o URL da bandeira. Aceita nomes em português e em inglês,
 * com ou sem acentos.
 */

namespace App\Classes;

class CountryFlag
{
    /**
     * Nome normalizado => código ISO alpha-2.
     * Acrescentar aqui qualquer variante que apareça nos registos.
     */
    private static $map = [
        // Estados membros da União Africana
        'africa do sul' => 'za', 'south africa' => 'za',
        'angola' => 'ao',
        'argelia' => 'dz', 'algeria' => 'dz',
        'benim' => 'bj', 'benin' => 'bj',
        'botsuana' => 'bw', 'botswana' => 'bw',
        'burkina faso' => 'bf',
        'burundi' => 'bi',
        'cabo verde' => 'cv', 'cape verde' => 'cv',
        'camaroes' => 'cm', 'cameroon' => 'cm', 'cameroun' => 'cm',
        'chade' => 'td', 'chad' => 'td',
        'comores' => 'km', 'comoros' => 'km',
        'congo' => 'cg', 'republica do congo' => 'cg', 'congo brazzaville' => 'cg',
        'republica democratica do congo' => 'cd', 'democratic republic of the congo' => 'cd',
        'rdc' => 'cd', 'drc' => 'cd', 'congo kinshasa' => 'cd',
        'costa do marfim' => 'ci', 'cote d ivoire' => 'ci', 'cote divoire' => 'ci', 'ivory coast' => 'ci',
        'djibuti' => 'dj', 'djibouti' => 'dj',
        'egipto' => 'eg', 'egito' => 'eg', 'egypt' => 'eg',
        'eritreia' => 'er', 'eritrea' => 'er',
        'essuatini' => 'sz', 'eswatini' => 'sz', 'suazilandia' => 'sz',
        'etiopia' => 'et', 'ethiopia' => 'et',
        'gabao' => 'ga', 'gabon' => 'ga',
        'gambia' => 'gm',
        'gana' => 'gh', 'ghana' => 'gh',
        'guine' => 'gn', 'guinea' => 'gn', 'guine conacri' => 'gn',
        'guine bissau' => 'gw', 'guinea bissau' => 'gw',
        'guine equatorial' => 'gq', 'equatorial guinea' => 'gq',
        'lesoto' => 'ls', 'lesotho' => 'ls',
        'liberia' => 'lr',
        'libia' => 'ly', 'libya' => 'ly',
        'madagascar' => 'mg',
        'malaui' => 'mw', 'malawi' => 'mw',
        'mali' => 'ml',
        'marrocos' => 'ma', 'morocco' => 'ma',
        'mauricia' => 'mu', 'mauritius' => 'mu', 'ilhas mauricias' => 'mu',
        'mauritania' => 'mr',
        'mocambique' => 'mz', 'mozambique' => 'mz',
        'namibia' => 'na',
        'niger' => 'ne',
        'nigeria' => 'ng',
        'quenia' => 'ke', 'kenya' => 'ke',
        'republica centro africana' => 'cf', 'central african republic' => 'cf',
        'ruanda' => 'rw', 'rwanda' => 'rw',
        'sao tome e principe' => 'st', 'sao tome and principe' => 'st',
        'senegal' => 'sn',
        'serra leoa' => 'sl', 'sierra leone' => 'sl',
        'seicheles' => 'sc', 'seychelles' => 'sc',
        'somalia' => 'so',
        'sudao' => 'sd', 'sudan' => 'sd',
        'sudao do sul' => 'ss', 'south sudan' => 'ss',
        'tanzania' => 'tz',
        'togo' => 'tg',
        'tunisia' => 'tn', 'tunisie' => 'tn',
        'uganda' => 'ug',
        'zambia' => 'zm',
        'zimbabue' => 'zw', 'zimbabwe' => 'zw',
        'republica arabe saaui democratica' => 'eh', 'sahara ocidental' => 'eh', 'western sahara' => 'eh',

        // Restantes países com presença habitual em eventos desta natureza
        'alemanha' => 'de', 'germany' => 'de',
        'arabia saudita' => 'sa', 'saudi arabia' => 'sa',
        'argentina' => 'ar',
        'australia' => 'au',
        'austria' => 'at',
        'belgica' => 'be', 'belgium' => 'be',
        'brasil' => 'br', 'brazil' => 'br',
        'canada' => 'ca',
        'catar' => 'qa', 'qatar' => 'qa',
        'china' => 'cn',
        'coreia do sul' => 'kr', 'south korea' => 'kr',
        'cuba' => 'cu',
        'dinamarca' => 'dk', 'denmark' => 'dk',
        'emirados arabes unidos' => 'ae', 'united arab emirates' => 'ae',
        'espanha' => 'es', 'spain' => 'es',
        'estados unidos' => 'us', 'estados unidos da america' => 'us',
        'united states' => 'us', 'united states of america' => 'us', 'eua' => 'us', 'usa' => 'us',
        'franca' => 'fr', 'france' => 'fr',
        'holanda' => 'nl', 'paises baixos' => 'nl', 'netherlands' => 'nl',
        'india' => 'in',
        'indonesia' => 'id',
        'irlanda' => 'ie', 'ireland' => 'ie',
        'israel' => 'il',
        'italia' => 'it', 'italy' => 'it',
        'japao' => 'jp', 'japan' => 'jp',
        'mexico' => 'mx',
        'noruega' => 'no', 'norway' => 'no',
        'polonia' => 'pl', 'poland' => 'pl',
        'portugal' => 'pt',
        'reino unido' => 'gb', 'united kingdom' => 'gb', 'inglaterra' => 'gb',
        'russia' => 'ru',
        'suecia' => 'se', 'sweden' => 'se',
        'suica' => 'ch', 'switzerland' => 'ch',
        'timor leste' => 'tl', 'east timor' => 'tl',
        'turquia' => 'tr', 'turkey' => 'tr', 'turkiye' => 'tr',
    ];

    /**
     * Devolve o código ISO alpha-2 em minúsculas, ou null se o nome
     * não corresponder a um país conhecido (ex.: "União Africana", "ONU").
     *
     * @param  string|null  $countryName
     * @return string|null
     */
    public static function code($countryName)
    {
        $key = self::normalize($countryName);

        if ($key === '') {
            return null;
        }

        if (isset(self::$map[$key])) {
            return self::$map[$key];
        }

        // O próprio valor já pode ser um código ISO ("AO", "ZA")
        if (strlen($key) === 2 && in_array($key, self::$map, true)) {
            return $key;
        }

        return null;
    }

    /**
     * URL da bandeira no flagcdn. Devolve null quando não há código.
     *
     * @param  string|null  $countryName
     * @param  int  $scale  1, 2 ou 3
     * @return string|null
     */
    public static function url($countryName, $scale = 1)
    {
        $code = self::code($countryName);

        if ($code === null) {
            return null;
        }

        $size = $scale > 1 ? ($scale * 24) . 'x' . ($scale * 18) : '24x18';

        return 'https://flagcdn.com/' . $size . '/' . $code . '.png';
    }

    /**
     * Iniciais usadas quando não existe bandeira (fallback visual).
     *
     * @param  string|null  $countryName
     * @return string
     */
    public static function initials($countryName)
    {
        $words = preg_split('/\s+/', trim((string) $countryName));
        $letters = '';

        foreach ($words as $word) {
            if ($word === '' || mb_strlen($word) < 3) {
                continue;
            }
            $letters .= mb_strtoupper(mb_substr($word, 0, 1));
            if (mb_strlen($letters) === 2) {
                break;
            }
        }

        if ($letters === '') {
            $letters = mb_strtoupper(mb_substr(trim((string) $countryName), 0, 2));
        }

        return $letters !== '' ? $letters : '??';
    }

    /**
     * Minúsculas, sem acentos e sem pontuação, para comparação.
     *
     * @param  string|null  $value
     * @return string
     */
    private static function normalize($value)
    {
        $value = mb_strtolower(trim((string) $value));

        $accents = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ];

        $value = strtr($value, $accents);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
