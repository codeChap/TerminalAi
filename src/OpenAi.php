<?php

class OpenAi
{
    public $prompt = [];

    public $key;

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setPrompt($text)
    {
        $this->prompt = $text;
    }

    public function run()
    {
        // Set API key and endpoint
        $endpoint = "https://api.openai.com/v1/chat/completions";

        // Set up headers
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->key}"
        ];

        // Default post body
        $body = [
            "model"             => 'gpt-4',
            "messages"          => array_values($this->prompt),
            "temperature"       => 1, // Control's randomness, less is more deterministic
            "top_p"             => 0, // Diversity
            "n"                 => 1, // Number of choices to return
            "frequency_penalty" => 0, // Penalize new tokens based on their existing frequency
            "presence_penalty"  => 0, // Penalize new tokens based on whether they appear in the text so far
            "stream"            => true // Stream back partial results as they are generated, instead of waiting for completion
        ];

        // The callback function while streaming
        $conversation = [];
        $callback = function ($ch, $str) use (&$conversation) {

            $json = str_replace("data: ", "", $str);

            // Explode around new lines
            $json = explode("\n", $json);

            foreach($json AS $j){
                if(!empty($j)){
                    $arr = json_decode($j, true);
                    if( ! empty($arr['choices'][0]['delta']['content'])) {
                        $conversation[] = $arr['choices'][0]['delta']['content'];
                        print $arr['choices'][0]['delta']['content'];
                    }
                }
            }
            return strlen($str);//return the exact length
        };

        // Create a cURL session
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
        curl_exec($ch);

        // Close the cURL session
        curl_close($ch);

        // Done
        return implode($conversation);
    }
}
?>