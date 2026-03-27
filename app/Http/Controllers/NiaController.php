<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OneLogin\Saml2\Auth;
use App\Models\Application;
use Illuminate\Support\Facades\Auth as UserAuth;
use Illuminate\Support\Facades\Log;

class NiaController extends Controller
{
    private function getSamlAuth()
    {
        return new Auth(config('nia'));
    }

    public function metadata()
    {
        try {
            $auth     = $this->getSamlAuth();
            $settings = $auth->getSettings();
            $metadata = $settings->getSPMetadata();
            $errors   = $settings->validateMetadata($metadata);

            if (empty($errors)) {
                return response($metadata, 200, ['Content-Type' => 'text/xml']);
            }

            throw new \Exception('Invalid SP Metadata: ' . implode(', ', $errors));
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function login($applicationId)
    {
        $auth = $this->getSamlAuth();
        session(['nia_application_id' => $applicationId]);

        $settings     = new \OneLogin\Saml2\Settings(config('nia'));
        $authnRequest = new \OneLogin\Saml2\AuthnRequest($settings);
        $xml          = $authnRequest->getXML();

        $extensionsXml = <<<XML
        <samlp:Extensions xmlns:eidas="http://eidas.europa.eu/saml-extensions">
            <eidas:SPType>public</eidas:SPType>
            <eidas:RequestedAttributes>
                <eidas:RequestedAttribute Name="http://eidas.europa.eu/attributes/naturalperson/CurrentGivenName" isRequired="true" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"/>
                <eidas:RequestedAttribute Name="http://eidas.europa.eu/attributes/naturalperson/CurrentFamilyName" isRequired="true" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"/>
                <eidas:RequestedAttribute Name="http://eidas.europa.eu/attributes/naturalperson/DateOfBirth" isRequired="true" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"/>
                <eidas:RequestedAttribute Name="http://eidas.europa.eu/attributes/naturalperson/PersonIdentifier" isRequired="true" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"/>
                <eidas:RequestedAttribute Name="http://eidas.europa.eu/attributes/naturalperson/CurrentAddress" isRequired="false" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"/>
            </eidas:RequestedAttributes>
        </samlp:Extensions>
        XML;

        $issuerCloseTag = '</saml:Issuer>';
        $position       = strpos($xml, $issuerCloseTag) + strlen($issuerCloseTag);
        $xml            = substr_replace($xml, $extensionsXml, $position, 0);

        $signedXml = \OneLogin\Saml2\Utils::addSign(
            $xml,
            $settings->getSPkey(),
            $settings->getSPcert(),
            $settings->getSecurityData()['signatureAlgorithm'],
            $settings->getSecurityData()['digestAlgorithm']
        );

        $ssoUrl         = $settings->getIdPData()['singleSignOnService']['url'];
        $encodedRequest = base64_encode($signedXml);
        $relayState     = $applicationId;

        return response(
            <<<HTML
            <!DOCTYPE html><html><head><meta charset="utf-8"></head><body onload="document.forms[0].submit()">
            <form method="post" action="{$ssoUrl}"><input type="hidden" name="SAMLRequest" value="{$encodedRequest}" /><input type="hidden" name="RelayState" value="{$relayState}" /></form>
            </body></html>
            HTML
        );
    }

    public function acs(Request $request)
    {
        Log::info('NIA Callback hit!');

        if (! UserAuth::check()) {
            return redirect()->route('login')->with('error', 'Relace vypršela.');
        }

        $appId = session('nia_application_id');
        if (! $appId) {
            return redirect()->route('dashboard')->with('error', 'Chybí ID přihlášky v relaci.');
        }

        $auth = $this->getSamlAuth();

        try {
            $auth->processResponse();
        } catch (\Exception $e) {
            Log::error('NIA processResponse error: ' . $e->getMessage());
        }

        $rawXml = $auth->getLastResponseXML();

        if (empty($rawXml)) {
            return redirect()->route('application.step1', $appId)
                ->with('error', 'NIA neposlala žádná data.');
        }

        $attributes = $this->parseAttributesManually($rawXml);
        Log::info('NIA Parsed Attributes:', $attributes);

        $firstName = $attributes['http://eidas.europa.eu/attributes/naturalperson/CurrentGivenName']  ?? null;
        $lastName  = $attributes['http://eidas.europa.eu/attributes/naturalperson/CurrentFamilyName'] ?? null;
        $birthDate = $attributes['http://eidas.europa.eu/attributes/naturalperson/DateOfBirth']       ?? null;
        $rawAddress = $attributes['http://eidas.europa.eu/attributes/naturalperson/CurrentAddress']   ?? null;

        $parsedAddress = [];
        if ($rawAddress) {
            Log::info('NIA Address Found (Base64): ' . $rawAddress);
            $parsedAddress = $this->parseEidasAddress($rawAddress);
            Log::info('NIA Address Parsed Result:', $parsedAddress);
        } else {
            Log::warning('NIA Address Attribute is missing or empty.');
        }

        $missingFields = [];
        if (empty($firstName))              $missingFields[] = 'jméno';
        if (empty($lastName))               $missingFields[] = 'příjmení';
        if (empty($birthDate))              $missingFields[] = 'datum narození';
        if (empty($parsedAddress['street'])) $missingFields[] = 'ulici';
        if (empty($parsedAddress['city']))   $missingFields[] = 'město';

        if (! empty($missingFields)) {
            Log::warning('NIA verification incomplete — missing: ' . implode(', ', $missingFields));
            return redirect()->route('application.step1', $appId)
                ->with('error', 'Ověření identity nebylo dokončeno. Pro úspěšné ověření je nutné poskytnout všechna požadovaná data (' . implode(', ', $missingFields) . '). Zkuste prosím znovu a udělte souhlas se sdílením všech požadovaných údajů.');
        }

        $niaData = [
            'first_name' => trim($firstName),
            'last_name'  => trim($lastName),
            'birth_date' => trim($birthDate),
            'street'     => $parsedAddress['street'],
            'city'       => $parsedAddress['city'],
            'zip'        => $parsedAddress['zip']     ?? '',
            'country'    => $parsedAddress['country'] ?? 'Česká republika',
        ];

        $this->saveDataToApplication($niaData);

        return redirect()->route('application.step1', $appId)
            ->with('success', 'Identita byla úspěšně ověřena.');
    }

    private function parseAttributesManually(string $xmlString): array
    {
        $attributes = [];
        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($xmlString);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

            $nodes = $xpath->query('//saml:Attribute');

            foreach ($nodes as $node) {
                if ($node instanceof \DOMElement) {
                    $name      = $node->getAttribute('Name');
                    $valueNode = $xpath->query('./saml:AttributeValue', $node)->item(0);

                    if ($valueNode) {
                        $attributes[$name] = trim($valueNode->textContent);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Manual XML Parse Error: ' . $e->getMessage());
        }

        return $attributes;
    }

    private function parseEidasAddress(string $base64Xml): array
    {
        try {
            $xmlString = base64_decode($base64Xml);
            Log::info('NIA Address Decoded XML: ' . $xmlString);

            $xmlString = str_replace(['eidas:', 'xmlns:eidas'], ['', 'ignore'], $xmlString);
            $xmlString = '<root>' . $xmlString . '</root>';

            $xml = new \SimpleXMLElement($xmlString);

            $houseNum   = (string) ($xml->LocatorDesignator ?? '');
            $streetName = (string) ($xml->Thoroughfare ?? '');
            $city       = (string) ($xml->CvaddressArea ?? $xml->PostName ?? '');
            $zip        = (string) ($xml->PostCode ?? '');

            if (empty($city) && isset($xml->PostName)) {
                $city = (string) $xml->PostName;
            }

            $fullStreet = empty($streetName)
                ? "$city $houseNum"
                : "$streetName $houseNum";

            return [
                'street'  => trim($fullStreet),
                'city'    => trim($city),
                'zip'     => str_replace(' ', '', trim($zip)),
                'country' => 'Česká republika',
            ];
        } catch (\Exception $e) {
            Log::error('Address Parse Error: ' . $e->getMessage());
            return [];
        }
    }

    private function saveDataToApplication(array $niaData): void
    {
        $appId = session('nia_application_id');
        if (! $appId) return;

        $application = Application::where('user_id', UserAuth::id())->findOrFail($appId);

        foreach ($niaData as $key => $value) {
            $application->$key = $value;
        }

        $application->verified_fields    = array_keys($niaData);
        $application->identity_verified  = true;
        $application->save();
    }
}
