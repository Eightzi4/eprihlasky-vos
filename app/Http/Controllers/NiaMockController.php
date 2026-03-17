<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NiaMockController extends Controller
{
    public function login($applicationId)
    {
        session(['nia_application_id' => $applicationId]);
        sleep(1);
        return redirect()->route('nia.mock.callback');
    }

    public function callback()
    {
        $samlAttributes = [
            'http://eidas.europa.eu/attributes/naturalperson/CurrentGivenName'   => ['Pavla'],
            'http://eidas.europa.eu/attributes/naturalperson/CurrentFamilyName'  => ['Dvořáková'],
            'http://eidas.europa.eu/attributes/naturalperson/DateOfBirth'        => ['1955-06-07'],
            'http://eidas.europa.eu/attributes/naturalperson/PersonIdentifier'   => ['CZ/CZ/225171f6-4662-4f04-a889-5e9b1870f608'],
            'ParsedAddress_Street' => 'Arnoltice',
            'ParsedAddress_City'   => 'Arnoltice u Děčína',
            'ParsedAddress_Zip'    => '40714',
        ];

        $niaData = [
            'first_name' => $this->getAttr($samlAttributes, 'http://eidas.europa.eu/attributes/naturalperson/CurrentGivenName'),
            'last_name'  => $this->getAttr($samlAttributes, 'http://eidas.europa.eu/attributes/naturalperson/CurrentFamilyName'),

            'birth_date' => $this->getAttr($samlAttributes, 'http://eidas.europa.eu/attributes/naturalperson/DateOfBirth'),
            // Birth Number: 555607/1235

            'street'     => $samlAttributes['ParsedAddress_Street'],
            'city'       => $samlAttributes['ParsedAddress_City'],
            'zip'        => $samlAttributes['ParsedAddress_Zip'],
        ];

        $appId      = session('nia_application_id');
        $application = Application::where('user_id', Auth::id())->findOrFail($appId);

        $verifiedFields = [];
        foreach ($niaData as $key => $value) {
            if (! empty($value)) {
                $application->$key = $value;
                $verifiedFields[]  = $key;
            }
        }

        $application->verified_fields    = $verifiedFields;
        $application->identity_verified  = true;
        $application->save();

        $application->evaluateStates();

        return redirect()->route('application.step1', $application->id)
            ->with('success', 'Identita byla úspěšně ověřena (Simulace NIA).');
    }

    private function getAttr($attributes, $key)
    {
        return $attributes[$key][0] ?? null;
    }
}
