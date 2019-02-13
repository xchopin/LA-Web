<?php

/*
 * @author  Xavier Chopin <xavier.chopin@univ-lorraine.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use App\Controller\AbstractController as Provider;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use function Sodium\crypto_aead_aes256gcm_encrypt;
use function Sodium\crypto_pwhash;
use function Sodium\crypto_pwhash_scryptsalsa208sha256;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Caliper
{

    /**
     * Send a JSON to the Caliper route
     *
     * @param string $json
     * @return int
     * @throws \Exception
     */
    private static function send(string $json)
    {

        try {
            return Provider::$http->post("key/caliper", [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type' => 'application/json',
                    'Authorization' =>  env('API_USERNAME')
                ],
                'body' => $json
            ])
                ->getStatusCode();
        } catch (GuzzleException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Create a Caliper Event
     *
     * @param $userId
     * @param $action
     * @param string $description
     * @param string $groupId
     * @param string $groupType
     * @return string
     */
    public static function create($userId, $action, $description = "", $groupId =  "null")
    {
        $date = date_format(new DateTime('NOW'), 'Y-m-d\TH:i:s.755\Z');
        $eventId = sha1($userId . $date);
        $json = '
        {
        	"data": [
        	 {
                "@context": "http://purl.imsglobal.org/ctx/caliper/v1p1",
                "@type": "Event",
                "action": "' . $action . '",
                "actor": {
                    "@id": "' . $userId . '",
                    "@type": "Person"
                },
                "eventTime": "' . $date . '",
                "object": {
                    "@id": "' . $eventId . '",
                    "@type": "SoftwareApplication",
                    "name": "OpenDashboard Advanced"
                 },
                "group": { 
                    "@id": "' . $groupId . '",
                    "@type": "Group"
                }
            }
        	],
        	 "sendTime": "' . $date . '",
             "sensor": "http://localhost/scripts/cas/authentication"
        }';

        try {
            $status = self::send($json);
            return $status;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }



}