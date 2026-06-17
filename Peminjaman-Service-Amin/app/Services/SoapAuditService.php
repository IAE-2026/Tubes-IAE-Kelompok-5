<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoapAuditService
{
    private string $soapUrl;
    private string $teamId;

    public function __construct()
    {
        $this->soapUrl = env('SSO_URL', 'https://iae-sso.virtualfri.id') . '/soap/v1/audit';
        $this->teamId  = env('TEAM_ID', 'TEAM-XX');
    }

    /**
     * Kirim audit transaksi ke Cloud Dosen via SOAP XML.
     * Mengembalikan ReceiptNumber jika berhasil, null jika gagal.
     */
    public function sendAudit(string $activityName, array $data, string $bearerToken): ?string
    {
        // Transformasi data JSON ke XML CDATA.
        $logContent = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $xmlEnvelope = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$this->teamId}</iae:TeamID>
      <iae:ActivityName>{$activityName}</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;

        try {
            $response = Http::withToken($bearerToken)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=UTF-8',
                    'SOAPAction'   => '"' . $activityName . '"',
                ])
                ->withBody($xmlEnvelope, 'text/xml')
                ->timeout(15)
                ->post($this->soapUrl);

            Log::info('SOAP Audit response', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);

            if (!$response->successful()) {
                Log::error('SOAP Audit HTTP error: ' . $response->status());
                return null;
            }

            // Parse ReceiptNumber dari XML response
            $receiptNumber = $this->parseReceiptNumber($response->body());

            if ($receiptNumber) {
                Log::info("SOAP Audit berhasil. ReceiptNumber: {$receiptNumber}");
            }

            return $receiptNumber;

        } catch (\Exception $e) {
            Log::error('SOAP Audit exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ekstrak ReceiptNumber dari XML response SOAP Dosen.
     */
    private function parseReceiptNumber(string $xmlBody): ?string
    {
        try {
            // Suppress error untuk XML yang tidak sempurna
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlBody);

            if (!$xml) {
                // Coba regex sebagai fallback
                preg_match('/<[^:>]*:?ReceiptNumber[^>]*>([^<]+)</', $xmlBody, $matches);
                return $matches[1] ?? null;
            }

            // Traverse semua namespace
            $namespaces = $xml->getNamespaces(true);

            foreach ($namespaces as $prefix => $ns) {
                $body = $xml->children($ns)->Body ?? null;
                if ($body) {
                    foreach ($body->children() as $child) {
                        foreach ($child->children() as $field) {
                            if (stripos($field->getName(), 'receipt') !== false) {
                                return (string) $field;
                            }
                        }
                    }
                }
            }

            // Fallback: regex langsung
            preg_match('/IAE-LOG-[\w-]+/', $xmlBody, $matches);
            return $matches[0] ?? null;

        } catch (\Exception $e) {
            Log::warning('Failed to parse SOAP receipt: ' . $e->getMessage());
            // Last resort regex
            preg_match('/IAE-LOG-[\w-]+/', $xmlBody, $matches);
            return $matches[0] ?? null;
        }
    }
}
