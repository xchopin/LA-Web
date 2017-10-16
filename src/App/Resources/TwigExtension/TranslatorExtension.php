<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Resources\TwigExtension;

use Slim\Http\Request;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class TranslatorExtension
 * Translates words into a language given.
 * @package App\Resources\TwigExtension
 */
class TranslatorExtension extends Twig_Extension
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $country_id;

    public function __construct(Request $request, $country_id = 'fr')
    {
        $this->request = $request;
        $this->country_id = $country_id;
    }

    public function getName()
    {
        return 'translate';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('translate', [$this, 'translate'])
        ];
    }

    public function translate($keyword)
    {
        $file = dirname(__FILE__) . '/../../' . DICTIONARY_PATH . ''. $this->country_id . '.json';
        $dictionary = json_decode(file_get_contents($file), true);

        if (isset($dictionary[$keyword]))
            return $dictionary[$keyword];

        return "Error: Undefined '" . $keyword . "' variable in '". $this->country_id ."' dictionary.";
    }
}
