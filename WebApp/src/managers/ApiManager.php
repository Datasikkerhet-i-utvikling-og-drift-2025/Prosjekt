<?php
namespace managers;

class ApiManager {
    private string $apiToken;

    public function __construct(string $apiToken) {
        $this->apiToken = $apiToken;
    }

    public function post(string $endpoint, array $data): array {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, getenv('URL') . $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'success' => false,
                'errors' => ['Curl error: ' . curl_error($ch)]
            ];
        }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedResponse)) {
            return [
                'success' => false,
                'errors' => ['Invalid JSON response from API']
            ];
        }

        return $decodedResponse;
    }
}
