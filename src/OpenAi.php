<?php
/**
 * Class OpenAi
 * 
 * This class represents an OpenAI chatbot that interacts with the OpenAI API to generate responses based on given prompts.
 */
class OpenAi
{
    /**
     * @var array $prompts An array of prompts to be used for generating responses.
     */
    public $prompts = [];

    /**
     * @var string $key The API key used for authentication with the OpenAI API.
     */
    public $key;

    /**
     * @var bool $stream Whether or not to stream back partial results as they are generated, instead of waiting for completion.
     */
    public $stream = true;
    
    /**
     * 
     */
    public $temperature = 1;

    /**
     * 
     */
    public $top_p = 0;

    /**
     * 
     */
    public $n = 1;

    /**
     * 
     */
    public $frequency_penalty = 0;

    /**
     * 
     */
    public $presence_penalty = 0;

    /**
     * Set the API key
     * 
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set the prompts array
     * 
     * @param array $prompts
     */
    public function setPrompts( array $prompts)
    {
        // Loop over the prompts and filter out empty ones
        foreach($prompts AS $k => $prompt){
            if(empty($prompt['content'])){
                unset($prompts[$k]);
            }
        }

        if(count($prompts) == 0){
            throw new Exception("No valid prompts provided");
        }

        // Reset keys
        $prompts = array_values($prompts);

        // Prepend a blank prompt
        array_unshift($prompts, [
            "role" => "system",
            "content" => "You are ChatGPT, a general assistant."
        ]);

        // Set the prompts
        $this->prompts = $prompts;
    }

    /**
     * Run the chatbot and generate responses
     * 
     * @return string
     */
    public function run()
    {
        // Set API key and endpoint
        $endpoint = "https://api.openai.com/v1/chat/completions";

        // Set up headers
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . trim($this->key),
        ];

        // Default post body
        $body = [
            "model"             => 'gpt-4-0613',
            "messages"          => $this->prompts,
            "temperature"       => $this->temperature, // Control's randomness, less is more deterministic
            "top_p"             => $this->top_p, // Diversity
            "n"                 => $this->n, // Number of choices to return
            "frequency_penalty" => $this->frequency_penalty, // Penalize new tokens based on their existing frequency
            "presence_penalty"  => $this->presence_penalty, // Penalize new tokens based on whether they appear in the text so far
            "stream"            => $this->stream // Stream back partial results as they are generated, instead of waiting for completion
        ];

        // The callback function while streaming
        $conversation = [];
        $callback = function ($ch, $str) use (&$conversation) {

            $json = str_replace("data: ", "", $str);

            // Check for error
            if(strpos($json, "error") !== false){
                throw new Exception($json);
            }

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

        $payload = json_encode($body);

        // Initialize a cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

        // Set the callback function if streaming
        if($this->stream){
            curl_setopt($curl, CURLOPT_WRITEFUNCTION, $callback);
        }

        // Execute the cURL session and fetch the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if(curl_errno($curl)){
            // If an error occurred, print the error message
            echo 'cURL error: ' . curl_error($curl);
        }

        // Close the cURL session
        curl_close($curl);

        // Done
        return implode($conversation);
    }
}
?>